<?php

use App\Models\{User, Member, Loan, LoanProduct, LoanGuarantor, Account, Transaction, PaymentVoucher, AuditLog};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Carbon\Carbon;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create a test user with admin role
    $this->user = User::factory()->create([
        'role' => 'admin',
        'is_active' => true,
    ]);

    // Create a test member
    $this->member = Member::factory()->create([
        'membership_status' => 'active',
        'monthly_income' => 50000,
    ]);

    // Create a loan product
    $this->loanProduct = LoanProduct::factory()->create([
        'name' => 'Personal Loan',
        'min_amount' => 10000,
        'max_amount' => 500000,
        'interest_rate' => 12.0,
        'min_term_months' => 6,
        'max_term_months' => 24,
        'processing_fee_rate' => 2.0,
        'insurance_rate' => 1.0,
        'is_active' => true,
    ]);

    // Authenticate the user
    $this->actingAs($this->user);
});

describe('Loan Listing', function () {
    test('can list loans with pagination', function () {
        Loan::factory(25)->create();

        $response = $this->getJson('/loans');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data',
                    'current_page',
                    'per_page',
                    'total',
                ],
                'summary',
            ]);
    });

    test('can filter loans by status', function () {
        Loan::factory()->create(['status' => 'pending']);
        Loan::factory()->create(['status' => 'approved']);
        Loan::factory()->create(['status' => 'disbursed']);

        $response = $this->getJson('/loans?status=pending');

        $response->assertStatus(200);
        $data = $response->json('data.data');
        expect($data)->toHaveCount(1)
            ->and($data[0]['status'])->toBe('pending');
    });

    test('can filter loans by member', function () {
        $member1 = Member::factory()->create();
        $member2 = Member::factory()->create();

        Loan::factory()->create(['member_id' => $member1->id]);
        Loan::factory()->create(['member_id' => $member2->id]);

        $response = $this->getJson("/loans?member_id={$member1->id}");

        $response->assertStatus(200);
        $data = $response->json('data.data');
        expect($data)->toHaveCount(1)
            ->and($data[0]['member_id'])->toBe($member1->id);
    });

    test('can search loans by loan number', function () {
        $loan = Loan::factory()->create(['loan_number' => 'LN202501001']);
        Loan::factory()->create(['loan_number' => 'LN202501002']);

        $response = $this->getJson('/loans?search=LN202501001');

        $response->assertStatus(200);
        $data = $response->json('data.data');
        expect($data)->toHaveCount(1)
            ->and($data[0]['loan_number'])->toBe('LN202501001');
    });
});

describe('Loan Creation', function () {
    test('can get create loan form data', function () {
        $response = $this->getJson('/loans/create');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'loan_products',
                    'members',
                ],
            ]);
    });

    test('can create a new loan application', function () {
        $loanData = [
            'member_id' => $this->member->id,
            'loan_product_id' => $this->loanProduct->id,
            'applied_amount' => 100000,
            'term_months' => 12,
            'purpose' => 'Business expansion',
            'guarantors' => [
                [
                    'member_id' => Member::factory()->create()->id,
                    'guaranteed_amount' => 50000,
                ],
            ],
        ];

        $response = $this->postJson('/loans', $loanData);

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'loan_number',
                    'monthly_repayment',
                    'total_repayable',
                ],
            ]);

        $this->assertDatabaseHas('loans', [
            'member_id' => $this->member->id,
            'loan_product_id' => $this->loanProduct->id,
            'applied_amount' => 100000,
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('loan_guarantors', [
            'guaranteed_amount' => 50000,
            'status' => 'pending',
        ]);
    });

    test('validates required fields when creating loan', function () {
        $response = $this->postJson('/loans', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'member_id',
                'loan_product_id',
                'applied_amount',
                'term_months',
                'purpose',
            ]);
    });

    test('validates loan amount against product limits', function () {
        $loanData = [
            'member_id' => $this->member->id,
            'loan_product_id' => $this->loanProduct->id,
            'applied_amount' => 5000, // Below minimum
            'term_months' => 12,
            'purpose' => 'Test purpose',
        ];

        $response = $this->postJson('/loans', $loanData);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Loan amount must be between 10000.00 and 500000.00',
            ]);
    });

    test('validates loan term against product limits', function () {
        $loanData = [
            'member_id' => $this->member->id,
            'loan_product_id' => $this->loanProduct->id,
            'applied_amount' => 50000,
            'term_months' => 30, // Above maximum
            'purpose' => 'Test purpose',
        ];

        $response = $this->postJson('/loans', $loanData);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Loan term must be between 6 and 24 months',
            ]);
    });

    test('prevents creating loan when member has existing active loan', function () {
        Loan::factory()->create([
            'member_id' => $this->member->id,
            'status' => 'pending',
        ]);

        $loanData = [
            'member_id' => $this->member->id,
            'loan_product_id' => $this->loanProduct->id,
            'applied_amount' => 50000,
            'term_months' => 12,
            'purpose' => 'Test purpose',
        ];

        $response = $this->postJson('/loans', $loanData);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Member has existing active loan applications',
            ]);
    });

    test('creates audit log when loan is created', function () {
        $loanData = [
            'member_id' => $this->member->id,
            'loan_product_id' => $this->loanProduct->id,
            'applied_amount' => 100000,
            'term_months' => 12,
            'purpose' => 'Business expansion',
        ];

        $this->postJson('/loans', $loanData);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->user->id,
            'action' => 'loan_application_created',
            'model_type' => 'App\Models\Loan',
        ]);
    });
});

describe('Loan Details', function () {
    test('can view loan details', function () {
        $loan = Loan::factory()->create([
            'member_id' => $this->member->id,
            'loan_product_id' => $this->loanProduct->id,
        ]);

        $response = $this->getJson("/loans/{$loan->id}");

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'loan_number',
                    'member',
                    'loan_product',
                    'guarantors',
                    'repayments',
                ],
            ]);
    });

    test('returns 404 for non-existent loan', function () {
        $response = $this->getJson('/loans/999999');

        $response->assertStatus(404);
    });
});

describe('Loan Updates', function () {
    test('can update pending loan', function () {
        $loan = Loan::factory()->create([
            'member_id' => $this->member->id,
            'status' => 'pending',
            'applied_amount' => 50000,
            'purpose' => 'Original purpose',
        ]);

        $updateData = [
            'applied_amount' => 75000,
            'purpose' => 'Updated purpose',
        ];

        $response = $this->putJson("/loans/{$loan->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('loans', [
            'id' => $loan->id,
            'applied_amount' => 75000,
            'purpose' => 'Updated purpose',
        ]);
    });

    test('cannot update non-pending loan', function () {
        $loan = Loan::factory()->create([
            'status' => 'approved',
        ]);

        $response = $this->putJson("/loans/{$loan->id}", [
            'applied_amount' => 75000,
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Only pending loans can be updated',
            ]);
    });

    test('recalculates loan details when amount or term changes', function () {
        $loan = Loan::factory()->create([
            'member_id' => $this->member->id,
            'loan_product_id' => $this->loanProduct->id,
            'status' => 'pending',
            'applied_amount' => 50000,
            'term_months' => 12,
        ]);

        $originalRepayment = $loan->monthly_repayment;

        $response = $this->putJson("/loans/{$loan->id}", [
            'applied_amount' => 100000,
        ]);

        $response->assertStatus(200);

        $loan->refresh();
        expect($loan->monthly_repayment)->not->toBe($originalRepayment);
    });
});

describe('Loan Approval', function () {
    test('can approve pending loan', function () {
        $loan = Loan::factory()->create([
            'status' => 'pending',
            'applied_amount' => 100000,
        ]);

        $approvalData = [
            'approved_amount' => 90000,
            'approval_notes' => 'Approved with conditions',
        ];

        $response = $this->postJson("/loans/{$loan->id}/approve", $approvalData);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('loans', [
            'id' => $loan->id,
            'status' => 'approved',
            'approved_amount' => 90000,
            'approved_by' => $this->user->id,
            'approval_notes' => 'Approved with conditions',
        ]);
    });

    test('cannot approve non-pending loan', function () {
        $loan = Loan::factory()->create([
            'status' => 'approved',
        ]);

        $response = $this->postJson("/loans/{$loan->id}/approve", [
            'approved_amount' => 50000,
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Only pending loans can be approved',
            ]);
    });

    test('recalculates loan details based on approved amount', function () {
        $loan = Loan::factory()->create([
            'loan_product_id' => $this->loanProduct->id,
            'status' => 'pending',
            'applied_amount' => 100000,
            'term_months' => 12,
        ]);

        $this->postJson("/loans/{$loan->id}/approve", [
            'approved_amount' => 80000,
        ]);

        $loan->refresh();
        
        expect($loan->approved_amount)->toBe(80000.0)
            ->and($loan->outstanding_balance)->toBe(80000.0)
            ->and($loan->principal_balance)->toBe(80000.0);
    });
});

describe('Loan Rejection', function () {
    test('can reject pending loan', function () {
        $loan = Loan::factory()->create([
            'status' => 'pending',
        ]);

        $rejectionData = [
            'rejection_reason' => 'Insufficient collateral',
        ];

        $response = $this->postJson("/loans/{$loan->id}/reject", $rejectionData);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('loans', [
            'id' => $loan->id,
            'status' => 'rejected',
            'rejection_reason' => 'Insufficient collateral',
            'approved_by' => $this->user->id,
        ]);
    });

    test('can reject approved loan', function () {
        $loan = Loan::factory()->create([
            'status' => 'approved',
        ]);

        $response = $this->postJson("/loans/{$loan->id}/reject", [
            'rejection_reason' => 'Changed circumstances',
        ]);

        $response->assertStatus(200);
    });

    test('cannot reject disbursed loan', function () {
        $loan = Loan::factory()->create([
            'status' => 'disbursed',
        ]);

        $response = $this->postJson("/loans/{$loan->id}/reject", [
            'rejection_reason' => 'Test reason',
        ]);

        $response->assertStatus(422);
    });

    test('validates rejection reason is required', function () {
        $loan = Loan::factory()->create([
            'status' => 'pending',
        ]);

        $response = $this->postJson("/loans/{$loan->id}/reject", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['rejection_reason']);
    });
});

describe('Loan Disbursement', function () {
    test('can disburse approved loan', function () {
        $loan = Loan::factory()->create([
            'member_id' => $this->member->id,
            'status' => 'approved',
            'approved_amount' => 100000,
            'processing_fee' => 2000,
            'insurance_fee' => 1000,
        ]);

        // Create member's savings account
        $account = Account::factory()->create([
            'member_id' => $this->member->id,
            'account_type' => 'savings',
            'balance' => 0,
        ]);

        $disbursementData = [
            'disbursed_amount' => 100000,
            'disbursement_method' => 'mobile_money',
            'disbursement_reference' => 'MM123456789',
        ];

        $response = $this->postJson("/loans/{$loan->id}/disburse", $disbursementData);

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'data' => [
                    'loan',
                    'transaction',
                    'net_disbursement',
                ],
            ]);

        $this->assertDatabaseHas('loans', [
            'id' => $loan->id,
            'status' => 'disbursed',
            'disbursed_amount' => 100000,
            'disbursed_by' => $this->user->id,
        ]);

        // Check transaction was created
        $this->assertDatabaseHas('transactions', [
            'member_id' => $this->member->id,
            'transaction_type' => 'loan_disbursement',
            'amount' => 97000, // 100000 - 2000 - 1000
            'status' => 'completed',
        ]);

        // Check payment voucher was created
        $this->assertDatabaseHas('payment_vouchers', [
            'loan_id' => $loan->id,
            'voucher_type' => 'loan_disbursement',
            'amount' => 100000,
            'status' => 'paid',
        ]);
    });

    test('cannot disburse non-approved loan', function () {
        $loan = Loan::factory()->create([
            'status' => 'pending',
        ]);

        $response = $this->postJson("/loans/{$loan->id}/disburse", [
            'disbursed_amount' => 50000,
            'disbursement_method' => 'cash',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Only approved loans can be disbursed',
            ]);
    });

    test('creates repayment schedule when loan is disbursed', function () {
        $loan = Loan::factory()->create([
            'member_id' => $this->member->id,
            'status' => 'approved',
            'approved_amount' => 100000,
            'term_months' => 12,
            'processing_fee' => 2000,
            'insurance_fee' => 1000,
        ]);

        Account::factory()->create([
            'member_id' => $this->member->id,
            'account_type' => 'savings',
        ]);

        $this->postJson("/loans/{$loan->id}/disburse", [
            'disbursed_amount' => 100000,
            'disbursement_method' => 'cash',
        ]);

        $this->assertDatabaseCount('loan_repayments', 12);
        
        $firstRepayment = \App\Models\LoanRepayment::where('loan_id', $loan->id)
            ->orderBy('due_date')
            ->first();
        
        expect($firstRepayment->status)->toBe('pending');
    });

    test('validates disbursement data', function () {
        $loan = Loan::factory()->create([
            'status' => 'approved',
        ]);

        $response = $this->postJson("/loans/{$loan->id}/disburse", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'disbursed_amount',
                'disbursement_method',
            ]);
    });

    test('updates account balance on disbursement', function () {
        $loan = Loan::factory()->create([
            'member_id' => $this->member->id,
            'status' => 'approved',
            'approved_amount' => 100000,
            'processing_fee' => 2000,
            'insurance_fee' => 1000,
        ]);

        $account = Account::factory()->create([
            'member_id' => $this->member->id,
            'account_type' => 'savings',
            'balance' => 5000,
            'available_balance' => 5000,
        ]);

        $this->postJson("/loans/{$loan->id}/disburse", [
            'disbursed_amount' => 100000,
            'disbursement_method' => 'cash',
        ]);

        $account->refresh();
        
        expect($account->balance)->toBe(102000.0) // 5000 + 97000 net disbursement
            ->and($account->available_balance)->toBe(102000.0);
    });
});

describe('Loan Statistics', function () {
    test('can get loans summary', function () {
        Loan::factory()->create(['status' => 'pending', 'applied_amount' => 50000]);
        Loan::factory()->create(['status' => 'approved', 'approved_amount' => 75000]);
        Loan::factory()->create(['status' => 'disbursed', 'disbursed_amount' => 100000]);
        Loan::factory()->create(['status' => 'completed']);
        Loan::factory()->create(['days_in_arrears' => 30]);

        $controller = new \App\Http\Controllers\LoanController();
        $summary = $controller->getLoansSummary();

        expect($summary)
            ->toHaveKey('total_loans', 5)
            ->toHaveKey('pending_loans', 1)
            ->toHaveKey('approved_loans', 1)
            ->toHaveKey('disbursed_loans', 1)
            ->toHaveKey('completed_loans', 1)
            ->toHaveKey('overdue_loans', 1)
            ->toHaveKey('total_applied_amount', 50000.0)
            ->toHaveKey('total_approved_amount', 75000.0)
            ->toHaveKey('total_disbursed_amount', 100000.0);
    });

    test('includes current month statistics in summary', function () {
        // Create loans for this month
        Loan::factory()->create([
            'application_date' => now(),
        ]);
        
        Loan::factory()->create([
            'disbursement_date' => now(),
        ]);

        $controller = new \App\Http\Controllers\LoanController();
        $summary = $controller->getLoansSummary();

        expect($summary)
            ->toHaveKey('this_month_applications', 1)
            ->toHaveKey('this_month_disbursements', 1);
    });
});

describe('Private Helper Methods', function () {
    test('generateLoanNumber creates unique loan numbers', function () {
        $controller = new \App\Http\Controllers\LoanController();
        
        // Use reflection to access private method
        $reflection = new ReflectionClass($controller);
        $method = $reflection->getMethod('generateLoanNumber');
        $method->setAccessible(true);
        
        $loanNumber1 = $method->invoke($controller);
        $loanNumber2 = $method->invoke($controller);
        
        expect($loanNumber1)->not->toBe($loanNumber2)
            ->and($loanNumber1)->toStartWith('LN' . date('Ym'))
            ->and($loanNumber2)->toStartWith('LN' . date('Ym'));
    });

    test('calculateMonthlyRepayment calculates correct amount', function () {
        $controller = new \App\Http\Controllers\LoanController();
        
        $reflection = new ReflectionClass($controller);
        $method = $reflection->getMethod('calculateMonthlyRepayment');
        $method->setAccessible(true);
        
        // Test with 12% annual rate (1% monthly), 100,000 principal, 12 months
        $monthlyRepayment = $method->invoke($controller, 100000, 0.01, 12);
        
        expect($monthlyRepayment)->toBeFloat()
            ->and($monthlyRepayment)->toBeGreaterThan(8000)
            ->and($monthlyRepayment)->toBeLessThan(10000);
    });

    test('calculateMonthlyRepayment handles zero interest rate', function () {
        $controller = new \App\Http\Controllers\LoanController();
        
        $reflection = new ReflectionClass($controller);
        $method = $reflection->getMethod('calculateMonthlyRepayment');
        $method->setAccessible(true);
        
        $monthlyRepayment = $method->invoke($controller, 120000, 0, 12);
        
        expect($monthlyRepayment)->toBe(10000.0); // 120000 / 12
    });
});

describe('Error Handling', function () {
    test('handles database errors gracefully during loan creation', function () {
        // Mock a database error by providing invalid foreign key
        $loanData = [
            'member_id' => 99999, // Non-existent member
            'loan_product_id' => $this->loanProduct->id,
            'applied_amount' => 100000,
            'term_months' => 12,
            'purpose' => 'Test purpose',
        ];

        $response = $this->postJson('/loans', $loanData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['member_id']);
    });

    test('handles database errors gracefully during loan approval', function () {
        $loan = Loan::factory()->create(['status' => 'pending']);
        
        // Delete the loan to simulate database error
        $loan->delete();

        $response = $this->postJson("/loans/{$loan->id}/approve", [
            'approved_amount' => 50000,
        ]);

        $response->assertStatus(404);
    });
});

describe('Authorization', function () {
    test('requires authentication', function () {
        auth()->logout();

        $response = $this->getJson('/loans');

        $response->assertStatus(401);
    });

    test('inactive user cannot access loans', function () {
        $inactiveUser = User::factory()->create(['is_active' => false]);
        
        $this->actingAs($inactiveUser);

        $response = $this->getJson('/loans');

        // This would depend on your middleware implementation
        // Adjust based on your actual authorization logic
        $response->assertStatus(403);
    });
});