<?php

declare(strict_types=1);

namespace Phprest;

use ReflectionNamedType;

use function class_exists;
use function in_array;
use function is_null;
use function sprintf;
use function mb_strtolower;

class Container extends \League\Container\Container
{
    private const TYPES_TO_IGNORE = [
        'bool',
        'int',
        'float',
        'string',
        'array',
        'object',
        'callable',
        'iterable',
        'resource',
    ];

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
            throw new \League\Container\Exception\ReflectionException(sprintf(
                'Unable to reflect on the class [%s], does the class exist and is it properly autoloaded?',
                $class
            ));
        }

        $factory = $this->getDefinitionFactory();
        $definition = $factory($class, $class, $this);

        if (is_null($constructor)) {
            return $definition;
        }

        // loop through dependencies and get aliases/values
        foreach ($constructor->getParameters() as $param) {
            $dependency = $param->getType();

            // if the dependency is a class, just register its name as an
            // argument with the definition
            if (
                $dependency instanceof ReflectionNamedType
                && !in_array(mb_strtolower($dependency->getName()), self::TYPES_TO_IGNORE, true)
                && (
                    $this->isSingleton($dependency->getName())
                    || $this->isRegistered($dependency->getName())
                    || class_exists($dependency->getName())
                )
            ) {
                $definition->withArgument($dependency->getName());
                continue;
            }

            // if the dependency is not a class we attempt to get a default value
            if ($param->isDefaultValueAvailable()) {
                $definition->withArgument($param->getDefaultValue());
                continue;
            }

            throw new \League\Container\Exception\UnresolvableDependencyException(
                sprintf('Unable to resolve a non-class dependency of [%s] for [%s]', $param, $class)
            );
        }

        return $definition;
    }
}
