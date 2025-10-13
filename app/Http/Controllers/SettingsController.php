<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Storage, Artisan, Hash, Auth, Cache, Log};
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use App\Models\{SystemSetting, User, AuditLog};
use App\Services\{BackupService, NotificationService};
use Carbon\Carbon;

class SettingsController extends Controller
{
    /**
     * Display the settings overview page
     */
    public function index()
    {
        $settings = $this->getSettingsGrouped();
        
        return Inertia::render('Settings/Index', [
            'settings' => $settings,
            'lastBackup' => $this->getLastBackupInfo(),
            'systemInfo' => $this->getSystemInfo(),
            'recentActivity' => $this->getRecentActivity()
        ]);
    }

    /**
     * Display general settings
     */
    public function general()
    {
        $settings = SystemSetting::where('group', 'general')
            ->where('is_public', true)
            ->get()
            ->keyBy('key');

        return Inertia::render('Settings/General', [
            'settings' => $settings,
            'currencies' => $this->getCurrencies(),
            'timezones' => $this->getTimezones(),
            'languages' => $this->getLanguages()
        ]);
    }

    /**
     * Update general settings
     */
    public function updateGeneral(Request $request)
    {
        $validated = $request->validate([
            'sacco_name' => ['required', 'string', 'max:255'],
            'sacco_code' => ['required', 'string', 'max:10', 'alpha_num'],
            'registration_number' => ['required', 'string', 'max:50'],
            'address' => ['required', 'string', 'max:500'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            'currency' => ['required', 'string', 'size:3'],
            'timezone' => ['required', 'string', 'max:50'],
            'language' => ['required', 'string', 'max:10'],
            'date_format' => ['required', 'string', 'max:20'],
            'time_format' => ['required', 'string', 'max:20'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'favicon' => ['nullable', 'image', 'max:512'],
            'maintenance_mode' => ['boolean'],
            'member_registration_open' => ['boolean'],
            'auto_generate_member_id' => ['boolean'],
            'require_email_verification' => ['boolean'],
            'require_phone_verification' => ['boolean']
        ]);

        DB::transaction(function () use ($validated, $request) {
            foreach ($validated as $key => $value) {
                if (in_array($key, ['logo', 'favicon'])) {
                    if ($request->hasFile($key)) {
                        // Delete old file if exists
                        $oldSetting = SystemSetting::where('key', $key)->first();
                        if ($oldSetting && $oldSetting->value) {
                            Storage::disk('public')->delete($oldSetting->value);
                        }
                        
                        // Store new file
                        $path = $request->file($key)->store('settings', 'public');
                        $this->updateSetting($key, $path, 'general');
                    }
                } else {
                    $this->updateSetting($key, $value, 'general');
                }
            }

            // Log the activity
            $this->logActivity('general_settings_updated', 'General settings updated');
        });

        return redirect()->back()->with('success', 'General settings updated successfully.');
    }

    /**
     * Display financial settings
     */
    public function financial()
    {
        $settings = SystemSetting::where('group', 'financial')
            ->get()
            ->keyBy('key');

        return Inertia::render('Settings/Financial', [
            'settings' => $settings,
            'accountTypes' => $this->getAccountTypes(),
            'interestCalculationMethods' => $this->getInterestCalculationMethods()
        ]);
    }

    /**
     * Update financial settings
     */
    public function updateFinancial(Request $request)
    {
        $validated = $request->validate([
            // Savings settings
            'min_savings_balance' => ['required', 'numeric', 'min:0'],
            'max_savings_balance' => ['nullable', 'numeric', 'min:0'],
            'savings_interest_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'savings_interest_calculation' => ['required', 'in:daily,monthly,quarterly,annually'],
            'savings_minimum_deposit' => ['required', 'numeric', 'min:0'],
            'savings_withdrawal_limit' => ['nullable', 'numeric', 'min:0'],
            'savings_withdrawal_fee' => ['nullable', 'numeric', 'min:0'],
            
            // Shares settings
            'share_value' => ['required', 'numeric', 'min:0'],
            'min_shares_required' => ['required', 'integer', 'min:1'],
            'max_shares_allowed' => ['nullable', 'integer', 'min:1'],
            'shares_transfer_fee' => ['nullable', 'numeric', 'min:0'],
            'shares_dividend_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            
            // Transaction settings
            'transaction_processing_fee' => ['nullable', 'numeric', 'min:0'],
            'mobile_money_charge' => ['nullable', 'numeric', 'min:0'],
            'bank_transfer_charge' => ['nullable', 'numeric', 'min:0'],
            'cheque_processing_fee' => ['nullable', 'numeric', 'min:0'],
            'daily_transaction_limit' => ['nullable', 'numeric', 'min:0'],
            
            // Loan settings
            'default_loan_interest_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'default_loan_processing_fee' => ['nullable', 'numeric', 'min:0'],
            'default_loan_insurance_rate' => ['nullable', 'numeric', 'min:0'],
            'default_loan_penalty_rate' => ['nullable', 'numeric', 'min:0'],
            'loan_grace_period_days' => ['nullable', 'integer', 'min:0'],
            'max_loan_term_months' => ['nullable', 'integer', 'min:1'],
            
            // Dividend settings
            'dividend_calculation_method' => ['required', 'in:shares_based,savings_based,combined'],
            'dividend_minimum_shares' => ['nullable', 'integer', 'min:0'],
            'dividend_processing_fee' => ['nullable', 'numeric', 'min:0'],
            
            // Budget settings
            'budget_variance_threshold' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'budget_approval_required' => ['boolean'],
            'budget_over_expenditure_allowed' => ['boolean']
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated as $key => $value) {
                $this->updateSetting($key, $value, 'financial');
            }

            $this->logActivity('financial_settings_updated', 'Financial settings updated');
        });

        return redirect()->back()->with('success', 'Financial settings updated successfully.');
    }

    /**
     * Display loan settings
     */
    public function loan()
    {
        $settings = SystemSetting::where('group', 'loan')
            ->get()
            ->keyBy('key');

        return Inertia::render('Settings/Loan', [
            'settings' => $settings,
            'loanStatuses' => $this->getLoanStatuses(),
            'repaymentFrequencies' => $this->getRepaymentFrequencies()
        ]);
    }

    /**
     * Update loan settings
     */
    public function updateLoan(Request $request)
    {
        $validated = $request->validate([
            'loan_application_fee' => ['nullable', 'numeric', 'min:0'],
            'loan_appraisal_fee' => ['nullable', 'numeric', 'min:0'],
            'loan_processing_time_days' => ['nullable', 'integer', 'min:1'],
            'loan_disbursement_delay_days' => ['nullable', 'integer', 'min:0'],
            'max_loan_amount' => ['nullable', 'numeric', 'min:0'],
            'loan_to_savings_ratio' => ['nullable', 'numeric', 'min:1'],
            'loan_to_shares_ratio' => ['nullable', 'numeric', 'min:1'],
            'guarantor_required' => ['boolean'],
            'min_guarantors_required' => ['nullable', 'integer', 'min:0'],
            'max_guarantors_allowed' => ['nullable', 'integer', 'min:1'],
            'guarantor_loan_limit_ratio' => ['nullable', 'numeric', 'min:0'],
            'auto_calculate_repayment' => ['boolean'],
            'allow_loan_restructuring' => ['boolean'],
            'loan_restructuring_fee' => ['nullable', 'numeric', 'min:0'],
            'early_repayment_penalty' => ['nullable', 'numeric', 'min:0'],
            'late_payment_grace_days' => ['nullable', 'integer', 'min:0'],
            'arrears_notification_days' => ['nullable', 'integer', 'min:1'],
            'loan_writeoff_days' => ['nullable', 'integer', 'min:1'],
            'compound_interest' => ['boolean'],
            'interest_calculation_method' => ['required', 'in:reducing_balance,flat_rate,compound'],
            'repayment_frequency' => ['required', 'in:weekly,bi_weekly,monthly,quarterly']
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated as $key => $value) {
                $this->updateSetting($key, $value, 'loan');
            }

            $this->logActivity('loan_settings_updated', 'Loan settings updated');
        });

        return redirect()->back()->with('success', 'Loan settings updated successfully.');
    }

    /**
     * Display notification settings
     */
    public function notification()
    {
        $settings = SystemSetting::where('group', 'notification')
            ->get()
            ->keyBy('key');

        return Inertia::render('Settings/Notification', [
            'settings' => $settings,
            'notificationChannels' => $this->getNotificationChannels(),
            'notificationTypes' => $this->getNotificationTypes()
        ]);
    }

    /**
     * Update notification settings
     */
    public function updateNotification(Request $request)
    {
        $validated = $request->validate([
            // SMS settings
            'sms_enabled' => ['boolean'],
            'sms_provider' => ['nullable', 'string', 'in:africastalking,twilio,custom'],
            'sms_api_key' => ['nullable', 'string'],
            'sms_api_secret' => ['nullable', 'string'],
            'sms_sender_id' => ['nullable', 'string', 'max:11'],
            'sms_balance_threshold' => ['nullable', 'numeric', 'min:0'],
            
            // Email settings
            'email_enabled' => ['boolean'],
            'email_driver' => ['nullable', 'string', 'in:smtp,mailgun,ses,postmark'],
            'email_host' => ['nullable', 'string'],
            'email_port' => ['nullable', 'integer'],
            'email_username' => ['nullable', 'string'],
            'email_password' => ['nullable', 'string'],
            'email_encryption' => ['nullable', 'string', 'in:tls,ssl'],
            'email_from_address' => ['nullable', 'email'],
            'email_from_name' => ['nullable', 'string'],
            
            // Push notification settings
            'push_enabled' => ['boolean'],
            'push_server_key' => ['nullable', 'string'],
            'push_vapid_public_key' => ['nullable', 'string'],
            'push_vapid_private_key' => ['nullable', 'string'],
            
            // Notification triggers
            'notify_on_deposit' => ['boolean'],
            'notify_on_withdrawal' => ['boolean'],
            'notify_on_loan_approval' => ['boolean'],
            'notify_on_loan_disbursement' => ['boolean'],
            'notify_on_loan_repayment' => ['boolean'],
            'notify_on_dividend_payment' => ['boolean'],
            'notify_on_account_activation' => ['boolean'],
            'notify_on_account_suspension' => ['boolean'],
            'notify_on_password_change' => ['boolean'],
            'notify_on_loan_arrears' => ['boolean'],
            'arrears_notification_frequency' => ['nullable', 'in:daily,weekly,monthly'],
            
            // Notification timing
            'notification_batch_size' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'notification_retry_attempts' => ['nullable', 'integer', 'min:1', 'max:10'],
            'notification_retry_delay' => ['nullable', 'integer', 'min:1']
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated as $key => $value) {
                $this->updateSetting($key, $value, 'notification');
            }

            $this->logActivity('notification_settings_updated', 'Notification settings updated');
        });

        return redirect()->back()->with('success', 'Notification settings updated successfully.');
    }

    /**
     * Display security settings
     */
    public function security()
    {
        $settings = SystemSetting::where('group', 'security')
            ->get()
            ->keyBy('key');

        return Inertia::render('Settings/Security', [
            'settings' => $settings,
            'sessionLifetime' => config('session.lifetime'),
            'recentSessions' => $this->getRecentSessions(),
            'loginAttempts' => $this->getRecentLoginAttempts()
        ]);
    }

    /**
     * Update security settings
     */
    public function updateSecurity(Request $request)
    {
        $validated = $request->validate([
            // Password policy
            'password_min_length' => ['required', 'integer', 'min:6', 'max:50'],
            'password_require_uppercase' => ['boolean'],
            'password_require_lowercase' => ['boolean'],
            'password_require_numbers' => ['boolean'],
            'password_require_symbols' => ['boolean'],
            'password_expiry_days' => ['nullable', 'integer', 'min:1'],
            'password_history_count' => ['nullable', 'integer', 'min:0'],
            
            // Account security
            'max_login_attempts' => ['required', 'integer', 'min:1', 'max:20'],
            'lockout_duration_minutes' => ['required', 'integer', 'min:1'],
            'session_timeout_minutes' => ['required', 'integer', 'min:5'],
            'require_2fa' => ['boolean'],
            'force_2fa_for_admin' => ['boolean'],
            'otp_expiry_minutes' => ['required', 'integer', 'min:1', 'max:60'],
            
            // API security
            'api_rate_limit' => ['required', 'integer', 'min:1'],
            'api_rate_limit_window' => ['required', 'integer', 'min:1'],
            'api_token_expiry_days' => ['nullable', 'integer', 'min:1'],
            'api_require_https' => ['boolean'],
            
            // Audit settings
            'audit_log_retention_days' => ['nullable', 'integer', 'min:1'],
            'audit_sensitive_actions' => ['boolean'],
            'audit_failed_logins' => ['boolean'],
            'audit_data_changes' => ['boolean'],
            
            // IP restrictions
            'ip_whitelist_enabled' => ['boolean'],
            'ip_whitelist' => ['nullable', 'string'],
            'ip_blacklist_enabled' => ['boolean'],
            'ip_blacklist' => ['nullable', 'string'],
            
            // Encryption
            'encrypt_sensitive_data' => ['boolean'],
            'backup_encryption_enabled' => ['boolean'],
            'file_upload_scan_enabled' => ['boolean'],
            'max_file_upload_size' => ['nullable', 'integer', 'min:1']
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated as $key => $value) {
                $this->updateSetting($key, $value, 'security');
            }

            $this->logActivity('security_settings_updated', 'Security settings updated');
        });

        return redirect()->back()->with('success', 'Security settings updated successfully.');
    }

    /**
     * Display backup settings
     */
    public function backup()
    {
        $backups = $this->getBackupHistory();
        $settings = SystemSetting::where('group', 'backup')
            ->get()
            ->keyBy('key');

        return Inertia::render('Settings/Backup', [
            'settings' => $settings,
            'backups' => $backups,
            'diskSpace' => $this->getDiskSpace(),
            'backupSize' => $this->getBackupSize()
        ]);
    }

    /**
     * Create a new backup
     */
    public function createBackup(Request $request)
    {
        $validated = $request->validate([
            'include_files' => ['boolean'],
            'include_database' => ['boolean'],
            'backup_type' => ['required', 'in:full,database,files'],
            'description' => ['nullable', 'string', 'max:255']
        ]);

        try {
            $backupService = new BackupService();
            $backupPath = $backupService->createBackup($validated);

            $this->logActivity('backup_created', 'System backup created', [
                'backup_path' => $backupPath,
                'backup_type' => $validated['backup_type']
            ]);

            return redirect()->back()->with('success', 'Backup created successfully.');
        } catch (\Exception $e) {
            Log::error('Backup creation failed', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return redirect()->back()->with('error', 'Backup creation failed: ' . $e->getMessage());
        }
    }

    /**
     * Restore from backup
     */
    public function restoreBackup(Request $request)
    {
        $validated = $request->validate([
            'backup_file' => ['required', 'string'],
            'restore_type' => ['required', 'in:full,database,files'],
            'confirm_restore' => ['required', 'boolean', 'accepted']
        ]);

        try {
            $backupService = new BackupService();
            $result = $backupService->restoreBackup($validated['backup_file'], $validated['restore_type']);

            $this->logActivity('backup_restored', 'System restored from backup', [
                'backup_file' => $validated['backup_file'],
                'restore_type' => $validated['restore_type']
            ]);

            return redirect()->back()->with('success', 'System restored successfully.');
        } catch (\Exception $e) {
            Log::error('Backup restoration failed', [
                'error' => $e->getMessage(),
                'backup_file' => $validated['backup_file'],
                'user_id' => Auth::id()
            ]);

            return redirect()->back()->with('error', 'Backup restoration failed: ' . $e->getMessage());
        }
    }

    /**
     * Helper method to update system settings
     */
    private function updateSetting($key, $value, $group = 'general')
    {
        $type = $this->getSettingType($value);
        
        SystemSetting::updateOrCreate(
            ['key' => $key],
            [
                'value' => $type === 'json' ? json_encode($value) : $value,
                'type' => $type,
                'group' => $group,
                'is_public' => $this->isPublicSetting($key)
            ]
        );
    }

    /**
     * Determine the type of a setting value
     */
    private function getSettingType($value)
    {
        if (is_bool($value)) {
            return 'boolean';
        } elseif (is_numeric($value)) {
            return 'number';
        } elseif (is_array($value) || is_object($value)) {
            return 'json';
        } else {
            return 'string';
        }
    }

    /**
     * Determine if a setting should be public
     */
    private function isPublicSetting($key)
    {
        $privateSetting = [
            'password', 'secret', 'key', 'token', 'api_key', 'api_secret',
            'email_password', 'sms_api_secret', 'push_vapid_private_key'
        ];

        foreach ($privateSetting as $private) {
            if (stripos($key, $private) !== false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get settings grouped by category
     */
    private function getSettingsGrouped()
    {
        return SystemSetting::where('is_public', true)
            ->get()
            ->groupBy('group')
            ->map(function ($settings) {
                return $settings->keyBy('key');
            });
    }

    /**
     * Log system activity
     */
    private function logActivity($action, $description, $metadata = [])
    {
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'model_type' => SystemSetting::class,
            'model_id' => null,
            'old_values' => null,
            'new_values' => json_encode($metadata),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);
    }

    /**
     * Get system information
     */
    private function getSystemInfo()
    {
        return [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'database_version' => DB::select('SELECT VERSION() as version')[0]->version ?? 'Unknown',
            'disk_space' => $this->getDiskSpace(),
            'memory_usage' => memory_get_usage(true),
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time')
        ];
    }

    /**
     * Get recent system activity
     */
    private function getRecentActivity()
    {
        return AuditLog::with('user')
            ->where('action', 'like', '%settings%')
            ->latest()
            ->take(10)
            ->get();
    }

    /**
     * Get last backup information
     */
    private function getLastBackupInfo()
    {
        $backups = $this->getBackupHistory();
        return $backups->first();
    }

    /**
     * Get backup history
     */
    private function getBackupHistory()
    {
        $backupPath = storage_path('app/backups');
        
        if (!is_dir($backupPath)) {
            return collect([]);
        }

        return collect(scandir($backupPath))
            ->filter(function ($file) {
                return !in_array($file, ['.', '..']) && pathinfo($file, PATHINFO_EXTENSION) === 'zip';
            })
            ->map(function ($file) use ($backupPath) {
                $fullPath = $backupPath . '/' . $file;
                return [
                    'name' => $file,
                    'size' => filesize($fullPath),
                    'created_at' => Carbon::createFromTimestamp(filemtime($fullPath)),
                    'path' => $fullPath
                ];
            })
            ->sortByDesc('created_at');
    }

    /**
     * Get disk space information
     */
    private function getDiskSpace()
    {
        $free = disk_free_space(storage_path());
        $total = disk_total_space(storage_path());
        $used = $total - $free;

        return [
            'free' => $free,
            'total' => $total,
            'used' => $used,
            'percentage' => round(($used / $total) * 100, 2)
        ];
    }

    /**
     * Get backup size
     */
    private function getBackupSize()
    {
        $backupPath = storage_path('app/backups');
        $size = 0;

        if (is_dir($backupPath)) {
            foreach (scandir($backupPath) as $file) {
                if ($file !== '.' && $file !== '..') {
                    $size += filesize($backupPath . '/' . $file);
                }
            }
        }

        return $size;
    }

    /**
     * Get available currencies
     */
    private function getCurrencies()
    {
        return [
            'USD' => 'US Dollar',
            'EUR' => 'Euro',
            'GBP' => 'British Pound',
            'KES' => 'Kenyan Shilling',
            'UGX' => 'Ugandan Shilling',
            'TZS' => 'Tanzanian Shilling',
            'NGN' => 'Nigerian Naira',
            'GHS' => 'Ghanaian Cedi',
            'ZAR' => 'South African Rand'
        ];
    }

    /**
     * Get available timezones
     */
    private function getTimezones()
    {
        return [
            'Africa/Nairobi' => 'East Africa Time (EAT)',
            'Africa/Lagos' => 'West Africa Time (WAT)',
            'Africa/Cairo' => 'Eastern European Time (EET)',
            'Africa/Johannesburg' => 'South Africa Standard Time (SAST)',
            'UTC' => 'Coordinated Universal Time (UTC)',
            'America/New_York' => 'Eastern Standard Time (EST)',
            'Europe/London' => 'Greenwich Mean Time (GMT)',
            'Asia/Dubai' => 'Gulf Standard Time (GST)'
        ];
    }

    /**
     * Get available languages
     */
    private function getLanguages()
    {
        return [
            'en' => 'English',
            'sw' => 'Swahili',
            'fr' => 'French',
            'ar' => 'Arabic',
            'es' => 'Spanish',
            'pt' => 'Portuguese'
        ];
    }

    /**
     * Get account types
     */
    private function getAccountTypes()
    {
        return [
            'savings' => 'Savings Account',
            'shares' => 'Shares Account',
            'deposits' => 'Fixed Deposits'
        ];
    }

    /**
     * Get interest calculation methods
     */
    private function getInterestCalculationMethods()
    {
        return [
            'daily' => 'Daily Compounding',
            'monthly' => 'Monthly Compounding',
            'quarterly' => 'Quarterly Compounding',
            'annually' => 'Annual Compounding'
        ];
    }

    /**
     * Get loan statuses
     */
    private function getLoanStatuses()
    {
        return [
            'pending' => 'Pending',
            'under_review' => 'Under Review',
            'approved' => 'Approved',
            'disbursed' => 'Disbursed',
            'active' => 'Active',
            'completed' => 'Completed',
            'defaulted' => 'Defaulted',
            'written_off' => 'Written Off',
            'rejected' => 'Rejected'
        ];
    }

    /**
     * Get repayment frequencies
     */
    private function getRepaymentFrequencies()
    {
        return [
            'weekly' => 'Weekly',
            'bi_weekly' => 'Bi-Weekly',
            'monthly' => 'Monthly',
            'quarterly' => 'Quarterly'
        ];
    }

    /**
     * Get notification channels
     */
    private function getNotificationChannels()
    {
        return [
            'sms' => 'SMS',
            'email' => 'Email',
            'push' => 'Push Notifications',
            'system' => 'System Notifications'
        ];
    }

    /**
     * Get notification types
     */
    private function getNotificationTypes()
    {
        return [
            'transaction' => 'Transaction Notifications',
            'loan' => 'Loan Notifications',
            'dividend' => 'Dividend Notifications',
            'general' => 'General Notifications',
            'system' => 'System Notifications'
        ];
    }

    /**
     * Get recent login sessions
     */
    private function getRecentSessions()
    {
        // This would need to be implemented based on your session tracking mechanism
        return collect([]);
    }

    /**
     * Get recent login attempts
     */
    private function getRecentLoginAttempts()
    {
        return AuditLog::where('action', 'login_attempt')
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->latest()
            ->take(50)
            ->get();
    }
}