<?php

namespace SergiX44\Container;

use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionException;

class Container implements ContainerInterface
{
    private array $definitions;

    private ContainerInterface $delegate;

    /**
     * @inheritDoc
     */
    public function get(string $id)
    {
        return $this->resolve($id);
    }

    /**
     * @inheritDoc
     */
    public function has(string $id): bool
    {
        return class_exists($id);
    }

    /**
     * @throws ReflectionException
     */
    public function resolve(string $class): object
    {
        $reflectionClass = new ReflectionClass($class);

        if (
            ($constructor = $reflectionClass->getConstructor()) === null ||
            ($params = $constructor->getParameters()) === []
        ) {
            return $reflectionClass->newInstance();
        }

        $newInstanceParams = [];
        foreach ($params as $param) {
            if ($param->getType() === null) {
                $newInstanceParams[] = $param->getDefaultValue();
            } else {
                $newInstanceParams[] = $this->resolve(
                    $param->getType()->getName()
                );
            }
        }

        return $reflectionClass->newInstanceArgs($newInstanceParams);
    }

    public function set(string $abstract, callable $resolver)
    {
    }
}