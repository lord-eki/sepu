<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class ScheduleExecutionLog extends Model
{
    protected $fillable = [

    'schedule_type','processing_month',
    'processing_year','executed_by','execution_date',
    'total_records_processed','total_records_failed',
    'total_amount_posted','status','error_log'
    
    ];

    protected $casts = [
        'execution_date' => 'datetime',
        'error_log' => 'array',
        'total_amount_posted' => 'decimal:2',
        'processing_month' => 'integer',
        'processing_year' => 'integer',
        'total_records_processed' => 'integer',
        'total_records_failed' => 'integer',
    ];

    public function executedBy()
    {
        return $this->belongsTo(User::class, 'executed_by');
    }

    /**
     * Check if schedule has already been executed for agiven period of time
     */

    public static function alreadyRun(string $type,int $year, ?int $month = null) : bool
    {
        return static::where('schedule_type', $type)->where('processing_year', $year)
        ->when($month != null , fn($q) =>  $q->where('processing_month', $month))
        ->whereIn('status', ['completed', 'partial'])
        ->exists();
    }

    public function getScheduleTypeLabelAttribute() : string
    {
        return match($this->schedule_type)
        {
            'monthly_deposits' => 'Monthly Deposits',
            'loan_repayments' => 'Loan Repayments',
            'loan_disbursements' => 'Loan Disbursements',
            'dividend_payments' => 'Dividend Payments',
            default => ucfirst($this->schedule_type),
        };
    }

    public function getPeriodLabelAttribute():string
    {
        if($this->processing_month)
            {
                $monthName = Carbon::create(null, $this->processing_month)->format('F');
                return "$monthName {$this->processing_year}";
            }

            return (string) $this->processing_year;
    }
}
