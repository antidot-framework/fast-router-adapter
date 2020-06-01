<?php

declare(strict_types=1);

namespace Antidot\Fast\Router\Container\Config;

use Antidot\Application\Http\RouteFactory;
use Antidot\Application\Http\Router;
use Antidot\Fast\Router\FastRouteFactory;
use Antidot\Fast\Router\FastRouter;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'services' => [
                Router::class => FastRouter::class,
                RouteFactory::class => FastRouteFactory::class,
            ],
        ];
    }
}
