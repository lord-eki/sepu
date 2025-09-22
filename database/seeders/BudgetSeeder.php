<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Budget;
use App\Models\BudgetItem;  


class BudgetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         $admin = User::where('role', 'admin')->first();
        
        $budget = Budget::updateOrCreate(
            ['budget_year' => 2025],
            [
                'title' => '2025 Annual Budget',
                'description' => 'Annual budget for SEPU SACCO operations',
                'total_budget' => 5000000.00,
                'status' => 'active',
                'start_date' => '2025-01-01',
                'end_date' => '2025-12-31',
                'created_by' => $admin->id,
                'approved_by' => $admin->id,
                'approval_date' => now(),
            ]
        );

        $budgetItems = [
            [
                'category' => 'Administration',
                'item_name' => 'Staff Salaries',
                'description' => 'Monthly salaries for all staff members',
                'budgeted_amount' => 1800000.00,
            ],
            [
                'category' => 'Administration',
                'item_name' => 'Office Rent',
                'description' => 'Monthly office rental payments',
                'budgeted_amount' => 360000.00,
            ],
            [
                'category' => 'Operations',
                'item_name' => 'Utilities',
                'description' => 'Electricity, water, and internet bills',
                'budgeted_amount' => 120000.00,
            ],
            [
                'category' => 'Operations',
                'item_name' => 'Stationery & Supplies',
                'description' => 'Office supplies and stationery',
                'budgeted_amount' => 60000.00,
            ],
            [
                'category' => 'Technology',
                'item_name' => 'System Maintenance',
                'description' => 'Software licenses and system maintenance',
                'budgeted_amount' => 150000.00,
            ],
            [
                'category' => 'Marketing',
                'item_name' => 'Member Education',
                'description' => 'Training and education programs for members',
                'budgeted_amount' => 200000.00,
            ],
            [
                'category' => 'Compliance',
                'item_name' => 'Audit Fees',
                'description' => 'External audit and compliance costs',
                'budgeted_amount' => 180000.00,
            ],
            [
                'category' => 'Reserves',
                'item_name' => 'Emergency Fund',
                'description' => 'Emergency reserve fund',
                'budgeted_amount' => 500000.00,
            ],
        ];

        foreach ($budgetItems as $item) {
            $item['budget_id'] = $budget->id;
            $item['spent_amount'] = 0.00;
            $item['remaining_amount'] = $item['budgeted_amount'];
            
            BudgetItem::updateOrCreate(
                [
                    'budget_id' => $budget->id,
                    'item_name' => $item['item_name']
                ],
                $item
            );
        }
    }
    
}
