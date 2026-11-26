<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Tests\Support\RouteInventory;
use Tests\Support\Scenario;

it('keeps the public routes to known pages', function () {
    $allowed = ['/', 'login', 'register', 'forgot-password', 'reset-password*', 'about', 'terms', 'contact', 'awaiting-*', 'up', 'storage/*', 'sanctum/*'];

    $unexpected = collect(Route::getRoutes()->getRoutes())
        ->filter(fn ($route) => RouteInventory::isPublic($route))
        ->reject(fn ($route) => Str::is($allowed, $route->uri()))
        ->map(fn ($route) => RouteInventory::label($route))
        ->unique()
        ->sort()
        ->values()
        ->all();

    expect($unexpected)->toBe([]);
})->group('security');

it('denies role restricted pages to every role outside the list', function () {
    $roles = ['member', 'loan_officer', 'accountant', 'management', 'admin'];
    $users = collect($roles)->mapWithKeys(fn ($role) => [$role => Scenario::user($role)]);
    $leaks = [];

    foreach (RouteInventory::plainGetRoutes() as $route) {
        $allowed = RouteInventory::allowedRoles($route);

        if ($allowed === null) {
            continue;
        }

        foreach ($roles as $role) {
            if (in_array($role, $allowed, true)) {
                continue;
            }

            $status = $this->actingAs($users[$role])->get('/' . $route->uri())->getStatusCode();

            if ($status === 200 || $status >= 500) {
                $leaks[] = "{$role} received {$status} on " . RouteInventory::label($route);
            }
        }
    }

    expect($leaks)->toBe([]);
})->group('security');

it('stops a member opening another member record', function (string $suffix) {
    $own = Scenario::member();
    $other = Scenario::member();

    $this->actingAs($own->user)
        ->get("/members/{$other->id}{$suffix}")
        ->assertForbidden();
})->with(['', '/accounts', '/transactions', '/loans', '/dividends', '/guarantees'])->group('security');

it('lets a member open their own record', function () {
    $own = Scenario::member();

    $status = $this->actingAs($own->user)->get("/members/{$own->id}")->getStatusCode();

    expect($status)->toBeLessThan(500)->not->toBe(403);
})->group('security');

it('sends guests to the login page', function () {
    $this->get('/dashboard')->assertRedirect('/login');
})->group('security');