<?php

namespace SergiX44\Container\Tests\Fixtures;

class MoreNestedClass
{
    public function __construct(public ResolvableClassWithDefault $r)
    {
    }
}
