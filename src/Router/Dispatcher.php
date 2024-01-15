<?php

namespace Phprest\Router;

use League\Route\Dispatcher as LeagueDispatcher;
use Psr\Http\Message\ServerRequestInterface;

class Dispatcher extends LeagueDispatcher
{
    public function __construct(
        private readonly ServerRequestInterface $request,
        $data
    ) {
        parent::__construct($data);
    }

    protected function handleFound(callable $route, array $vars)
    {
        return call_user_func_array($route, array_merge([$this->request], $vars));
    }
}
