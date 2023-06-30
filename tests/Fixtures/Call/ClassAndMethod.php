<?php

namespace SergiX44\Container\Tests\Fixtures\Call;

use SergiX44\Container\Tests\Fixtures\Resolve\SimpleInterface;

class ClassAndMethod
{
    public function superMethod(SimpleInterface $simple, int $b, string $a, string $z): array
    {
        return [$simple, $b, $a, $z];
    }
}
