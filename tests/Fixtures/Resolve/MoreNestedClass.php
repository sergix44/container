<?php

namespace SergiX44\Container\Tests\Fixtures\Resolve;

class MoreNestedClass
{
    public function __construct(public ResolvableClassWithDefault $r)
    {
    }
}
