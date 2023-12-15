<?php

namespace Phprest\Middleware;

use League\Container\ContainerInterface;
use Negotiation\FormatNegotiator;
use Phprest\Application;
use Phprest\HttpFoundation\Request;
use Phprest\Util;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Request as BaseRequest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ApiVersion implements HttpKernelInterface
{
    use Util\Mime;

    protected Application $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * @param BaseRequest $request
     * @param int $type
     * @param bool $catch
     *
     * @return ResponseInterface
     */
    public function handle(BaseRequest $request, $type = self::MAIN_REQUEST, $catch = true)
    {
        $request        = new Request($request);
        $mimeProcResult = $this->processMime(
            (new FormatNegotiator())->getBest($request->headers->get('Accept', '*/*'))->getValue()
        );

        $request->setApiVersion(
            str_pad($mimeProcResult->apiVersion, 3, '.0')
        );

        return $this->app->handle($request, $type, $catch);
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->app->getConfiguration()->getContainer();
    }
}
