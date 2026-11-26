<?php

use Illuminate\Support\Facades\Route;
use Tests\Support\RouteInventory;

it('has a controller method behind every application route', function () {
    $missing = [];

    foreach (Route::getRoutes()->getRoutes() as $route) {
        $action = $route->getActionName();

        if (! str_starts_with($action, 'App\\')) {
            continue;
        }

        [$class, $method] = array_pad(explode('@', $action, 2), 2, '__invoke');

        if (! class_exists($class) || ! method_exists($class, $method)) {
            $missing[] = RouteInventory::label($route) . " -> {$class}@{$method}";
        }
    }

    $missing = array_values(array_unique($missing));
    sort($missing);

    expect($missing)->toBe([]);
})->group('audit');