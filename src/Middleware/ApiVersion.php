<?php

namespace Phprest\Middleware;

use League\Container\ContainerInterface;
use Phprest\Application;
use Phprest\HttpFoundation\Request;
use Phprest\Util;
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
     * @return Response
     */
    public function handle(BaseRequest $request, $type = self::MASTER_REQUEST, $catch = true)
    {
        $request        = new Request($request);
        $mimeProcResult = $this->processMime(
            $this->getBestMediaType($request->headers->get('Accept', '*/*'))
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

    /**
     * Parse Accept header and return the best media type based on quality values
     *
     * @param string $acceptHeader
     * @return string
     */
    protected function getBestMediaType(string $acceptHeader): string
    {
        $parts = explode(',', $acceptHeader);
        $best = ['type' => '*/*', 'quality' => 0];

        foreach ($parts as $part) {
            $part = trim($part);
            $segments = explode(';', $part);
            $mediaType = trim($segments[0]);
            $quality = 1.0;

            // Check for quality parameter
            foreach (array_slice($segments, 1) as $param) {
                if (preg_match('/q\s*=\s*([0-9.]+)/', $param, $matches)) {
                    $quality = (float)$matches[1];
                    break;
                }
            }

            if ($quality > $best['quality'] || ($quality === $best['quality'] && $best['type'] === '*/*')) {
                $best = ['type' => $mediaType, 'quality' => $quality];
            }
        }

        return $best['type'];
    }
}
