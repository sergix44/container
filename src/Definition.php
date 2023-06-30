<?php

namespace SergiX44\Container;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use SergiX44\Container\Exception\ContainerException;

class Definition
{
    private bool $shared = false;

    public function __construct(
        public readonly string $id,
        private readonly mixed $resolver = null,
        private ?object $instance = null
    ) {
    }

    public function singleton(): static
    {
        $this->shared = true;

        return $this;
    }

    public function hasInstance(): bool
    {
        return $this->instance !== null;
    }

    public function getInstance(): object|null
    {
        return $this->instance;
    }

    /**
     * @throws ContainerException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function make(ContainerInterface $container): mixed
    {
        if ($this->hasInstance()) {
            return $this->getInstance();
        }

        $resolved = $this->resolver;

        // resolve the callable, if the resolver is a callable
        if (is_callable($this->resolver)) {
            $resolved = ($this->resolver)($container);
        }

        // if is a string (class concrete) and can be resolved via container
        if (is_string($resolved) && class_exists($resolved) && !enum_exists($resolved)) {
            $resolved = $container->get($resolved);
        }

        if ($resolved === null) {
            throw new ContainerException("Cannot resolve definition '$this->id'");
        }

        if ($this->shared) {
            $this->instance = $resolved;
        }

        return $resolved;
    }
}
