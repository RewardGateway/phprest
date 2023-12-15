<?php

declare(strict_types=1);

namespace Phprest;

use League\Container\Container as LeagueContainer;
use League\Container\Definition\DefinitionFactory;
use League\Container\Exception\NotFoundException;
use ReflectionNamedType;

class Container extends LeagueContainer
{
    public function get($alias, array $args = [])
    {
        try {
            return parent::get($alias, $args);
        } catch (NotFoundException) {
            return $this->attemptReflect($alias, $args);
        }
    }

    public function attemptReflect($alias, $args = [])
    {
        try {
            return $this->reflect($alias);
        } catch (\Throwable $e) {
            error_log('Could not reflect class: ' . $e->getMessage());
            throw new NotFoundException();
        }
    }

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
            throw new \ReflectionException(sprintf(
                'Unable to reflect on the class [%s], does the class exist and is it properly autoloaded?',
                $class
            ));
        }

        $factory = new DefinitionFactory();
        $factory->setContainer($this);
        $definition = $factory->getDefinition($class, $class);

        if (null === $constructor) {
            return $definition->build();
        }

        // loop through dependencies and get aliases/values
        foreach ($constructor->getParameters() as $param) {
            $dependency = $param->getType();

            // if the dependency is a class, just register its name as an
            // argument with the definition
            if (
                $dependency instanceof ReflectionNamedType
                && !$dependency->isBuiltin()
                && (
                    $this->hasShared($dependency->getName())
                    || $this->has($dependency->getName())
                    || class_exists($dependency->getName())
                )
            ) {
                $definition->withArgument($this->get($dependency->getName()));
                continue;
            }

            // if the dependency is not a class we attempt to get a default value
            if ($param->isDefaultValueAvailable()) {
                $definition->withArgument($param->getDefaultValue());
                continue;
            }

            throw new NotFoundException(
                sprintf('Unable to resolve a non-class dependency of [%s] for [%s]', $param, $class)
            );
        }

        return $definition->build();
    }
}
