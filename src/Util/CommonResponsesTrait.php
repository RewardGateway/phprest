<?php

namespace Phprest\Util;

use Fig\Http\Message\StatusCodeInterface;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Shared exception methods used in controllers
 */
trait CommonResponsesTrait
{
    abstract protected function getSerializer(): SerializerInterface;

    abstract protected function getRequest(): Request;

    abstract protected function getResponse(): Response;

    abstract protected function setResponse(Response $response): void;

    /**
     * The request has succeeded. The meaning of the success depends on the HTTP method:
     *   GET: The resource has been fetched and is transmitted in the message body.
     *   HEAD: The entity headers are in the message body.
     *   PUT or POST: The resource describing the result of the action is transmitted in the message body.
     *   TRACE: The message body contains the request message as received by the server.
     */
    protected function okResponse($data = ''): Response
    {
        $this->setResponseStatus(StatusCodeInterface::STATUS_OK);

        return $this->jsonResponse($data);
    }

    /**
     * The request has been received but not yet acted upon.
     * It is noncommittal, since there is no way in HTTP to later send an asynchronous response
     * indicating the outcome of the request.
     * It is intended for cases where another process or server handles the request,
     * or for batch processing.
     */
    protected function acceptedResponse($data): Response
    {
        $this->setResponseStatus(StatusCodeInterface::STATUS_ACCEPTED);

        return $this->jsonResponse($data);
    }

    /**
     * The request has succeeded and a new resource has been created as a result.
     * This is typically the response sent after POST requests, or some PUT requests.
     */
    protected function createdResponse($data, string $location = null): Response
    {
        if ($location) {
            $this->getResponse()->headers->set('Location', $location);
        }

        $this->setResponseStatus(StatusCodeInterface::STATUS_CREATED);

        return $this->jsonResponse($data);
    }

    /**
     * There is no content to send for this request, but the headers may be useful.
     * The user-agent may update its cached headers for this resource with the new ones.
     */
    protected function noContentResponse(): Response
    {
        $this->setResponseStatus(StatusCodeInterface::STATUS_NO_CONTENT);

        return $this->jsonResponse(null);
    }

    /**
     * @param array|object|string|null $data
     *
     * @return Response
     */
    protected function jsonResponse($data): Response
    {
        if (is_array($data) || is_object($data)) {
            $responseBody = $this->getSerializer()->serialize($data, 'json');
        } else {
            $responseBody = $data ?? '';
        }

        $this->getResponse()->setContent($responseBody);
        $this->getResponse()->headers->set('Content-Type', 'application/json');

        return $this->getResponse();
    }

    private function setResponseStatus(int $httpStatusCode): void
    {
        $this->setResponse(
            $this->getResponse()->setStatusCode($httpStatusCode)
        );
    }
}
