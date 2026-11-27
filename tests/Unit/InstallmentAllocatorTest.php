<?php

use App\Services\Loans\InstallmentAllocator;


it('pays penalty, then interest, then principal', function () {
    $split = InstallmentAllocator::split(500, 50, 100, 1000, 0);

    expect($split)->toBe(['penalty' => 50.0, 'interest' => 100.0, 'principal' => 350.0, 'unallocated' => 0.0]);
})->group('loans');

it('covers only interest when the payment is small', function () {
    $split = InstallmentAllocator::split(60, 0, 100, 1000, 0);

    expect($split['interest'])->toBe(60.0)->and($split['principal'])->toBe(0.0);
})->group('loans');

it('does not charge interest twice after an earlier partial payment', function () {
    $split = InstallmentAllocator::split(700, 0, 200, 1000, 300);

    expect($split)->toBe(['penalty' => 0.0, 'interest' => 0.0, 'principal' => 700.0, 'unallocated' => 0.0]);
})->group('loans');

it('reports what could not be allocated', function () {
    $split = InstallmentAllocator::split(1500, 0, 100, 1000, 0);

    expect($split['unallocated'])->toBe(400.0);
})->group('loans');