<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ChartOfAccount;

class ChartsSeeder extends Seeder
{
    public function run()
    {
        $accounts = [
            // ASSETS (1000-1999)
            [
                'account_code' => '1000',
                'account_name' => 'ASSETS',
                'account_type' => 'asset',
                'account_category' => 'current_asset',
                'is_system_account' => true,
                'level' => 1,
                'children' => [
                    [
                        'account_code' => '1100',
                        'account_name' => 'Current Assets',
                        'account_category' => 'current_asset',
                        'level' => 2,
                        'children' => [
                            ['account_code' => '1101', 'account_name' => 'Cash on Hand', 'level' => 3],
                            ['account_code' => '1102', 'account_name' => 'Cash at Bank', 'level' => 3],
                            ['account_code' => '1103', 'account_name' => 'Mobile Money (M-PESA)', 'level' => 3],
                            ['account_code' => '1110', 'account_name' => 'Member Loans Receivable', 'level' => 3],
                            ['account_code' => '1111', 'account_name' => 'Loan Interest Receivable', 'level' => 3],
                        ]
                    ],
                ]
            ],
            
            // LIABILITIES (2000-2999)
            [
                'account_code' => '2000',
                'account_name' => 'LIABILITIES',
                'account_type' => 'liability',
                'account_category' => 'current_liability',
                'is_system_account' => true,
                'level' => 1,
                'children' => [
                    [
                        'account_code' => '2100',
                        'account_name' => 'Current Liabilities',
                        'account_category' => 'current_liability',
                        'level' => 2,
                        'children' => [
                            ['account_code' => '2101', 'account_name' => 'Member Share Capital', 'level' => 3],
                            ['account_code' => '2102', 'account_name' => 'Member Share Deposits', 'level' => 3],
                            ['account_code' => '2110', 'account_name' => 'Dividends Payable', 'level' => 3],
                        ]
                    ],
                ]
            ],
            
            // EQUITY (3000-3999)
            [
                'account_code' => '3000',
                'account_name' => 'EQUITY',
                'account_type' => 'equity',
                'account_category' => 'member_equity',
                'is_system_account' => true,
                'level' => 1,
                'children' => [
                    ['account_code' => '3100', 'account_name' => 'Retained Earnings', 'account_category' => 'retained_earnings', 'level' => 2],
                    ['account_code' => '3200', 'account_name' => 'Current Year Earnings', 'account_category' => 'retained_earnings', 'level' => 2],
                ]
            ],
            
            // REVENUE (4000-4999)
            [
                'account_code' => '4000',
                'account_name' => 'REVENUE',
                'account_type' => 'revenue',
                'account_category' => 'operating_revenue',
                'is_system_account' => true,
                'level' => 1,
                'children' => [
                    [
                        'account_code' => '4100',
                        'account_name' => 'Operating Revenue',
                        'account_category' => 'operating_revenue',
                        'level' => 2,
                        'children' => [
                            ['account_code' => '4101', 'account_name' => 'Loan Interest Income', 'level' => 3],
                            ['account_code' => '4102', 'account_name' => 'Loan Processing Fees', 'level' => 3],
                            ['account_code' => '4103', 'account_name' => 'Loan Insurance Fees', 'level' => 3],
                        ]
                    ],
                ]
            ],
            
            // EXPENSES (5000-5999)
            [
                'account_code' => '5000',
                'account_name' => 'EXPENSES',
                'account_type' => 'expense',
                'account_category' => 'operating_expense',
                'is_system_account' => true,
                'level' => 1,
                'children' => [
                    [
                        'account_code' => '5100',
                        'account_name' => 'Operating Expenses',
                        'account_category' => 'operating_expense',
                        'level' => 2,
                        'children' => [
                            ['account_code' => '5101', 'account_name' => 'Salaries & Wages', 'level' => 3],
                            ['account_code' => '5102', 'account_name' => 'Office Rent', 'level' => 3],
                            ['account_code' => '5103', 'account_name' => 'Utilities', 'level' => 3],
                        ]
                    ],
                ]
            ],
        ];

        $this->createAccounts($accounts);
    }

    private function createAccounts($accounts, $parentId = null)
    {
        foreach ($accounts as $accountData) {
            $children = $accountData['children'] ?? [];
            unset($accountData['children']);

            $account = ChartOfAccount::create(array_merge([
                'parent_account_id' => $parentId,
                'description' => $accountData['account_name'],
                'opening_balance' => 0,
                'current_balance' => 0,
                'is_active' => true,
                'is_system_account' => $accountData['is_system_account'] ?? false,
                'account_type' => $accountData['account_type'] ?? 'asset',
                'account_category' => $accountData['account_category'] ?? 'current_asset',
            ], $accountData));

            if (!empty($children)) {
                $this->createAccounts($children, $account->id);
            }
        }
    }
}