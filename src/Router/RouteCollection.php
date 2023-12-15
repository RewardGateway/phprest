<?php

namespace Phprest\Router;

use Closure;
use FastRoute\DataGenerator;
use FastRoute\RouteParser;
use League\Container\ContainerInterface;
use League\Route\RouteCollection as LeagueRouteCollection;
use League\Route\Strategy\StrategyInterface;

class RouteCollection extends LeagueRouteCollection
{
    /**
     * @var array keys: method, route, handler
     */
    protected array $routingTable = [];

    public function __construct(
        ContainerInterface $container = null,
        RouteParser $parser = null,
        DataGenerator $generator = null
    ) {
        parent::__construct($container, $parser, $generator);

        $this->addPatternMatcher('any', '\d\.\d');
    }

    /**
     * Add a route to the collection.
     *
     * @param  string $httpMethod
     * @param  string $route
     * @param  string|Closure $handler
     * @param StrategyInterface|null $strategy
     *
     * @return RouteCollection
     */
    public function addRoute($httpMethod, $route, $handler, StrategyInterface $strategy = null): RouteCollection
    {
        parent::addRoute($httpMethod, $route, $handler);

        $this->routingTable[] = [
            'method'    => $httpMethod,
            'route'     => $route,
            'handler'   => $handler,
        ];

        return $this;
    }

    /**
     * @return array keys: method, route, handler
     */
    public function getRoutingTable(): array
    {
        return $this->routingTable;
    }
}
