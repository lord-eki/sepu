<?php

namespace App\Support;

use DomainException;
use Throwable;

class ErrorMessage
{
    public static function from(Throwable $e,string $fallback = 'Something went wrong. Please try again or contact support.'): string {
        if ($e instanceof DomainException) {
            return $e->getMessage();
        }

        report($e); 

        return config('app.debug') ? $e->getMessage() : $fallback;
    }
}