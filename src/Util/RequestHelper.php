<?php

namespace Phprest\Util;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as PsrRequest;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Slim\Psr7\Factory;

/**
 * RG handle conversion of request objects from and to Symfony and PSR request formats
 */
class RequestHelper
{
    public static function toPsr(SymfonyRequest $request): PsrRequest
    {
        $psr17Factory = new Factory\ServerRequestFactory();
        $streamFactory = new Factory\StreamFactory();
        $uploadedFileFactory = new Factory\UploadedFileFactory();
        $responseFactory = new Factory\ResponseFactory();
        $psrHttpFactory = new PsrHttpFactory(
            $psr17Factory,
            $streamFactory,
            $uploadedFileFactory,
            $responseFactory
        );

        return $psrHttpFactory->createRequest($request);
    }

    public static function createResponse(): ResponseInterface
    {
        $responseFactory = new Factory\ResponseFactory();

        return $responseFactory->createResponse();
    }
}
