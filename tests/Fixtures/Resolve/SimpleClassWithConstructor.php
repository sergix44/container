<?php

namespace SergiX44\Container\Tests\Fixtures\Resolve;

class SimpleClassWithConstructor
{
    public function __construct(public readonly SimpleInterface $class)
    {
    }
}
