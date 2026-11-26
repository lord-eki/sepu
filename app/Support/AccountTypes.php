<?php

namespace App\Support;

class AccountTypes {

   public const ACCOUNT_TYPES = [
        'asset'        => 'Asset',
        'liability'    => 'Liability',
        'equity'       => 'Equity',
        'revenue'      => 'Income / Revenue',
        'expense'      => 'Expense',
        'contra_asset' => 'Contra Asset',
        'header'       => 'Header / Group',
    ];
}