<?php

namespace Phprest\Util;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as PsrRequest;
use Psr\Http\Message\ResponseInterface as PsrResponse;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Slim\Psr7\Factory;

/**
 * RG handle conversion of request objects from and to Symfony and PSR request formats
 */
class RequestHelper
{
    public static function toPsr(SymfonyRequest $request): PsrRequest
    {
        $psrHttpFactory = self::getPsrFactory();

        return $psrHttpFactory->createRequest($request);
    }

    public static function createResponse(): ResponseInterface
    {
        $responseFactory = new Factory\ResponseFactory();

        return $responseFactory->createResponse();
    }

    public static function toSymfonyRequest(PsrRequest $request): SymfonyRequest
    {
        return (new HttpFoundationFactory())->createRequest($request);
    }

    public static function toSymfonyResponse(PsrResponse $response): SymfonyResponse
    {
        return (new HttpFoundationFactory())->createResponse($response);
    }

    public static function toPsrResponse(SymfonyResponse $response): PsrResponse
    {
        return self::getPsrFactory()->createResponse($response);
    }

    private static function getPsrFactory(): PsrHttpFactory
    {
        $psr17Factory = new Factory\ServerRequestFactory();
        $streamFactory = new Factory\StreamFactory();
        $uploadedFileFactory = new Factory\UploadedFileFactory();
        $responseFactory = new Factory\ResponseFactory();

        return new PsrHttpFactory(
            $psr17Factory,
            $streamFactory,
            $uploadedFileFactory,
            $responseFactory
        );
    }
}
