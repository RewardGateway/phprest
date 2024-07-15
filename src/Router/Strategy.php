<?php

namespace Phprest\Router;

use Closure;
use Hateoas\Hateoas;
use League\Container\ContainerInterface;
use League\Route\Route;
use League\Route\Strategy\JsonStrategy;
use League\Route\Strategy\StrategyInterface;
use Phprest\Service;
use Phprest\Util\RequestHelper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

class Strategy extends JsonStrategy implements StrategyInterface
{
    use Service\Hateoas\Util;

    public function __construct(
        protected ContainerInterface $container
    ) {
    }

    public function getCallable(Route $route, array $vars): Closure
    {
        return function (
            ServerRequestInterface $request,
            ResponseInterface $response,
            callable $next
        ) use (
            $route,
            $vars
        ) {
            $symfonyRequest = RequestHelper::toSymfonyRequest($request);
            $return = $this->invokeController(
                $route->getCallable(),
                array_merge([$symfonyRequest], array_values($vars))
            );

            if (! $return instanceof ResponseInterface) {
                throw new RuntimeException(
                    'Route callables must return an instance of (Psr\Http\Message\ResponseInterface)'
                );
            }

            $response = $return;
            $response = $next($request, $response);

            if (! $response->hasHeader('content-type')) {
                return $response->withHeader('content-type', 'application/json');
            }

            return $response;
        };
    }

    /**
     * Invoke a controller action
     *
     * @param  string|array|\Closure $controller
     * @param  array                 $vars
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function invokeController($controller, array $vars = [])
    {
        if (is_array($controller)) {
            $controller = [
                (is_object($controller[0])) ? $controller[0] : $this->getContainer()->get($controller[0]),
                $controller[1]
            ];
        }

        return call_user_func_array($controller, array_values($vars));
    }

    /**
     * @return Hateoas
     *
     * @codeCoverageIgnore
     */
    protected function serviceHateoas(): Hateoas
    {
        return $this->getContainer()->get(Service\Hateoas\Config::getServiceName());
    }

    protected function getContainer()
    {
        return $this->container;
    }
}
