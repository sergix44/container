<?php

namespace SergiX44\Container;

use Psr\Container\ContainerInterface;
use ReflectionClass;
use SergiX44\Container\Exception\NotFoundException;

class Container implements ContainerInterface
{
    /**
     * @var Definition[]
     */
    private array $definitions = [];

    private ?ContainerInterface $delegate = null;

    /**
     * @inheritDoc
     */
    public function get(string $id)
    {
        if ($this->has($id)) {
            return $this->resolve($id);
        }

        throw new NotFoundException();
    }

    /**
     * @inheritDoc
     */
    public function has(string $id): bool
    {
        // check if the delegate can resolve it, if defined
        if ($this->delegate !== null && $this->delegate->has($id)) {
            return true;
        }

        // check if is something we match right away
        if (array_key_exists($id, $this->definitions)) {
            return true;
        }

        // check if is a registered definition
        foreach ($this->definitions as $definition) {
            if ($definition->matches($id)) {
                return true;
            }
        }

        // if is not registered, check if the class exists
        return class_exists($id);
    }

    public function resolve(string $id): object
    {
        $definition = $this->definitions[$id] ?? null;

        if ($definition == null) {
            foreach ($this->definitions as $d) {
                if ($d->matches($id)) {
                    $definition = $d;
                    break;
                }
            }
        }

        if ($definition !== null) {
            return $definition->getInstance();
        }

        return $this->reflectionInstance($id);
    }

    public function reflectionInstance(string $class)
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
                $newInstanceParams[] = $this->resolve($param->getType()->getName());
            }
        }

        return $reflectionClass->newInstanceArgs($newInstanceParams);
    }

    public function register(string $abstract, mixed $resolverOrConcrete): Definition
    {
        return $this->definitions[$abstract] = new Definition($abstract, resolver: $resolverOrConcrete);
    }

    public function set(string $abstract, object $concrete): void
    {
        $this->definitions[$abstract] = new Definition($abstract, instance: $concrete);
    }

    public function delegateTo(ContainerInterface $container): void
    {
        $this->delegate = $container;
    }
}