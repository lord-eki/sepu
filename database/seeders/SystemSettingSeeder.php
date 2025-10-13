<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SystemSetting;

class SystemSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         $settings = [
            // General Settings
            ['key' => 'sacco_name', 'value' => 'SEPU SACCO SOCIETY', 'type' => 'string', 'description' => 'SACCO Name', 'group' => 'general', 'is_public' => true],
            ['key' => 'sacco_registration_number', 'value' => 'SACCO/REG/2025/001', 'type' => 'string', 'description' => 'SACCO Registration Number', 'group' => 'general', 'is_public' => true],
            ['key' => 'sacco_email', 'value' => 'info@sepusacco.co.ke', 'type' => 'string', 'description' => 'SACCO Email Address', 'group' => 'general', 'is_public' => true],
            ['key' => 'sacco_phone', 'value' => '+254700000000', 'type' => 'string', 'description' => 'SACCO Phone Number', 'group' => 'general', 'is_public' => true],
            ['key' => 'sacco_address', 'value' => 'P.O. Box 123, Machakos, Kenya', 'type' => 'string', 'description' => 'SACCO Address', 'group' => 'general', 'is_public' => true],
            
            // Financial Settings
            ['key' => 'minimum_share_contribution', 'value' => '1000', 'type' => 'number', 'description' => 'Minimum Monthly Share Contribution', 'group' => 'financial', 'is_public' => false],
            ['key' => 'membership_fee', 'value' => '500', 'type' => 'number', 'description' => 'One-time Membership Fee', 'group' => 'financial', 'is_public' => true],
            ['key' => 'dividend_calculation_formula', 'value' => 'shares_balance * dividend_rate', 'type' => 'string', 'description' => 'Formula for calculating member dividends', 'group' => 'financial', 'is_public' => false],
            ['key' => 'loan_processing_fee_rate', 'value' => '2.5', 'type' => 'number', 'description' => 'Default loan processing fee percentage', 'group' => 'financial', 'is_public' => false],
            ['key' => 'loan_insurance_rate', 'value' => '1.0', 'type' => 'number', 'description' => 'Default loan insurance percentage', 'group' => 'financial', 'is_public' => false],
            
            // Loan Settings
            ['key' => 'maximum_loan_multiplier', 'value' => '3', 'type' => 'number', 'description' => 'Maximum loan as multiple of shares', 'group' => 'loans', 'is_public' => false],
            ['key' => 'minimum_membership_period_months', 'value' => '6', 'type' => 'number', 'description' => 'Minimum membership period before loan eligibility', 'group' => 'loans', 'is_public' => true],
            ['key' => 'loan_grace_period_days', 'value' => '7', 'type' => 'number', 'description' => 'Grace period for loan repayments', 'group' => 'loans', 'is_public' => false],
            ['key' => 'loan_penalty_rate', 'value' => '5.0', 'type' => 'number', 'description' => 'Monthly penalty rate for overdue loans', 'group' => 'loans', 'is_public' => false],
            
            // Notification Settings
            ['key' => 'sms_notifications_enabled', 'value' => 'true', 'type' => 'boolean', 'description' => 'Enable SMS notifications', 'group' => 'notifications', 'is_public' => false],
            ['key' => 'email_notifications_enabled', 'value' => 'true', 'type' => 'boolean', 'description' => 'Enable email notifications', 'group' => 'notifications', 'is_public' => false],
            ['key' => 'sms_sender_id', 'value' => 'SEPUSACCO', 'type' => 'string', 'description' => 'SMS Sender ID', 'group' => 'notifications', 'is_public' => false],
            
            // System Settings
            ['key' => 'system_maintenance_mode', 'value' => 'false', 'type' => 'boolean', 'description' => 'Enable maintenance mode', 'group' => 'system', 'is_public' => false],
            ['key' => 'auto_generate_statements', 'value' => 'true', 'type' => 'boolean', 'description' => 'Auto-generate monthly statements', 'group' => 'system', 'is_public' => false],
            ['key' => 'backup_frequency_hours', 'value' => '24', 'type' => 'number', 'description' => 'Database backup frequency in hours', 'group' => 'system', 'is_public' => false],
            
            // Mobile Money Settings
            ['key' => 'mpesa_paybill_number', 'value' => '123456', 'type' => 'string', 'description' => 'M-Pesa Paybill Number', 'group' => 'mobile_money', 'is_public' => true],
            ['key' => 'mpesa_shortcode', 'value' => '123456', 'type' => 'string', 'description' => 'M-Pesa Business Shortcode', 'group' => 'mobile_money', 'is_public' => false],
            ['key' => 'mpesa_consumer_key', 'value' => '', 'type' => 'string', 'description' => 'M-Pesa Consumer Key', 'group' => 'mobile_money', 'is_public' => false],
            ['key' => 'mpesa_consumer_secret', 'value' => '', 'type' => 'string', 'description' => 'M-Pesa Consumer Secret', 'group' => 'mobile_money', 'is_public' => false],
            ['key' => 'mpesa_passkey', 'value' => '', 'type' => 'string', 'description' => 'M-Pesa Lipa Na M-Pesa Passkey', 'group' => 'mobile_money', 'is_public' => false],
            
            // Account Settings
            ['key' => 'account_number_prefix', 'value' => 'SEPU', 'type' => 'string', 'description' => 'Account number prefix', 'group' => 'accounts', 'is_public' => false],
            ['key' => 'membership_id_prefix', 'value' => 'MEM', 'type' => 'string', 'description' => 'Membership ID prefix', 'group' => 'accounts', 'is_public' => false],
            ['key' => 'loan_number_prefix', 'value' => 'LN', 'type' => 'string', 'description' => 'Loan number prefix', 'group' => 'accounts', 'is_public' => false],
            ['key' => 'voucher_number_prefix', 'value' => 'PV', 'type' => 'string', 'description' => 'Payment voucher number prefix', 'group' => 'accounts', 'is_public' => false],
        ];

        foreach ($settings as $setting) {
            SystemSetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
    }

