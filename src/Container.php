<?php

namespace SergiX44\Container;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionClass;
use ReflectionException;
use SergiX44\Container\Exception\ContainerException;
use SergiX44\Container\Exception\NotFoundException;
use Throwable;

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

        if (array_key_exists($id, $this->definitions)) {
            return $this->definitions[$id]?->make($this);
        }

        try {
            return $this->resolve($id);
        } catch (Throwable $e) {
            throw new NotFoundException("Cannot resolve '$id'", previous: $e);
        }
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

        return false;
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

    /**
     * @param string  $class
     *
     * @throws ContainerException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    private function resolve(string $class): object|string|null
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
            $type = $param->getType()?->getName();
            $newInstanceParams[] = match (true) {
                $type !== null && $this->has($type) => $this->get($type), // via definitions
                $param->isOptional() => $param->getDefaultValue(), // use default when available
                $type !== null && class_exists($type) && !enum_exists($type) => $this->resolve($type), // via reflection
                default => throw new ContainerException("Cannot resolve constructor parameter '\${$param->getName()}::{$param->getDeclaringClass()?->getName()}'"),
            };
        }

        return $reflectionClass->newInstanceArgs($newInstanceParams);
    }
}