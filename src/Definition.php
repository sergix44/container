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

    /**
     * @throws ContainerException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface|\ReflectionException
     */
    public function make(ContainerInterface $container): mixed
    {
        if ($this->instance !== null) {
            return $this->instance;
        }

        $resolved = $this->resolver;

        // resolve the callable, if the resolver is a callable
        if (is_callable($resolved)) {
            $resolved = call_user_func($resolved, $container);
        }

        // if is a string (class concrete) and can be resolved via container
        if (is_string($resolved) && class_exists($resolved) && !enum_exists($resolved)) {
            $resolved = $container->get($resolved);
        }

        if (!is_object($resolved)) {
            throw ContainerException::invalidDefinition($this->id);
        }

        if ($this->shared) {
            $this->instance = $resolved;
        }

        return $resolved;
    }
}
