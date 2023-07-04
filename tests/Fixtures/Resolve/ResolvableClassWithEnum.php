<?php

namespace SergiX44\Container\Tests\Fixtures\Resolve;

class ResolvableClassWithEnum
{
    public function __construct(public SimpleInterface $simple, public MyEnum $enum = MyEnum::HEY)
    {
    }
}
