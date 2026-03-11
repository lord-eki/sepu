<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ChartOfAccountSeeder extends Seeder
{
 
    public function run(): void
    {
        DB::table('charts_of_accounts')->delete();

        $now = Carbon::now();

        $accounts = [
            // ── ASSETS ──────────────────────────────────────────────────
            ['10000','ASSETS',              null,    'header',       'debit',  'Active'],
            ['11000','Current Assets',      '10000', 'asset',        'debit',  'Active'],
            ['11100','Cash on Hand',        '11000', 'asset',        'debit',  'Active'],
            ['11200','Bank Accounts',       '11000', 'asset',        'debit',  'Active'],
            ['11201','Bank – Operations Account','11200','asset',    'debit',  'Active'],
            ['11202','Bank – FOSA Account', '11200', 'asset',        'debit',  'Active'],
            ['11300','Member Loans',        '11000', 'asset',        'debit',  'Active'],
            ['11301','Performing Loans',    '11300', 'asset',        'debit',  'Active'],
            ['11302','Non Performing Loans','11300', 'asset',        'debit',  'Active'],
            ['11400','Loan Interest Receivable','11000','asset',     'debit',  'Active'],
            ['11500','Other Receivables',   '11000', 'asset',        'debit',  'Active'],
            ['11600','Prepaid Expenses',    '11000', 'asset',        'debit',  'Active'],
            ['11700','Investments',         '10000', 'asset',        'debit',  'Active'],
            ['11701','Fixed Deposit Investments','11700','asset',    'debit',  'Active'],
            ['11702','Shares Investments',  '11700', 'asset',        'debit',  'Active'],
            ['12000','Non Current Assets',  '10000', 'asset',        'debit',  'Active'],
            ['12100','Office Equipment',    '12000', 'asset',        'debit',  'Active'],
            ['12200','Furniture & Fittings','12000', 'asset',        'debit',  'Active'],
            ['12300','Computers & IT Equipment','12000','asset',     'debit',  'Active'],
            ['12400','Motor Vehicles',      '12000', 'asset',        'debit',  'Active'],
            ['12900','Accumulated Depreciation','12000','contra_asset','credit','Active'],

            // ── LIABILITIES ─────────────────────────────────────────────
            ['20000','LIABILITIES',         null,    'header',       'credit', 'Active'],
            ['21000','Current Liabilities', '20000', 'liability',    'credit', 'Active'],
            ['21100','Members Deposits',    '21000', 'liability',    'credit', 'Active'],
            ['21200','Members Savings',     '21000', 'liability',    'credit', 'Active'],
            ['21300','Accounts Payable',    '21000', 'liability',    'credit', 'Active'],
            ['21400','Accrued Expenses',    '21000', 'liability',    'credit', 'Active'],
            ['21500','Audit Fees Payable',  '21000', 'liability',    'credit', 'Active'],
            ['21600','Tax Payable',         '21000', 'liability',    'credit', 'Active'],
            ['21700','Dividends Payable',   '21000', 'liability',    'credit', 'Active'],
            ['22000','Long Term Liabilities','20000','liability',    'credit', 'Active'],
            ['22100','KUSCO Loan',          '22000', 'liability',    'credit', 'Active'],
            ['22200','Other Borrowings',    '22000', 'liability',    'credit', 'Active'],
            ['22300','Interest Payable on Loans','22000','liability','credit', 'Active'],

            // ── EQUITY ──────────────────────────────────────────────────
            ['30000','EQUITY',              null,    'header',       'credit', 'Active'],
            ['31000','Share Capital',       '30000', 'equity',       'credit', 'Active'],
            ['32000','Statutory Reserves',  '30000', 'equity',       'credit', 'Active'],
            ['33000','Retained Earnings',   '30000', 'equity',       'credit', 'Active'],
            ['34000','Current Year Surplus','30000', 'equity',       'credit', 'Active'],

            // ── INCOME ──────────────────────────────────────────────────
            ['40000','INCOME',              null,    'header',       'credit', 'Active'],
            ['41000','Interest Income',     '40000', 'revenue',      'credit', 'Active'],
            ['41100','Interest on Members Loans','41000','revenue',  'credit', 'Active'],
            ['41200','Interest on Investments','41000','revenue',    'credit', 'Active'],
            ['42000','Fees & Commission Income','40000','revenue',   'credit', 'Active'],
            ['42100','Entrance Fees',       '42000', 'revenue',      'credit', 'Active'],
            ['42200','Loan Processing Fees','42000', 'revenue',      'credit', 'Active'],
            ['42300','Loan Appraisal Fees', '42000', 'revenue',      'credit', 'Active'],
            ['42400','Penalty Charges',     '42000', 'revenue',      'credit', 'Active'],
            ['42500','Withdrawal Fees',     '42000', 'revenue',      'credit', 'Active'],
            ['43000','Other Income',        '40000', 'revenue',      'credit', 'Active'],
            ['43100','Other Interest Income','43000','revenue',      'credit', 'Active'],
            ['43200','Miscellaneous Income','43000', 'revenue',      'credit', 'Active'],

            // ── EXPENSES ────────────────────────────────────────────────
            ['50000','EXPENSES',            null,    'header',       'debit',  'Active'],
            ['51000','Administrative Expenses','50000','expense',    'debit',  'Active'],
            ['51100','Printing & Stationery','51000','expense',      'debit',  'Active'],
            ['51200','Bank Charges',        '51000', 'expense',      'debit',  'Active'],
            ['51300','Telephone & Communication','51000','expense',  'debit',  'Active'],
            ['51400','Insurance Premium',   '51000', 'expense',      'debit',  'Active'],
            ['51500','Miscellaneous Expenses','51000','expense',     'debit',  'Active'],
            ['52000','Governance Expenses', '50000', 'expense',      'debit',  'Active'],
            ['52100','Committee Allowances','52000', 'expense',      'debit',  'Active'],
            ['52200','Committee Travelling','52000', 'expense',      'debit',  'Active'],
            ['52300','AGM Expenses',        '52000', 'expense',      'debit',  'Active'],
            ['53000','Professional Fees',   '50000', 'expense',      'debit',  'Active'],
            ['53100','Bookkeeping Fees',    '53000', 'expense',      'debit',  'Active'],
            ['53200','Audit Fees',          '53000', 'expense',      'debit',  'Active'],
            ['53300','Legal Charges',       '53000', 'expense',      'debit',  'Active'],
            ['53400','Tax Filing Charges',  '53000', 'expense',      'debit',  'Active'],
            ['53500','Dividends Computation','53000','expense',      'debit',  'Active'],
            ['54000','Training & Education','50000', 'expense',      'debit',  'Active'],
            ['54100','Member Education',    '54000', 'expense',      'debit',  'Active'],
            ['55000','Loan Management Costs','50000','expense',      'debit',  'Active'],
            ['55100','Provision for Loan Loss','55000','expense',    'debit',  'Active'],
            ['55200','Loan Appraisal Expenses','55000','expense',    'debit',  'Active'],
            ['56000','Financing Costs',     '50000', 'expense',      'debit',  'Active'],
            ['56100','KUSCO Loan Interest', '56000', 'expense',      'debit',  'Active'],
        ];

        // ── Pass 1: insert all rows (parent_account_id = null) ──────────
        $codeToId = [];

        foreach ($accounts as $idx => $row) {
            [$code, $name, $parentCode, $type, $normalBalance, $status] = $row;

            $level = $this->resolveLevel($code);
            $category = $this->resolveCategory($type, $normalBalance, $code);

            $id = DB::table('charts_of_accounts')->insertGetId([
                'account_code'      => $code,
                'account_name'      => $name,
                'account_type'      => $type,
                'account_category'  => $category,
                'normal_balance'    => $normalBalance,
                'parent_account_id' => null,   // resolved in pass 2
                'description'       => $name,
                'opening_balance'   => 0.00,
                'current_balance'   => 0.00,
                'is_active'         => $status === 'Active' ? 1 : 0,
                'is_system_account' => in_array($type, ['header']) ? 1 : 0,
                'level'             => $level,
                'created_at'        => $now,
                'updated_at'        => $now,
            ]);

            $codeToId[$code] = $id;
        }

        // ── Pass 2: set parent_account_id ───────────────────────────────
        foreach ($accounts as $row) {
            [$code, , $parentCode] = $row;
            if ($parentCode && isset($codeToId[$parentCode])) {
                DB::table('charts_of_accounts')
                    ->where('id', $codeToId[$code])
                    ->update(['parent_account_id' => $codeToId[$parentCode]]);
            }
        }
    }

    /** Derive level from account_code length / pattern */
    private function resolveLevel(string $code): int
    {
        // x0000 = level 1 (header), xx000 = level 2, xxx00 = level 3, xxxxx = level 4
        if (str_ends_with($code, '0000')) return 1;
        if (str_ends_with($code, '000'))  return 2;
        if (str_ends_with($code, '00'))   return 3;
        if (str_ends_with($code, '0'))    return 4;
        return 5;
    }

    /** Map type + normal_balance to the existing account_category enum */
    private function resolveCategory(string $type, string $normalBalance, string $code): string
    {
        return match($type) {
            'asset', 'contra_asset' => str_starts_with($code, '12') ? 'fixed_asset' : 'current_asset',
            'liability'             => str_starts_with($code, '22') ? 'long_term_liability' : 'current_liability',
            'equity'                => $code === '33000'            ? 'retained_earnings'   : 'member_equity',
            'revenue'               => 'operating_revenue',
            'expense'               => 'operating_expense',
            'header'                => match(true) {
                str_starts_with($code, '1') => 'current_asset',
                str_starts_with($code, '2') => 'current_liability',
                str_starts_with($code, '3') => 'member_equity',
                str_starts_with($code, '4') => 'operating_revenue',
                default                     => 'operating_expense',
            },
            default => 'current_asset',
        };
    }
}