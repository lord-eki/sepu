<?php

namespace App\Http\Controllers;

use App\Models\MemberFinanceConfig;
use App\Models\Member;
use Illuminate\Http\Request;

class MemberFinanceConfigController extends Controller
{
    
    public function save(Request $request, Member $member)
    {
        $validated = $request->validate([
            'contribution_active'     => 'boolean',
            'monthly_contribution'    => 'numeric|min:0',
            'contribution_account_id' => 'nullable|exists:accounts,id',

            'loan_auto_deduct'        => 'boolean',
            'loan_deduction_amount'   => 'nullable|numeric|min:0',

            'dividend_eligible'       => 'boolean',
            'dividend_account_id'     => 'nullable|exists:accounts,id',
        ]);

        $data = array_merge([
            'contribution_active' => false,
            'monthly_contribution' => 0,
            'loan_auto_deduct'     => false,
            'dividend_eligible'    => true,
        ], $validated);

        MemberFinanceConfig::updateOrCreate(
            ['member_id' => $member->id],
            $data
        );

        return back()->with('success', 'Finance configuration saved successfully.');
    }
}