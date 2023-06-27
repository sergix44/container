<?php

namespace SergiX44\Container;

use Psr\Container\ContainerInterface;
use ReflectionClass;
use SergiX44\Container\Exception\ContainerException;
use SergiX44\Container\Exception\NotFoundException;

class Container implements ContainerInterface
{
    /**
     * @var Definition[]
     */
    private array $definitions = [];

    private ?ContainerInterface $delegate = null;

    /**
     * @template T
     * @param  class-string<T>  $id
     *
     * @return T
     *
     * @inheritDoc
     */
    public function get(string $id)
    {
        if ($this->delegate !== null && $this->delegate->has($id)) {
            return $this->delegate->get($id);
        }

        if ($this->has($id)) {
            return $this->resolve($id);
        }

        throw new NotFoundException("Cannot find a definition resolver for $id");
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

        // if is not registered, check if the class exists
        return class_exists($id) && !enum_exists($id);
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

    private function resolve(string $id): object
    {
        if (array_key_exists($id, $this->definitions)) {
            return $this->definitions[$id]?->make($this);
        }

        return $this->reflectionInstance($id);
    }

    /**
     * @throws \ReflectionException|ContainerException
     */
    private function reflectionInstance(string $class)
    {
        $reflectionClass = new ReflectionClass($class);

        if (
            ($constructor = $reflectionClass->getConstructor()) === null ||
            ($constructorParams = $constructor->getParameters()) === []
        ) {
            return $reflectionClass->newInstance();
        }

        $newInstanceParams = [];
        foreach ($constructorParams as $param) {
            $newInstanceParams[] = match (true) {
                $param->isOptional() => $param->getDefaultValue(),
                $param->hasType() && $this->has($param->getType()->getName()) => $this->resolve($param->getType()->getName()),
                default => throw new ContainerException("Cannot resolve constructor parameter \${$param->getName()}::{$param->getDeclaringClass()?->getName()}"),
            };
        }

        return $reflectionClass->newInstanceArgs($newInstanceParams);
    }
}