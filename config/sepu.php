<?php

return [
    'admin_email' => env('SEPU_ADMIN_EMAIL', 'admin@sepusacco.co.ke'),
    'admin_password' => env('SEPU_ADMIN_PASSWORD'),
    'maker_checker' => env('SEPU_MAKER_CHECKER', true),
    'schedule_deposit_method' => env('SEPU_SCHEDULE_DEPOSIT_METHOD', 'bank_transfer'),
     'settlement_interest' => env('SEPU_SETTLEMENT_INTEREST', 'accrued'),
];