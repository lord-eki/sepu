<?php

namespace Tests\Support;

use Illuminate\Routing\Route as LaravelRoute;
use Illuminate\Support\Facades\Route;

final class RouteInventory
{
    // Returns every route that accepts GET and has no URI parameters.
    public static function plainGetRoutes(): array
    {
        return collect(Route::getRoutes()->getRoutes())
            ->filter(fn (LaravelRoute $route) => in_array('GET', $route->methods(), true) && ! str_contains($route->uri(), '{'))
            ->values()
            ->all();
    }

    // Returns the string middleware names attached to a route.
    public static function middleware(LaravelRoute $route): array
    {
        return array_values(array_filter($route->gatherMiddleware(), 'is_string'));
    }

    // Returns the roles every role: middleware on the route allows, or null when there is none.
    public static function allowedRoles(LaravelRoute $route): ?array
    {
        $sets = [];

        foreach (self::middleware($route) as $name) {
            if (str_starts_with($name, 'role:')) {
                $sets[] = explode(',', substr($name, 5));
            }
        }

        if ($sets === []) {
            return null;
        }

        return array_values(array_intersect(...$sets));
    }

    // Tells whether the route can be reached without logging in.
    public static function isPublic(LaravelRoute $route): bool
    {
        foreach (self::middleware($route) as $name) {
            if ($name === 'auth' || str_starts_with($name, 'auth:') || str_starts_with($name, 'role:')) {
                return false;
            }
        }

        return true;
    }

    // Formats a route as METHOD /uri for failure messages.
    public static function label(LaravelRoute $route): string
    {
        return $route->methods()[0] . ' /' . ltrim($route->uri(), '/');
    }
}