<?php

namespace Phprest\Service\Hateoas;

use Hateoas\Hateoas;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializationContext;
use Phprest\Exception;
use Phprest\Util\Mime;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

trait Util
{
    use Mime;

    /**
     * @param mixed $value
     * @param Request $request
     * @param Response $response
     *
     * @throws Exception\NotAcceptable
     *
     * @return Response
     */
    protected function serialize($value, Request $request, Response $response)
    {
        $mimeProcResult = $this->processMime(
            $this->getBestMediaType($request->headers->get('Accept', '*/*'))
        );

        if ($mimeProcResult->mime === '*/*') {
            $mimeProcResult->mime = 'application/vnd.' . $mimeProcResult->vendor .
                '+json; version=' . $mimeProcResult->apiVersion;
            $mimeProcResult->format = 'json';
        }

        if (in_array($mimeProcResult->format, ['json', 'xml'])) {
            $response->setContent(
                $this->serviceHateoas()->serialize(
                    $value,
                    $mimeProcResult->format,
                    SerializationContext::create()->setVersion($mimeProcResult->apiVersion)
                )
            );

            $response->headers->set('Content-Type', $mimeProcResult->mime);

            return $response;
        }

        throw new Exception\NotAcceptable(0, [$mimeProcResult->mime . ' is not supported']);
    }

    /**
     * @param string $type
     * @param Request $request
     *
     * @throws Exception\UnsupportedMediaType
     *
     * @return mixed
     */
    protected function deserialize($type, Request $request)
    {
        $mimeProcResult = $this->processMime($request->headers->get('Content-Type'));

        if (is_null($mimeProcResult->format)) {
            throw new Exception\UnsupportedMediaType();
        }

        return $this->serviceHateoas()->getSerializer()->deserialize(
            $request->getContent(),
            $type,
            $mimeProcResult->format,
            DeserializationContext::create()->setVersion($mimeProcResult->apiVersion)
        );
    }

    /**
     * @return Hateoas
     */
    abstract protected function serviceHateoas();

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
