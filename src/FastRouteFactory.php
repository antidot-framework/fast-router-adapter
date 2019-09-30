<?php

declare(strict_types=1);

namespace Antidot\Fast\Router;

use Antidot\Application\Http\Route;
use Antidot\Application\Http\RouteFactory;

class FastRouteFactory implements RouteFactory
{
    public function create(array $methods, array $middleware, string $uri, string $name): Route
    {
        return new FastRoute($methods, $name, $uri, $middleware);
    }
}
