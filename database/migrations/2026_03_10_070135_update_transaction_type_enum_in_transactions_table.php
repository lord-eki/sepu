<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        DB::statement("
            ALTER TABLE transactions
            MODIFY transaction_type ENUM(
                'deposit',
                'withdrawal',
                'loan_disbursement',
                'loan_repayment',
                'dividend_payment',
                'transfer',
                'fee',
                'penalty',
                'share_capital_contribution'
            )
        ");
    }

    public function down()
    {
        DB::statement("
            ALTER TABLE transactions
            MODIFY transaction_type ENUM(
                'deposit',
                'withdrawal',
                'loan_disbursement',
                'loan_repayment',
                'dividend_payment',
                'transfer',
                'fee',
                'penalty'
            )
        ");
    }
};