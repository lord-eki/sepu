<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\LoanProduct; 


class LoanProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         $loanProducts = [
            [
                'name' => 'Normal Loan',
                'code' => 'NL001',
                'description' => 'Standard loan product for general purposes',
                'min_amount' => 5000.00,
                'max_amount' => 500000.00,
                'interest_rate' => 12.00,
                'min_term_months' => 6,
                'max_term_months' => 36,
                'processing_fee_rate' => 2.50,
                'insurance_rate' => 1.00,
                'grace_period_days' => 7,
                'penalty_rate' => 5.00,
                'eligibility_criteria' => [
                    'minimum_membership_months' => 6,
                    'minimum_shares_balance' => 2000,
                    'maximum_loan_to_shares_ratio' => 3,
                    'clean_credit_history' => true
                ],
                'required_documents' => [
                    'national_id_copy',
                    'passport_photo',
                    'payslip_3_months',
                    'bank_statement_3_months'
                ],
                'requires_guarantor' => true,
                'min_guarantors' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Emergency Loan',
                'code' => 'EL001',
                'description' => 'Quick loan for emergency situations',
                'min_amount' => 1000.00,
                'max_amount' => 50000.00,
                'interest_rate' => 15.00,
                'min_term_months' => 3,
                'max_term_months' => 12,
                'processing_fee_rate' => 1.50,
                'insurance_rate' => 0.50,
                'grace_period_days' => 3,
                'penalty_rate' => 7.50,
                'eligibility_criteria' => [
                    'minimum_membership_months' => 3,
                    'minimum_shares_balance' => 1000,
                    'maximum_loan_to_shares_ratio' => 2,
                    'clean_credit_history' => true
                ],
                'required_documents' => [
                    'national_id_copy',
                    'passport_photo'
                ],
                'requires_guarantor' => false,
                'min_guarantors' => 0,
                'is_active' => true,
            ],
            [
                'name' => 'Development Loan',
                'code' => 'DL001',
                'description' => 'Long-term loan for development projects',
                'min_amount' => 50000.00,
                'max_amount' => 2000000.00,
                'interest_rate' => 10.00,
                'min_term_months' => 12,
                'max_term_months' => 60,
                'processing_fee_rate' => 3.00,
                'insurance_rate' => 1.50,
                'grace_period_days' => 14,
                'penalty_rate' => 4.00,
                'eligibility_criteria' => [
                    'minimum_membership_months' => 12,
                    'minimum_shares_balance' => 10000,
                    'maximum_loan_to_shares_ratio' => 4,
                    'clean_credit_history' => true,
                    'proof_of_income' => true
                ],
                'required_documents' => [
                    'national_id_copy',
                    'passport_photo',
                    'payslip_6_months',
                    'bank_statement_6_months',
                    'project_proposal',
                    'quotations'
                ],
                'requires_guarantor' => true,
                'min_guarantors' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'School Fees Loan',
                'code' => 'SF001',
                'description' => 'Education loan for school fees payment',
                'min_amount' => 10000.00,
                'max_amount' => 300000.00,
                'interest_rate' => 8.00,
                'min_term_months' => 6,
                'max_term_months' => 24,
                'processing_fee_rate' => 2.00,
                'insurance_rate' => 0.75,
                'grace_period_days' => 10,
                'penalty_rate' => 3.50,
                'eligibility_criteria' => [
                    'minimum_membership_months' => 6,
                    'minimum_shares_balance' => 3000,
                    'maximum_loan_to_shares_ratio' => 3,
                    'clean_credit_history' => true
                ],
                'required_documents' => [
                    'national_id_copy',
                    'passport_photo',
                    'school_fees_structure',
                    'admission_letter',
                    'payslip_3_months'
                ],
                'requires_guarantor' => true,
                'min_guarantors' => 1,
                'is_active' => true,
            ],
        ];

        foreach ($loanProducts as $product) {
            LoanProduct::updateOrCreate(
                ['code' => $product['code']],
                $product
            );
        }
    }
    }

