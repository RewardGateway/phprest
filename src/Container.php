<?php

declare(strict_types=1);

namespace Phprest;

use function class_exists;
use function is_null;
use function sprintf;

class Container extends \League\Container\Container
{
    /**
     * {@inheritDoc}
     */
    protected function reflect($class)
    {
        // try to reflect on the class so we can build a definition
        try {
            $reflection  = new \ReflectionClass($class);
            $constructor = $reflection->getConstructor();
        } catch (\ReflectionException $e) {
            throw new \League\Container\Exception\ReflectionException(
                sprintf(
                    'Unable to reflect on the class [%s], does the class exist and is it properly autoloaded?',
                    $class
                )
            );
        }

        $factory = $this->getDefinitionFactory();
        $definition = $factory($class, $class, $this);

        if (is_null($constructor)) {
            return $definition;
        }

        // loop through dependencies and get aliases/values
        foreach ($constructor->getParameters() as $param) {
            $dependency = $param->getType();

            if ($dependency && class_exists($dependency->getName())) {
                // if the dependency is a class, just register it's name as an
                // argument with the definition
                $definition->withArgument($dependency->getName());
            } else {
                // if the dependency is not a class we attempt to get a dafult value
                if ($param->isDefaultValueAvailable()) {
                    $definition->withArgument($param->getDefaultValue());
                    continue;
                }

                throw new \League\Container\Exception\UnresolvableDependencyException(
                    sprintf('Unable to resolve a non-class dependency of [%s] for [%s]', $param, $class)
                );
            }
        }

        return $definition;
    }
}
