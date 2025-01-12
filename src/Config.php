<?php

namespace Phprest;

use InvalidArgumentException;
use League\BooBoo\BooBoo;
use League\Container\ContainerInterface;
use League\Event\Emitter as EventEmitter;
use League\Event\EmitterInterface as EventEmitterInterface;
use League\Route\Strategy\StrategyInterface;
use Phprest\ErrorHandler\Formatter\JsonXml as JsonXmlFormatter;
use Phprest\ErrorHandler\Handler\Log as LogHandler;
use Phprest\Router\RouteCollection;
use Phprest\Router\Strategy as RouterStrategy;
use Phprest\Service\Hateoas\Config as HateoasConfig;
use Phprest\Service\Hateoas\Service as HateoasService;
use Phprest\Service\Logger\Config as LoggerConfig;
use Phprest\Service\Logger\Service as LoggerService;

class Config
{
    protected string $vendor;
    protected string $apiVersion;
    protected bool $debug = false;
    protected ContainerInterface $container;
    protected RouteCollection $router;
    protected EventEmitterInterface $eventEmitter;
    protected BooBoo $errorHandler;
    protected HateoasConfig $hateoasConfig;
    protected HateoasService $hateoasService;
    protected LoggerConfig $loggerConfig;
    protected LoggerService $loggerService;
    protected LogHandler $logHandler;

    public function __construct(string $vendor, string $apiVersion, bool $debug = false)
    {
        if (! preg_match('#^' . Application::API_VERSION_REG_EXP . '$#', $apiVersion)) {
            throw new InvalidArgumentException('Api version is not valid');
        }

        $this->vendor       = $vendor;
        $this->apiVersion   = $apiVersion;
        $this->debug        = $debug;

        $this->setContainer(new Container());
        $this->setRouter(new RouteCollection($this->getContainer()));
        $this->setEventEmitter(new EventEmitter());
        $this->setHateoasConfig(new HateoasConfig($debug));
        $this->setHateoasService(new HateoasService());
        $this->setLoggerConfig(new LoggerConfig('phprest'));
        $this->setLoggerService(new LoggerService());
        $this->setLogHandler(new LogHandler());
        $this->setRouterStrategy(new RouterStrategy($this->getContainer()));

        $errorHandler = new BooBoo([new JsonXmlFormatter($this)]);
        $errorHandler->silenceAllErrors(false);
        $errorHandler->treatErrorsAsExceptions(false);

        $this->setErrorHandler($errorHandler);
    }

    public function getVendor(): string
    {
        return $this->vendor;
    }

    public function getApiVersion(): string
    {
        return $this->apiVersion;
    }

    public function isDebug(): bool
    {
        return $this->debug;
    }

    public function setContainer(ContainerInterface $container): self
    {
        $this->container = $container;

        return $this;
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    public function setRouter(RouteCollection $router): self
    {
        $this->router = $router;

        return $this;
    }

    public function setRouterStrategy(StrategyInterface $strategy): self
    {
        $this->router->setStrategy($strategy);

        return $this;
    }

    public function getRouter(): RouteCollection
    {
        return $this->router;
    }

    public function setEventEmitter(EventEmitterInterface $eventEmitter): self
    {
        $this->eventEmitter = $eventEmitter;

        return $this;
    }

    public function getEventEmitter(): EventEmitterInterface
    {
        return $this->eventEmitter;
    }

    public function setErrorHandler(BooBoo $errorHandler): self
    {
        $this->errorHandler = $errorHandler;

        return $this;
    }

    public function getErrorHandler(): BooBoo
    {
        return $this->errorHandler;
    }

    public function setHateoasConfig(HateoasConfig $config): self
    {
        $this->hateoasConfig = $config;

        return $this;
    }

    public function getHateoasConfig(): HateoasConfig
    {
        return $this->hateoasConfig;
    }

    public function setHateoasService(HateoasService $service): self
    {
        $this->hateoasService = $service;

        return $this;
    }

    public function getHateoasService(): HateoasService
    {
        return $this->hateoasService;
    }

    public function setLoggerConfig(LoggerConfig $config): self
    {
        $this->loggerConfig = $config;

        return $this;
    }

    public function getLoggerConfig(): LoggerConfig
    {
        return $this->loggerConfig;
    }

    public function setLoggerService(LoggerService $service): self
    {
        $this->loggerService = $service;

        return $this;
    }

    public function getLoggerService(): LoggerService
    {
        return $this->loggerService;
    }

    public function setLogHandler(LogHandler $logHandler): self
    {
        $this->logHandler = $logHandler;

        return $this;
    }

    public function getLogHandler(): LogHandler
    {
        return $this->logHandler;
    }
}
