<?php

use App\Models\Account;
use App\Models\Member;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create(['role' => 'admin']);
    $this->member = Member::factory()->create(['user_id' => $this->user->id]);
    $this->actingAs($this->user);
});

describe('Account Index', function () {
    test('can view accounts index', function () {
        Account::factory()->count(3)->create();

        $response = $this->get(route('accounts.index'));

        $response->assertOk()
            ->assertInertia(
                fn($page) => $page
                    ->component('Accounts/Index')
                    ->has('accounts.data', 3)
                    ->has('stats')
                    ->has('accountTypes')
            );
    });

    test('can search accounts by account number', function () {
        $account = Account::factory()->create(['account_number' => 'SAV1234567']);
        Account::factory()->create(['account_number' => 'SAV9876543']);

        $response = $this->get(route('accounts.index', ['search' => '1234']));

        $response->assertOk()
            ->assertInertia(
                fn($page) => $page
                    ->has('accounts.data', 1)
                    ->where('accounts.data.0.account_number', 'SAV1234567')
            );
    });

    test('can search accounts by member name', function () {
        $member = Member::factory()->create(['first_name' => 'John', 'last_name' => 'Doe']);
        Account::factory()->create(['member_id' => $member->id]);
        Account::factory()->create(); // Other account

        $response = $this->get(route('accounts.index', ['search' => 'John']));

        $response->assertOk()
            ->assertInertia(
                fn($page) => $page
                    ->has('accounts.data', 1)
            );
    });

    test('can filter accounts by type', function () {
        Account::factory()->create(['account_type' => 'savings']);
        Account::factory()->create(['account_type' => 'shares']);

        $response = $this->get(route('accounts.index', ['account_type' => 'savings']));

        $response->assertOk()
            ->assertInertia(
                fn($page) => $page
                    ->has('accounts.data', 1)
                    ->where('accounts.data.0.account_type', 'savings')
            );
    });

    test('can filter accounts by status', function () {
        Account::factory()->create(['is_active' => true]);
        Account::factory()->create(['is_active' => false]);

        $response = $this->get(route('accounts.index', ['status' => 'active']));

        $response->assertOk()
            ->assertInertia(
                fn($page) => $page
                    ->has('accounts.data', 1)
                    ->where('accounts.data.0.is_active', true)
            );
    });

    test('can sort accounts', function () {
        Account::factory()->create(['created_at' => now()->subDays(2)]);
        Account::factory()->create(['created_at' => now()->subDay()]);

        $response = $this->get(route('accounts.index', [
            'sortBy' => 'created_at',
            'sortDirection' => 'asc'
        ]));

        $response->assertOk()
            ->assertInertia(fn($page) => $page->has('accounts.data', 2));
    });
});

describe('Account Creation', function () {
    test('can view create account form', function () {
        $response = $this->get(route('accounts.create'));

        $response->assertOk()
            ->assertInertia(
                fn($page) => $page
                    ->component('Accounts/Create')
                    ->has('members')
                    ->has('accountTypes')
            );
    });

    test('can create new account', function () {
        $response = $this->post(route('accounts.store'), [
            'member_id' => $this->member->id,
            'account_type' => 'savings',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('accounts', [
            'member_id' => $this->member->id,
            'account_type' => 'savings',
            'balance' => 0,
            'is_active' => true,
        ]);

        // Check that initial transaction was created
        $this->assertDatabaseHas('transactions', [
            'member_id' => $this->member->id,
            'transaction_type' => 'account_opening',
            'amount' => 0,
            'status' => 'completed',
        ]);
    });

    test('generates unique account number for each account type', function () {
        $savingsAccount = Account::factory()->savings()->create();
        $sharesAccount = Account::factory()->shares()->create();
        $depositsAccount = Account::factory()->deposits()->create();

        expect($savingsAccount->account_number)->toStartWith('SAV');
        expect($sharesAccount->account_number)->toStartWith('SHR');
        expect($depositsAccount->account_number)->toStartWith('DEP');
    });

    test('validates required fields when creating account', function () {
        $response = $this->post(route('accounts.store'), []);

        $response->assertSessionHasErrors(['member_id', 'account_type']);
    });

    test('rolls back transaction on account creation failure', function () {
        // Mock DB to throw exception
        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('rollBack')->once();

        // Create invalid data that would cause failure
        $response = $this->post(route('accounts.store'), [
            'member_id' => 99999, // Non-existent member
            'account_type' => 'savings',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseMissing('accounts', [
            'member_id' => 99999,
        ]);
    });
});

describe('Account Display', function () {
    test('can view account details', function () {
        $account = Account::factory()->create(['member_id' => $this->member->id]);
        Transaction::factory()->count(3)->create([
            'account_id' => $account->id,
            'member_id' => $this->member->id,
            'transaction_type' => 'deposit',
            'status' => 'completed',
        ]);
        $response = $this->get(route('accounts.show', $account));

        $response->assertOk()
            ->assertInertia(
                fn($page) => $page
                    ->component('Accounts/Show')
                    ->has('account')
                    ->has('recentTransactions')
                    ->has('stats')
            );
    });
});

describe('Account Updates', function () {
    test('can view edit account form', function () {
        $account = Account::factory()->create(['member_id' => $this->member->id]);

        $response = $this->get(route('accounts.edit', $account));

        $response->assertOk()
            ->assertInertia(
                fn($page) => $page
                    ->component('Accounts/Edit')
                    ->has('account')
                    ->has('accountTypes')
            );
    });

    test('can update account', function () {
        $account = Account::factory()->create(['member_id' => $this->member->id]);

        $response = $this->put(route('accounts.update', $account), [
            'account_type' => 'shares',
        ]);

        $response->assertRedirect(route('accounts.show', $account));
        $this->assertDatabaseHas('accounts', [
            'id' => $account->id,
            'account_type' => 'shares',
        ]);
    });
});

describe('Account Closure', function () {
    test('can close account with zero balance', function () {
        $account = Account::factory()->create([
            'member_id' => $this->member->id,
            'balance' => 0
        ]);

        $response = $this->delete(route('accounts.destroy', $account));

        $response->assertRedirect(route('accounts.index'));

        $account->refresh();
        expect($account->is_active)->toBeFalse();

        // Check closure transaction was created
        $this->assertDatabaseHas('transactions', [
            'account_id' => $account->id,
            'transaction_type' => 'account_closure',
            'status' => 'completed',
        ]);
    });

    test('cannot close account with balance', function () {
        $account = Account::factory()->create([
            'member_id' => $this->member->id,
            'balance' => 100
        ]);

        $response = $this->delete(route('accounts.destroy', $account));

        $response->assertRedirect();
        $response->assertSessionHasErrors(['error']);

        $account->refresh();
        expect($account->is_active)->toBeTrue();
    });

    test('cannot close account with pending transactions', function () {
        $account = Account::factory()->create([
            'member_id' => $this->member->id,
            'balance' => 0
        ]);

        Transaction::factory()->create([
            'account_id' => $account->id,
            'member_id' => $this->member->id,
            'status' => 'pending',
            'transaction_type' => 'deposit',
        ]);

        $response = $this->delete(route('accounts.destroy', $account));

        $response->assertRedirect();
        $response->assertSessionHasErrors(['error']);

        $account->refresh();
        expect($account->is_active)->toBeTrue();
    });
});

describe('Deposits', function () {
    test('can view deposit form', function () {
        $account = Account::factory()->create(['member_id' => $this->member->id]);

        $response = $this->get(route('accounts.deposit.show', $account));

        $response->assertOk()
            ->assertInertia(
                fn($page) => $page
                    ->component('Accounts/Deposit')
                    ->has('account')
                    ->has('paymentMethods')
            );
    });

    test('can process deposit', function () {
        $account = Account::factory()->create([
            'member_id' => $this->member->id,
            'balance' => 100
        ]);

        $response = $this->post(route('accounts.deposit', $account), [
            'amount' => 50,
            'payment_method' => 'cash',
            'description' => 'Test deposit'
        ]);

        $response->assertRedirect(route('accounts.show', $account));

        $account->refresh();
        expect($account->balance)->toBe(150.0);

        $this->assertDatabaseHas('transactions', [
            'account_id' => $account->id,
            'transaction_type' => 'deposit',
            'amount' => 50,
            'balance_before' => 100,
            'balance_after' => 150,
            'status' => 'completed',
        ]);
    });

    test('creates share_purchase transaction for shares account', function () {
        $account = Account::factory()->create([
            'member_id' => $this->member->id,
            'account_type' => 'shares',
            'balance' => 0
        ]);

        $this->post(route('accounts.deposit', $account), [
            'amount' => 100,
            'payment_method' => 'cash'
        ]);

        $this->assertDatabaseHas('transactions', [
            'account_id' => $account->id,
            'transaction_type' => 'share_purchase',
            'amount' => 100,
        ]);
    });

    test('validates deposit amount', function () {
        $account = Account::factory()->create(['member_id' => $this->member->id]);

        $response = $this->post(route('accounts.deposit', $account), [
            'amount' => -10,
            'payment_method' => 'cash'
        ]);

        $response->assertSessionHasErrors(['amount']);
    });
});

describe('Withdrawals', function () {
    test('can view withdrawal form', function () {
        $account = Account::factory()->create(['member_id' => $this->member->id]);

        $response = $this->get(route('accounts.withdrawal.show', $account));

        $response->assertOk()
            ->assertInertia(
                fn($page) => $page
                    ->component('Accounts/Withdrawal')
                    ->has('account')
                    ->has('paymentMethods')
            );
    });

    test('can process withdrawal', function () {
        $account = Account::factory()->create([
            'member_id' => $this->member->id,
            'balance' => 100
        ]);

        $response = $this->post(route('accounts.withdrawal', $account), [
            'amount' => 30,
            'payment_method' => 'cash',
            'description' => 'Test withdrawal'
        ]);

        $response->assertRedirect(route('accounts.show', $account));

        $account->refresh();
        expect($account->balance)->toBe(70.0);

        $this->assertDatabaseHas('transactions', [
            'account_id' => $account->id,
            'transaction_type' => 'withdrawal',
            'amount' => 30,
            'balance_before' => 100,
            'balance_after' => 70,
            'status' => 'completed',
        ]);
    });

    test('creates share_sale transaction for shares account', function () {
        $account = Account::factory()->create([
            'member_id' => $this->member->id,
            'account_type' => 'shares',
            'balance' => 100
        ]);

        $this->post(route('accounts.withdrawal', $account), [
            'amount' => 50,
            'payment_method' => 'cash'
        ]);

        $this->assertDatabaseHas('transactions', [
            'account_id' => $account->id,
            'transaction_type' => 'share_sale',
            'amount' => 50,
        ]);
    });

    test('cannot withdraw more than balance', function () {
        $account = Account::factory()->create([
            'member_id' => $this->member->id,
            'balance' => 50
        ]);

        $response = $this->post(route('accounts.withdrawal', $account), [
            'amount' => 100,
            'payment_method' => 'cash'
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['amount']);

        $account->refresh();
        expect($account->balance)->toBe(50.0);
    });
});

describe('Share Transfers', function () {
    test('can view share transfer form for shares account', function () {
        $account = Account::factory()->create([
            'member_id' => $this->member->id,
            'account_type' => 'shares'
        ]);

        $response = $this->get(route('accounts.share-transfer.show', $account));

        $response->assertOk()
            ->assertInertia(
                fn($page) => $page
                    ->component('Accounts/ShareTransfer')
                    ->has('account')
                    ->has('members')
            );
    });

    test('cannot view share transfer form for non-shares account', function () {
        $account = Account::factory()->create([
            'member_id' => $this->member->id,
            'account_type' => 'savings'
        ]);

        $response = $this->get(route('accounts.share-transfer.show', $account));

        $response->assertRedirect(route('accounts.show', $account));
        $response->assertSessionHas('error');
    });

    test('can process share transfer', function () {
        $senderAccount = Account::factory()->create([
            'member_id' => $this->member->id,
            'account_type' => 'shares',
            'balance' => 200
        ]);

        $recipientMember = Member::factory()->create();

        $response = $this->post(route('accounts.share-transfer', $senderAccount), [
            'recipient_member_id' => $recipientMember->id,
            'amount' => 100,
        ]);

        $response->assertRedirect(route('accounts.show', $senderAccount));

        // Check sender's account
        $senderAccount->refresh();
        expect($senderAccount->balance)->toBe(100.0);

        // Check recipient's account was created/updated
        $recipientAccount = Account::where('member_id', $recipientMember->id)
            ->where('account_type', 'shares')
            ->first();

        expect($recipientAccount)->not->toBeNull();
        expect($recipientAccount->balance)->toBe(100.0);

        // Check transactions
        $this->assertDatabaseHas('transactions', [
            'account_id' => $senderAccount->id,
            'transaction_type' => 'share_transfer_out',
            'amount' => 100,
        ]);

        $this->assertDatabaseHas('transactions', [
            'account_id' => $recipientAccount->id,
            'transaction_type' => 'share_transfer_in',
            'amount' => 100,
        ]);
    });

    test('cannot transfer more than balance', function () {
        $senderAccount = Account::factory()->create([
            'member_id' => $this->member->id,
            'account_type' => 'shares',
            'balance' => 50
        ]);

        $recipientMember = Member::factory()->create();

        $response = $this->post(route('accounts.share-transfer', $senderAccount), [
            'recipient_member_id' => $recipientMember->id,
            'amount' => 100,
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['amount']);
    });

    test('creates recipient shares account if it does not exist', function () {
        $senderAccount = Account::factory()->create([
            'member_id' => $this->member->id,
            'account_type' => 'shares',
            'balance' => 100
        ]);

        $recipientMember = Member::factory()->create();

        $this->post(route('accounts.share-transfer', $senderAccount), [
            'recipient_member_id' => $recipientMember->id,
            'amount' => 50,
        ]);

        $this->assertDatabaseHas('accounts', [
            'member_id' => $recipientMember->id,
            'account_type' => 'shares',
            'balance' => 50,
        ]);
    });
});

describe('Account Transactions', function () {
    test('can view account transactions', function () {
        $account = Account::factory()->create(['member_id' => $this->member->id]);
        Transaction::factory()->count(5)->create([
            'account_id' => $account->id,
            'member_id' => $this->member->id,
            'transaction_type' => 'deposit',
            'status' => 'completed',
        ]);
        $response = $this->get(route('accounts.transactions', $account));

        $response->assertOk()
            ->assertInertia(
                fn($page) => $page
                    ->component('Accounts/Transactions')
                    ->has('account')
                    ->has('transactions.data', 5)
                    ->has('transactionTypes')
                    ->has('statuses')
            );
    });

    test('can filter transactions by type', function () {
        $account = Account::factory()->create(['member_id' => $this->member->id]);
        Transaction::factory()->create([
            'account_id' => $account->id,
            'transaction_type' => 'deposit'
        ]);
        Transaction::factory()->create([
            'account_id' => $account->id,
            'transaction_type' => 'withdrawal'
        ]);

        $response = $this->get(route('accounts.transactions', [
            $account,
            'transaction_type' => 'deposit'
        ]));

        $response->assertOk()
            ->assertInertia(
                fn($page) => $page
                    ->has('transactions.data', 1)
                    ->where('transactions.data.0.transaction_type', 'deposit')
            );
    });

    test('can filter transactions by date range', function () {
        $account = Account::factory()->create(['member_id' => $this->member->id]);

        Transaction::factory()->create([
            'account_id' => $account->id,
            'member_id' => $this->member->id,
            'transaction_type' => 'deposit',
            'status' => 'completed',
            'created_at' => now()->subDays(5),
        ]);
        Transaction::factory()->create([
            'account_id' => $account->id,
            'member_id' => $this->member->id,
            'transaction_type' => 'withdrawal',
            'status' => 'completed',
            'created_at' => now(),
        ]);

        $response = $this->get(route('accounts.transactions', [
            $account,
            'date_from' => now()->subDay()->toDateString(),
            'date_to' => now()->toDateString()
        ]));

        $response->assertOk()
            ->assertInertia(
                fn($page) => $page
                    ->has('transactions.data', 1)
            );
    });
});

describe('Helper Methods', function () {
    test('generates unique account numbers', function () {
        $controller = new \App\Http\Controllers\AccountController();
        $method = new ReflectionMethod($controller, 'generateAccountNumber');
        $method->setAccessible(true);

        $accountNumber1 = $method->invoke($controller, 'savings');
        $accountNumber2 = $method->invoke($controller, 'savings');

        expect($accountNumber1)->not->toBe($accountNumber2);
        expect($accountNumber1)->toStartWith('SAV');
        expect($accountNumber2)->toStartWith('SAV');
    });

    test('generates unique transaction ids', function () {
        $controller = new \App\Http\Controllers\AccountController();
        $method = new ReflectionMethod($controller, 'generateTransactionId');
        $method->setAccessible(true);

        $transactionId1 = $method->invoke($controller);
        $transactionId2 = $method->invoke($controller);

        expect($transactionId1)->not->toBe($transactionId2);
        expect($transactionId1)->toStartWith('TXN');
        expect($transactionId2)->toStartWith('TXN');
    });
});

// Test middleware and authorization
describe('Authorization', function () {
    test('requires authentication for all routes', function () {
        auth()->logout();

        $account = Account::factory()->create();

        $routes = [
            ['GET', route('accounts.index'), []],
            ['GET', route('accounts.create'), []],
            ['GET', route('accounts.show', $account), []],
            ['GET', route('accounts.edit', $account), []],
            ['POST', route('accounts.store'), ['member_id' => 1, 'account_type' => 'savings']],
            ['PUT', route('accounts.update', $account), ['account_type' => 'savings']],
            ['DELETE', route('accounts.destroy', $account), []],
        ];

        foreach ($routes as [$method, $route, $data]) {
            $response = $this->call($method, $route, $data);
            $response->assertRedirect(route('login'));
        }
    });
});