<?php

use App\Models\Transaction;
use Tests\Support\Scenario;

it('rejects a deposit with bad input', function (array $payload, string $field) {
    $account = Scenario::account(Scenario::member());

    $this->actingAs(Scenario::user('admin'))
        ->post(route('accounts.deposit', $account), $payload)
        ->assertSessionHasErrors($field);

    expect(Transaction::count())->toBe(0);
})->with([
    'missing amount' => [['payment_method' => 'cash'], 'amount'],
    'zero amount' => [['amount' => 0, 'payment_method' => 'cash'], 'amount'],
    'negative amount' => [['amount' => -50, 'payment_method' => 'cash'], 'amount'],
    'text amount' => [['amount' => 'abc', 'payment_method' => 'cash'], 'amount'],
    'three decimals' => [['amount' => 100.555, 'payment_method' => 'cash'], 'amount'],
    'absurdly large' => [['amount' => 1000000000, 'payment_method' => 'cash'], 'amount'],
    'missing method' => [['amount' => 100], 'payment_method'],
    'unknown method' => [['amount' => 100, 'payment_method' => 'barter'], 'payment_method'],
])->group('validation');

it('records a valid deposit as pending', function () {
    $account = Scenario::account(Scenario::member());

    $this->actingAs(Scenario::user('admin'))
        ->post(route('accounts.deposit', $account), ['amount' => 1500.50, 'payment_method' => 'cash'])
        ->assertSessionHasNoErrors();

    $transaction = Transaction::where('account_id', $account->id)->first();

    expect($transaction)->not->toBeNull()
        ->and($transaction->status)->toBe('pending')
        ->and((float) $transaction->amount)->toBe(1500.5);
})->group('validation');

it('rejects a withdrawal with bad input', function (array $payload, string $field) {
    $account = Scenario::account(Scenario::member(), 'share_deposits', 5000);

    $this->actingAs(Scenario::user('admin'))
        ->post(route('accounts.withdrawal', $account), $payload)
        ->assertSessionHasErrors($field);

    expect(Transaction::count())->toBe(0);
})->with([
    'missing amount' => [['payment_method' => 'cash'], 'amount'],
    'negative amount' => [['amount' => -50, 'payment_method' => 'cash'], 'amount'],
    'text amount' => [['amount' => 'abc', 'payment_method' => 'cash'], 'amount'],
    'unknown method' => [['amount' => 100, 'payment_method' => 'barter'], 'payment_method'],
])->group('validation');

it('rejects a share transfer to the owner or an unknown member', function (string $recipient) {
    $owner = Scenario::member();
    $account = Scenario::account($owner, 'share_capital', 5000);
    $recipientId = $recipient === 'self' ? $owner->id : 999999;

    $this->actingAs(Scenario::user('admin'))
        ->post(route('accounts.share-transfer', $account), ['amount' => 100, 'recipient_member_id' => $recipientId])
        ->assertSessionHasErrors('recipient_member_id');
})->with(['self', 'unknown'])->group('validation');