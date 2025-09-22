<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MpesaService;
use App\Models\Transaction;
use App\Models\Member;
use App\Models\Account;
use App\Models\MemberDividend;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class MpesaController extends Controller
{
    private $mpesaService;

    public function __construct(MpesaService $mpesaService)
    {
        $this->mpesaService = $mpesaService;
    }

    /**
     * Initiate M-Pesa STK Push for deposits
     */
    public function initiateDeposit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|string|min:10|max:13',
            'amount' => 'required|numeric|min:1',
            'member_id' => 'required|exists:members,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $member = Member::findOrFail($request->member_id);
            $accountReference = $member->membership_id;
            $description = "SACCO Deposit - {$member->first_name} {$member->last_name}";

            $response = $this->mpesaService->stkPush(
                $request->phone_number,
                $request->amount,
                $accountReference,
                $description
            );

            // Store pending transaction
            $transaction = Transaction::create([
                'member_id' => $member->id,
                'account_id' => $member->accounts()->where('account_type', 'savings')->first()->id ?? null,
                'transaction_type' => 'deposit',
                'amount' => $request->amount,
                'payment_method' => 'mpesa',
                'status' => 'pending',
                'description' => $description,
                'metadata' => json_encode([
                    'checkout_request_id' => $response['CheckoutRequestID'],
                    'merchant_request_id' => $response['MerchantRequestID'],
                    'phone_number' => $request->phone_number
                ])
            ]);

            return response()->json([
                'success' => true,
                'message' => 'STK Push initiated successfully',
                'data' => [
                    'checkout_request_id' => $response['CheckoutRequestID'],
                    'merchant_request_id' => $response['MerchantRequestID'],
                    'transaction_id' => $transaction->id
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('M-Pesa STK Push Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to initiate payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Initiate M-Pesa B2C for withdrawals
     */
    public function initiateWithdrawal(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|string|min:10|max:13',
            'amount' => 'required|numeric|min:1',
            'member_id' => 'required|exists:members,id',
            'account_id' => 'required|exists:accounts,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $member = Member::findOrFail($request->member_id);
            $account = Account::findOrFail($request->account_id);

            // Check if member owns the account
            if ($account->member_id !== $member->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Account does not belong to member'
                ], 403);
            }

            // Check sufficient balance
            if ($account->available_balance < $request->amount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient balance'
                ], 400);
            }

            DB::beginTransaction();

            // Create pending withdrawal transaction
            $transaction = Transaction::create([
                'member_id' => $member->id,
                'account_id' => $account->id,
                'transaction_type' => 'withdrawal',
                'amount' => $request->amount,
                'balance_before' => $account->balance,
                'balance_after' => $account->balance - $request->amount,
                'payment_method' => 'mpesa',
                'status' => 'pending',
                'description' => "M-Pesa Withdrawal - {$member->first_name} {$member->last_name}",
                'processed_by' => auth()->id()
            ]);

            // Update account balance (reserved)
            $account->update([
                'available_balance' => $account->available_balance - $request->amount
            ]);

            $remarks = "SACCO Withdrawal - {$member->first_name} {$member->last_name}";
            $occasion = 'Withdrawal';

            $response = $this->mpesaService->b2cPayment(
                $request->phone_number,
                $request->amount,
                $remarks,
                $occasion
            );

            // Update transaction with M-Pesa details
            $transaction->update([
                'metadata' => json_encode([
                    'conversation_id' => $response['ConversationID'],
                    'originator_conversation_id' => $response['OriginatorConversationID'],
                    'phone_number' => $request->phone_number
                ])
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Withdrawal initiated successfully',
                'data' => [
                    'conversation_id' => $response['ConversationID'],
                    'transaction_id' => $transaction->id
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('M-Pesa B2C Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to initiate withdrawal',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Distribute dividends via M-Pesa
     */
    public function distributeDividends(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'dividend_id' => 'required|exists:dividends,id',
            'member_dividend_ids' => 'required|array',
            'member_dividend_ids.*' => 'exists:member_dividends,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $memberDividends = MemberDividend::whereIn('id', $request->member_dividend_ids)
                ->where('status', 'approved')
                ->with('member')
                ->get();

            $results = [];
            $totalAmount = 0;

            foreach ($memberDividends as $memberDividend) {
                $member = $memberDividend->member;
                
                if (!$member->phone_number) {
                    $results[] = [
                        'member_id' => $member->id,
                        'member_name' => $member->first_name . ' ' . $member->last_name,
                        'amount' => $memberDividend->amount,
                        'status' => 'failed',
                        'message' => 'No phone number'
                    ];
                    continue;
                }

                try {
                    $remarks = "SACCO Dividend {$memberDividend->dividend->year} - {$member->first_name} {$member->last_name}";
                    $occasion = 'Dividend Distribution';

                    $response = $this->mpesaService->b2cPayment(
                        $member->phone_number,
                        $memberDividend->amount,
                        $remarks,
                        $occasion
                    );

                    // Update member dividend status
                    $memberDividend->update([
                        'status' => 'processing',
                        'payment_date' => now(),
                        'payment_method' => 'mpesa',
                        'payment_reference' => $response['ConversationID']
                    ]);

                    $results[] = [
                        'member_id' => $member->id,
                        'member_name' => $member->first_name . ' ' . $member->last_name,
                        'amount' => $memberDividend->amount,
                        'status' => 'processing',
                        'conversation_id' => $response['ConversationID']
                    ];

                    $totalAmount += $memberDividend->amount;

                } catch (\Exception $e) {
                    $results[] = [
                        'member_id' => $member->id,
                        'member_name' => $member->first_name . ' ' . $member->last_name,
                        'amount' => $memberDividend->amount,
                        'status' => 'failed',
                        'message' => $e->getMessage()
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Dividend distribution initiated',
                'data' => [
                    'total_amount' => $totalAmount,
                    'total_members' => count($memberDividends),
                    'results' => $results
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Dividend Distribution Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to distribute dividends',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * STK Push Callback
     */
    public function stkCallback(Request $request)
    {
        Log::info('STK Push Callback received', $request->all());

        try {
            $result = $this->mpesaService->handleStkCallback($request->all());
            
            return response()->json([
                'ResultCode' => 0,
                'ResultDesc' => 'Callback processed successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('STK Callback Error: ' . $e->getMessage());
            
            return response()->json([
                'ResultCode' => 1,
                'ResultDesc' => 'Callback processing failed'
            ]);
        }
    }

    /**
     * B2C Result Callback
     */
    public function b2cResultCallback(Request $request)
    {
        Log::info('B2C Result Callback received', $request->all());

        try {
            $result = $this->mpesaService->handleB2cCallback($request->all());
            
            return response()->json([
                'ResultCode' => 0,
                'ResultDesc' => 'Callback processed successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('B2C Callback Error: ' . $e->getMessage());
            
            return response()->json([
                'ResultCode' => 1,
                'ResultDesc' => 'Callback processing failed'
            ]);
        }
    }

    /**
     * B2C Timeout Callback
     */
    public function b2cTimeoutCallback(Request $request)
    {
        Log::info('B2C Timeout Callback received', $request->all());

        // Handle timeout - mark transaction as failed
        $conversationId = $request->input('ConversationID');
        
        return response()->json([
            'ResultCode' => 0,
            'ResultDesc' => 'Timeout processed successfully'
        ]);
    }

    /**
     * Check transaction status
     */
    public function checkTransactionStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'transaction_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $response = $this->mpesaService->transactionStatus($request->transaction_id);
            
            return response()->json([
                'success' => true,
                'data' => $response
            ]);

        } catch (\Exception $e) {
            Log::error('Transaction Status Check Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to check transaction status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get M-Pesa account balance
     */
    public function getAccountBalance()
    {
        try {
            $response = $this->mpesaService->accountBalance();
            
            return response()->json([
                'success' => true,
                'data' => $response
            ]);

        } catch (\Exception $e) {
            Log::error('Account Balance Check Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to check account balance',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}