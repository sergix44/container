<?php

namespace SergiX44\Container\Tests\Fixtures;

class UnresolvableClass
{

    public function __construct(public SimpleInterface $simple, public int $mandatory)
    {
    }

}