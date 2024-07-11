<?php

namespace Phprest\Router;

use Closure;
use FastRoute\DataGenerator;
use FastRoute\RouteParser;
use League\Container\ContainerInterface;
use League\Route\Middleware\ExecutionChain;
use League\Route\RouteCollection as LeagueRouteCollection;
use League\Route\Strategy\ApplicationStrategy;
use League\Route\Strategy\StrategyInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\HttpFoundation\Response;

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

    public function getDispatcher(ServerRequestInterface $request)
    {
        if (is_null($this->getStrategy())) {
            $this->setStrategy(new ApplicationStrategy());
        }

        $this->prepRoutes($request);

        $dispatcher = new \Phprest\Router\Dispatcher($request, $this->getData(), $this->container);

        return $dispatcher->setStrategy($this->getStrategy());
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

    public function dispatch(ServerRequestInterface $request, ResponseInterface $response)
    {
        $dispatcher = $this->getDispatcher($request);
        $execChain  = $dispatcher->handle($request);

        foreach ($this->getMiddlewareStack() as $middleware) {
            $execChain->middleware($middleware);
        }

        try {
            if ($execChain instanceof ResponseInterface || $execChain instanceof Response) {
                return $execChain;
            }

            return $execChain->execute($request, $response);
        } catch (\Exception $exception) {
            $middleware = $this->getStrategy()->getExceptionDecorator($exception);

            /** @phpstan-ignore-next-line */
            return (new ExecutionChain())->middleware($middleware)->execute($request, $response);
        }
    }
}
