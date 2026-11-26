<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    // Stops the run before any table is wiped unless the database name ends in _test.
    protected function refreshApplication(): void
    {
        parent::refreshApplication();

        $connection = config('database.default');
        $database = (string) config("database.connections.{$connection}.database");

        if (! str_ends_with($database, '_test')) {
            throw new RuntimeException("Refusing to run tests against '{$database}'. Use a database whose name ends in _test.");
        }
    }

    // Turns off the Vite manifest so Inertia pages render without a frontend build.
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }
}