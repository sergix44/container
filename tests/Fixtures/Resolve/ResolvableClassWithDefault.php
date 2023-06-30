<?php

namespace SergiX44\Container\Tests\Fixtures\Resolve;

class ResolvableClassWithDefault
{
    public function __construct(public SimpleInterface $simple, public int $mandatory = 1)
    {
    }
}
