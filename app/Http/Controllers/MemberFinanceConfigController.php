<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMemberFinanceConfigRequest;
use App\Models\MemberFinanceConfig;
use App\Models\Member;

class MemberFinanceConfigController extends Controller
{
    
    public function save(StoreMemberFinanceConfigRequest $request, Member $member)
    {
        $validated = $request->validated();

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