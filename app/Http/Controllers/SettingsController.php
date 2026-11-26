<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateFinancialSettingsRequest;
use App\Http\Requests\StoreSettingsRequest;
use App\Http\Requests\UpdateLoanSettingsRequest;
use App\Http\Requests\UpdateNotificationSettingRequest;
use App\Http\Requests\UpdateSecuritySettingRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Storage, Artisan, Hash, Auth, Cache, Log};
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use App\Models\{SystemSetting, User, AuditLog};
use App\Services\{BackupService, NotificationService};
use App\Support\ErrorMessage;
use Carbon\Carbon;

class SettingsController extends Controller
{
    /**
     * Display the settings overview page
     */
    public function index()
    {
        $settings = $this->getSettingsGrouped();
        
        return Inertia::render('Admin/Settings/Index', [
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

        return Inertia::render('Admin/Settings/General', [
            'settings' => $settings,
            'currencies' => $this->getCurrencies(),
            'timezones' => $this->getTimezones(),
            'languages' => $this->getLanguages()
        ]);
    }

    /**
     * Update general settings
     */

    public function updateGeneral(StoreSettingsRequest $request)
    {
    
        $validated = $request->validated();

        DB::transaction(function () use ($validated, $request) {

            $booleanFields = [
                'maintenance_mode',
                'member_registration_open',
                'auto_generate_member_id',
                'require_email_verification',
                'require_phone_verification'
            ];

            foreach ($booleanFields as $field) {
                $validated[$field] = $validated->boolean($field);
            }

            foreach ($validated as $key => $value) {

                if (is_null($value)) {
                    continue;
                }

                if (in_array($key, ['logo', 'favicon'])) {

                    if ($validated->hasFile($key)) {
                        $oldSetting = SystemSetting::where('key', $key)->first();

                        if ($oldSetting && $oldSetting->value) {
                            Storage::disk('public')->delete($oldSetting->value);
                        }

                        $path = $validated->file($key)->store('settings', 'public');
                        $this->updateSetting($key, $path, 'general');
                    }

                } else {

                    $existing = SystemSetting::where('key', $key)->first();

                    if (!$existing || $existing->value != $value) {
                        $this->updateSetting($key, $value, 'general');
                    }
                }
            }

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

        return Inertia::render('Admin/Settings/Financial', [
            'settings' => $settings,
            'accountTypes' => $this->getAccountTypes(),
            'interestCalculationMethods' => $this->getInterestCalculationMethods()
        ]);
    }

    /**
     * Update financial settings
     */
    public function updateFinancial(StoreFinancialSettingsRequest $request)
    {
        $validated = $request->validated();

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

        return Inertia::render('Admin/Settings/Loan', [
            'settings' => $settings,
            'loanStatuses' => $this->getLoanStatuses(),
            'repaymentFrequencies' => $this->getRepaymentFrequencies()
        ]);
    }

    /**
     * Update loan settings
     */
    public function updateLoan(UpdateLoanSettingsRequest $request)
    {
        $validated = $request->validated();

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

        return Inertia::render('Admin/Settings/Notification', [
            'settings' => $settings,
            'notificationChannels' => $this->getNotificationChannels(),
            'notificationTypes' => $this->getNotificationTypes()
        ]);
    }

    /**
     * Update notification settings
     */
    public function updateNotification(UpdateNotificationSettingRequest $request)
    {
        $validated = $request->validated();

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

        return Inertia::render('Admin/Settings/Security', [
            'settings' => $settings,
            'sessionLifetime' => config('session.lifetime'),
            'recentSessions' => $this->getRecentSessions(),
            'loginAttempts' => $this->getRecentLoginAttempts()
        ]);
    }

    /**
     * Update security settings
     */
    public function updateSecurity(UpdateSecuritySettingRequest $request)
    {
        $validated = $request->validated();

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

        return Inertia::render('Admin/Settings/Backup', [
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
                'error' => ErrorMessage::from($e),
                'user_id' => Auth::id()
            ]);

            return redirect()->back()->with('error', 'Backup creation failed: ' . \App\Support\ErrorMessage::from($e));
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
                'error' => ErrorMessage::from($e),
                'backup_file' => $validated['backup_file'],
                'user_id' => Auth::id()
            ]);

            return redirect()->back()->with('error', 'Backup restoration failed: ' . \App\Support\ErrorMessage::from($e));
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