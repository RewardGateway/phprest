<?php

namespace Phprest\Service\Hateoas;

use Hateoas\Hateoas;
use League\Container\Container;
use League\Container\ContainerInterface;

trait Getter
{
    protected function serviceHateoas(): Hateoas
    {
        return $this->getContainer()->get(Config::getServiceName());
    }

    /**
     * Returns the DI container.
     *
     * @return Container&ContainerInterface
     */
    abstract protected function getContainer();
}
