<?php

namespace SergiX44\Container\Tests\Fixtures\Resolve;

class ValueClass
{
    public function __construct(public string $value)
    {
    }

    public function getValue(): string
    {
        return $this->value;
    }
}