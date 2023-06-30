<?php

namespace SergiX44\Container\Tests\Fixtures\Call;

use SergiX44\Container\Tests\Fixtures\Resolve\SimpleInterface;

class InvokableClass
{
    public function __invoke(SimpleInterface $simple, int $b, string $a, string $z): array
    {
        return [$simple, $b, $a, $z];
    }
}
