<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Jobs\CheckOverdueLoansJob;
use App\Jobs\GenerateReportsJob;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::job(new CheckOverdueLoansJob)->daily();
Schedule::job(new GenerateReportsJob('financial_summary',now()->submonth(),now(),1))->monthly();