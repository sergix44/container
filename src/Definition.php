<?php

namespace SergiX44\Container;

use Psr\Container\ContainerInterface;
use SergiX44\Container\Exception\ContainerException;

class Definition
{
    private bool $shared = false;
    private array $arguments = [];

    public function __construct(
        public readonly string $id,
        private mixed $resolver = null,
        private ?object $instance = null
    ) {
    }

    public function singleton(): self
    {
        $this->shared = true;
        return $this;
    }

    public function argument(string $name, mixed $value): self
    {
        $this->arguments['name'] = $value;
        return $this;
    }

    public function hasInstance(): bool
    {
        return $this->instance !== null;
    }

    public function getInstance(): object
    {
        return $this->instance;
    }

    public function matches(string $id): bool
    {
        return $this->id === $id;
    }

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

        // if is a string (class concrete)  and can be resolved via container
        // lets try
        if (is_string($resolved) && $container->has($resolved)) {
            $resolved = $container->get($resolved);
        }

        if ($resolved === null) {
            throw new ContainerException("Cannot resolve definition with id: $this->id");
        }

        if ($this->shared) {
            $this->instance = $resolved;
        }

        return $resolved;
    }

}