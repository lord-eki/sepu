<?php

use App\Services\Loans\LoanRepaymentService;
use Illuminate\Support\Str;
use Tests\Support\Scenario;

beforeEach(function () {
    Scenario::chart();
    $this->admin = Scenario::user('admin');
    $this->member = Scenario::member();
    Scenario::account($this->member, 'share_deposits', 5000);
    $this->loan = Scenario::loan($this->member);
    app(LoanRepaymentService::class)->record($this->loan, 1100, 'cash', (string) Str::uuid(), $this->admin);
});

it('shows a repayment from the ledger to staff', function () {
    $this->actingAs($this->admin)
        ->get(route('loans.statement', $this->loan))
        ->assertInertia(fn ($page) => $page
            ->has('entries', 1)
            ->where('entries.0.type', 'repayment')
            ->where('entries.0.principal', 1000)
            ->where('entries.0.interest', 100)
            ->where('summary.principal_repaid', 1000)
            ->where('summary.interest_paid', 100));
})->group('loans');

it('lets the borrower open their own statement', function () {
    $this->actingAs($this->member->user)
        ->get(route('loans.statement', $this->loan))
        ->assertOk();
})->group('loans');

it('keeps another member out of the statement', function () {
    $other = Scenario::member();

    $this->actingAs($other->user)
        ->get(route('loans.statement', $this->loan))
        ->assertForbidden();
})->group('loans');