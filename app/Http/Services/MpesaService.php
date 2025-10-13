<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\Transaction;
use App\Models\Member;
use App\Models\Account;
use Carbon\Carbon;

class MpesaService
{
    private $baseUrl;
    private $consumerKey;
    private $consumerSecret;
    private $passkey;
    private $shortcode;
    private $environment;

    public function __construct()
    {
        $this->environment = config('mpesa.environment', 'sandbox');
        $this->baseUrl = $this->environment === 'production' 
            ? 'https://api.safaricom.co.ke' 
            : 'https://sandbox.safaricom.co.ke';
        
        $this->consumerKey = config('mpesa.consumer_key');
        $this->consumerSecret = config('mpesa.consumer_secret');
        $this->passkey = config('mpesa.passkey');
        $this->shortcode = config('mpesa.shortcode');
    }

    /**
     * Generate OAuth access token
     */
    public function generateAccessToken()
    {
        $cacheKey = 'mpesa_access_token';
        
        // Check if token exists in cache
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $credentials = base64_encode($this->consumerKey . ':' . $this->consumerSecret);
        
        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . $credentials,
            'Content-Type' => 'application/json',
        ])->get($this->baseUrl . '/oauth/v1/generate?grant_type=client_credentials');

        if ($response->successful()) {
            $token = $response->json()['access_token'];
            $expiresIn = $response->json()['expires_in'];
            
            // Cache token for slightly less than expiry time
            Cache::put($cacheKey, $token, now()->addSeconds($expiresIn - 60));
            
            return $token;
        }

        throw new \Exception('Failed to generate M-Pesa access token: ' . $response->body());
    }

    /**
     * STK Push for member deposits
     */
    public function stkPush($phoneNumber, $amount, $accountReference, $description = 'SACCO Deposit')
    {
        $token = $this->generateAccessToken();
        $timestamp = Carbon::now()->format('YmdHis');
        $password = base64_encode($this->shortcode . $this->passkey . $timestamp);

        // Validate phone number format
        $phoneNumber = $this->formatPhoneNumber($phoneNumber);

        $payload = [
            'BusinessShortCode' => $this->shortcode,
            'Password' => $password,
            'Timestamp' => $timestamp,
            'TransactionType' => 'CustomerPayBillOnline',
            'Amount' => $amount,
            'PartyA' => $phoneNumber,
            'PartyB' => $this->shortcode,
            'PhoneNumber' => $phoneNumber,
            'CallBackURL' => route('mpesa.callback.stk'),
            'AccountReference' => $accountReference,
            'TransactionDesc' => $description
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ])->post($this->baseUrl . '/mpesa/stkpush/v1/processrequest', $payload);

        if ($response->successful()) {
            $responseData = $response->json();
            
            // Log the transaction for tracking
            $this->logTransaction([
                'checkout_request_id' => $responseData['CheckoutRequestID'],
                'merchant_request_id' => $responseData['MerchantRequestID'],
                'phone_number' => $phoneNumber,
                'amount' => $amount,
                'account_reference' => $accountReference,
                'transaction_type' => 'STK_PUSH',
                'status' => 'PENDING'
            ]);

            return $responseData;
        }

        throw new \Exception('STK Push failed: ' . $response->body());
    }

    /**
     * B2C Payment for withdrawals and dividend distributions
     */
    public function b2cPayment($phoneNumber, $amount, $remarks = 'SACCO Payment', $occasion = 'Withdrawal')
    {
        $token = $this->generateAccessToken();
        $securityCredential = $this->generateSecurityCredential();

        $phoneNumber = $this->formatPhoneNumber($phoneNumber);

        $payload = [
            'InitiatorName' => config('mpesa.initiator_name'),
            'SecurityCredential' => $securityCredential,
            'CommandID' => 'BusinessPayment',
            'Amount' => $amount,
            'PartyA' => $this->shortcode,
            'PartyB' => $phoneNumber,
            'Remarks' => $remarks,
            'QueueTimeOutURL' => route('mpesa.callback.b2c.timeout'),
            'ResultURL' => route('mpesa.callback.b2c.result'),
            'Occasion' => $occasion
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ])->post($this->baseUrl . '/mpesa/b2c/v3/paymentrequest', $payload);

        if ($response->successful()) {
            $responseData = $response->json();
            
            // Log the transaction
            $this->logTransaction([
                'conversation_id' => $responseData['ConversationID'],
                'originator_conversation_id' => $responseData['OriginatorConversationID'],
                'phone_number' => $phoneNumber,
                'amount' => $amount,
                'transaction_type' => 'B2C_PAYMENT',
                'status' => 'PENDING',
                'remarks' => $remarks
            ]);

            return $responseData;
        }

        throw new \Exception('B2C Payment failed: ' . $response->body());
    }

    /**
     * Transaction Status Query
     */
    public function transactionStatus($transactionId)
    {
        $token = $this->generateAccessToken();
        $securityCredential = $this->generateSecurityCredential();

        $payload = [
            'Initiator' => config('mpesa.initiator_name'),
            'SecurityCredential' => $securityCredential,
            'CommandID' => 'TransactionStatusQuery',
            'TransactionID' => $transactionId,
            'PartyA' => $this->shortcode,
            'IdentifierType' => '4',
            'ResultURL' => route('mpesa.callback.status'),
            'QueueTimeOutURL' => route('mpesa.callback.timeout'),
            'Remarks' => 'Transaction Status Query',
            'Occasion' => 'Status Check'
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ])->post($this->baseUrl . '/mpesa/transactionstatus/v1/query', $payload);

        return $response->json();
    }

    /**
     * Account Balance Query
     */
    public function accountBalance()
    {
        $token = $this->generateAccessToken();
        $securityCredential = $this->generateSecurityCredential();

        $payload = [
            'Initiator' => config('mpesa.initiator_name'),
            'SecurityCredential' => $securityCredential,
            'CommandID' => 'AccountBalance',
            'PartyA' => $this->shortcode,
            'IdentifierType' => '4',
            'ResultURL' => route('mpesa.callback.balance'),
            'QueueTimeOutURL' => route('mpesa.callback.timeout'),
            'Remarks' => 'Account Balance Query'
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ])->post($this->baseUrl . '/mpesa/accountbalance/v1/query', $payload);

        return $response->json();
    }

    /**
     * Process STK Push Callback
     */
    public function handleStkCallback($callbackData)
    {
        $resultCode = $callbackData['Body']['stkCallback']['ResultCode'];
        $checkoutRequestId = $callbackData['Body']['stkCallback']['CheckoutRequestID'];
        
        if ($resultCode == 0) {
            // Payment successful
            $callbackMetadata = $callbackData['Body']['stkCallback']['CallbackMetadata']['Item'];
            
            $amount = 0;
            $mpesaReceiptNumber = '';
            $phoneNumber = '';
            $transactionDate = '';
            
            foreach ($callbackMetadata as $item) {
                switch ($item['Name']) {
                    case 'Amount':
                        $amount = $item['Value'];
                        break;
                    case 'MpesaReceiptNumber':
                        $mpesaReceiptNumber = $item['Value'];
                        break;
                    case 'PhoneNumber':
                        $phoneNumber = $item['Value'];
                        break;
                    case 'TransactionDate':
                        $transactionDate = $item['Value'];
                        break;
                }
            }
            
            // Find member by phone number
            $member = Member::where('phone_number', $phoneNumber)->first();
            
            if ($member) {
                // Find or create savings account
                $account = Account::firstOrCreate([
                    'member_id' => $member->id,
                    'account_type' => 'savings'
                ], [
                    'account_number' => $this->generateAccountNumber(),
                    'balance' => 0,
                    'available_balance' => 0,
                    'status' => 'active'
                ]);
                
                // Create transaction record
                $transaction = Transaction::create([
                    'member_id' => $member->id,
                    'account_id' => $account->id,
                    'transaction_type' => 'deposit',
                    'amount' => $amount,
                    'balance_before' => $account->balance,
                    'balance_after' => $account->balance + $amount,
                    'payment_method' => 'mpesa',
                    'payment_reference' => $mpesaReceiptNumber,
                    'transaction_date' => Carbon::createFromFormat('YmdHis', $transactionDate),
                    'description' => 'M-Pesa Deposit',
                    'status' => 'completed',
                    'processed_by' => 1, // System user
                    'metadata' => json_encode([
                        'checkout_request_id' => $checkoutRequestId,
                        'phone_number' => $phoneNumber,
                        'mpesa_receipt' => $mpesaReceiptNumber
                    ])
                ]);
                
                // Update account balance
                $account->update([
                    'balance' => $account->balance + $amount,
                    'available_balance' => $account->available_balance + $amount,
                    'last_transaction_date' => now()
                ]);
                
                // Send notification
                $this->sendTransactionNotification($member, $transaction);
                
                return ['status' => 'success', 'transaction' => $transaction];
            }
        }
        
        // Payment failed - update transaction status
        $this->updateTransactionStatus($checkoutRequestId, 'failed');
        
        return ['status' => 'failed', 'result_code' => $resultCode];
    }

    /**
     * Process B2C Callback
     */
    public function handleB2cCallback($callbackData)
    {
        $resultCode = $callbackData['Result']['ResultCode'];
        $conversationId = $callbackData['Result']['ConversationID'];
        
        if ($resultCode == 0) {
            // Payment successful
            $resultParameters = $callbackData['Result']['ResultParameters']['ResultParameter'];
            
            $transactionId = '';
            $amount = 0;
            $recipientNumber = '';
            $transactionDate = '';
            
            foreach ($resultParameters as $parameter) {
                switch ($parameter['Key']) {
                    case 'TransactionID':
                        $transactionId = $parameter['Value'];
                        break;
                    case 'TransactionAmount':
                        $amount = $parameter['Value'];
                        break;
                    case 'ReceiverPartyPublicName':
                        $recipientNumber = $parameter['Value'];
                        break;
                    case 'TransactionCompletedDateTime':
                        $transactionDate = $parameter['Value'];
                        break;
                }
            }
            
            // Update transaction status
            $this->updateB2cTransactionStatus($conversationId, 'completed', $transactionId);
            
            return ['status' => 'success'];
        }
        
        // Payment failed
        $this->updateB2cTransactionStatus($conversationId, 'failed');
        
        return ['status' => 'failed', 'result_code' => $resultCode];
    }

    /**
     * Generate security credential
     */
    private function generateSecurityCredential()
    {
        $publicKey = config('mpesa.certificate_path');
        $initiatorPassword = config('mpesa.initiator_password');
        
        if (!file_exists($publicKey)) {
            throw new \Exception('M-Pesa certificate not found');
        }
        
        $publicKeyContent = file_get_contents($publicKey);
        openssl_public_encrypt($initiatorPassword, $encrypted, $publicKeyContent, OPENSSL_PKCS1_PADDING);
        
        return base64_encode($encrypted);
    }

    /**
     * Format phone number to international format
     */
    private function formatPhoneNumber($phoneNumber)
    {
        $phoneNumber = preg_replace('/\D/', '', $phoneNumber);
        
        if (strlen($phoneNumber) == 10 && substr($phoneNumber, 0, 1) == '0') {
            return '254' . substr($phoneNumber, 1);
        }
        
        if (strlen($phoneNumber) == 9) {
            return '254' . $phoneNumber;
        }
        
        if (strlen($phoneNumber) == 12 && substr($phoneNumber, 0, 3) == '254') {
            return $phoneNumber;
        }
        
        throw new \Exception('Invalid phone number format');
    }

    /**
     * Generate account number
     */
    private function generateAccountNumber()
    {
        do {
            $accountNumber = 'SAV' . str_pad(rand(0, 9999999), 7, '0', STR_PAD_LEFT);
        } while (Account::where('account_number', $accountNumber)->exists());
        
        return $accountNumber;
    }

    /**
     * Log transaction for tracking
     */
    private function logTransaction($data)
    {
        Log::channel('mpesa')->info('M-Pesa Transaction', $data);
    }

    /**
     * Update transaction status
     */
    private function updateTransactionStatus($checkoutRequestId, $status)
    {
        // Update your transaction table based on checkout request ID
        Log::info("Updating transaction status for {$checkoutRequestId} to {$status}");
    }

    /**
     * Update B2C transaction status
     */
    private function updateB2cTransactionStatus($conversationId, $status, $transactionId = null)
    {
        // Update your transaction table based on conversation ID
        Log::info("Updating B2C transaction status for {$conversationId} to {$status}");
    }

    /**
     * Send transaction notification
     */
    private function sendTransactionNotification($member, $transaction)
    {
        // Send SMS/Email notification
        Log::info("Sending notification to member {$member->id} for transaction {$transaction->id}");
    }
}