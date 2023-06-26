<?php

namespace SergiX44\Container;

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
        if ($id === $this->id || is_subclass_of($id, $this->id)) {
            return true;
        }

        return false;
    }

}