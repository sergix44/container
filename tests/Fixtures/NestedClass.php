<?php

namespace SergiX44\Container\Tests\Fixtures;

class NestedClass
{
    public function __construct(public SimpleInterface $simple, public MoreNestedClass $more)
    {
    }
}