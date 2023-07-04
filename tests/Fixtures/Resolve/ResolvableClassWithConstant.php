<?php

namespace SergiX44\Container\Tests\Fixtures\Resolve;

class ResolvableClassWithConstant
{
    private const MY_CONSTANT = 'my_constant';
    public function __construct(public SimpleInterface $simple, public string $c = self::MY_CONSTANT)
    {
    }
}
