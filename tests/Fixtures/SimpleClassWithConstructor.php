<?php

namespace SergiX44\Container\Tests\Fixtures;

class SimpleClassWithConstructor
{

    public function __construct(public readonly SimpleInterface $class)
    {
    }

}