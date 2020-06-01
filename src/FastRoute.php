<?php

declare(strict_types=1);

namespace Antidot\Fast\Router;

use Antidot\Application\Http\Route;

class FastRoute implements Route
{
    private array $method;
    private string $name;
    private string $path;
    private array $pipeline;

    public function __construct(array $method, string $name, string $path, array $pipeline)
    {
        $this->method = $method;
        $this->name = $name;
        $this->path = $path;
        $this->pipeline = $pipeline;
    }

    public function pipeline(): array
    {
        return $this->pipeline;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function method(): array
    {
        return $this->method;
    }

    public function path(): string
    {
        return $this->path;
    }
}
