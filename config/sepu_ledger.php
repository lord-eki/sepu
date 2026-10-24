<?php

return [
    'accounts' => [
        'cash'                  => '11100',
        'bank'                  => '11201',
        'member_deposits'       => '21100', 
        'share_capital'         => '31000',  
        'dividends_payable'     => '21700',  
        'loans_performing'      => '11301',  
        'interest_receivable'   => '11400',
        'interest_income'       => '41100',
        'processing_fee_income' => '42200',
        'penalty_income'        => '42400',
        'withdrawal_fee_income' => '42500',
        'entrance_fee_income'   => '42100',
    ],

    'cash_by_method' => [
        'cash'            => 'cash',
        'mobile_money'    => 'bank',
        'bank_transfer'   => 'bank',
        'cheque'          => 'bank',
        'system_transfer' => null,
    ],
];