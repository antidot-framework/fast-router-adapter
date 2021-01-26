<?php

declare(strict_types=1);

namespace Antidot\Fast\Router;

use Antidot\Application\Http\Middleware\CallableMiddleware;
use Antidot\Application\Http\Middleware\MiddlewarePipeline;
use Antidot\Application\Http\Middleware\PipedRouteMiddleware;
use Antidot\Application\Http\Middleware\SyncMiddlewareQueue;
use Antidot\Application\Http\Route;
use Antidot\Application\Http\Router;
use Antidot\Container\MiddlewareFactory;
use Antidot\Container\RequestHandlerFactory;
use FastRoute\DataGenerator\GroupCountBased;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use FastRoute\RouteParser\Std;
use LogicException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class FastRouter implements Router
{
    private RouteCollector $routeCollector;
    private MiddlewareFactory $middlewareFactory;
    private RequestHandlerFactory $requestHandlerFactory;
    private Dispatcher\GroupCountBased $dispatcher;

    public function __construct(
        MiddlewareFactory $middlewareFactory,
        RequestHandlerFactory $requestHandlerFactory
    ) {
        $this->routeCollector = new RouteCollector(new Std(), new GroupCountBased());
        $this->dispatcher = new Dispatcher\GroupCountBased($this->routeCollector->getData());
        $this->middlewareFactory = $middlewareFactory;
        $this->requestHandlerFactory = $requestHandlerFactory;
    }

    public function append(Route $route): void
    {
        $this->routeCollector->addRoute($route->method(), $route->path(), $route->pipeline());
    }

    public function match(ServerRequestInterface $request): PipedRouteMiddleware
    {
        $dispatcher = new Dispatcher\GroupCountBased($this->routeCollector->getData());
        $routeInfo = $dispatcher->dispatch($request->getMethod(), $request->getUri()->getPath());
        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
            case Dispatcher::METHOD_NOT_ALLOWED:
                return new PipedRouteMiddleware(new MiddlewarePipeline(new SyncMiddlewareQueue()), true, []);
            case Dispatcher::FOUND:
                $pipeline = $this->getPipeline($routeInfo[1]);
                return new PipedRouteMiddleware($pipeline, false, $routeInfo[2]);
        }

        throw new LogicException('Something went wrong in routing.');
    }

    private function getPipeline(array $pipes): MiddlewarePipeline
    {
        $pipeline = new MiddlewarePipeline(new SyncMiddlewareQueue());
        $middlewarePipeline = $pipes;
        $handler = array_pop($middlewarePipeline);
        foreach ($middlewarePipeline as $middleware) {
            $pipeline->pipe($this->middlewareFactory->create($middleware));
        }

        $requestHandlerFactory = $this->requestHandlerFactory;
        $pipeline->pipe(new CallableMiddleware(
            static function (ServerRequestInterface $request) use (
                $handler,
                $requestHandlerFactory
            ): ResponseInterface {
                $handler = $requestHandlerFactory->create($handler);

                return $handler->handle($request);
            }
        ));

        return $pipeline;
    }
}
