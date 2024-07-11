<?php

namespace Phprest\Router;

use League\Route\Dispatcher as LeagueDispatcher;
use League\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;

class Dispatcher extends LeagueDispatcher
{
    public function __construct(
        private readonly ServerRequestInterface $request,
        $data,
        private readonly ContainerInterface $container
    ) {
        parent::__construct($data);
    }

    /**
     * @param callable|array|string $route
     */
    protected function handleFound($route, array $vars)
    {
        $controller = $route;

        if (is_string($route) && str_contains($route, '::')) {
            $controller = explode('::', $route);
        }

        if (is_array($controller)) {
            $controller = [
                (is_object($controller[0])) ? $controller[0] : $this->container->get($controller[0]),
                $controller[1]
            ];
        }

        return call_user_func_array($controller, array_merge([$this->request], array_values($vars)));
    }
}
