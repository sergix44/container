<?php

namespace SergiX44\Container\Exception;

use Exception;
use Psr\Container\NotFoundExceptionInterface;
use Throwable;

class NotFoundException extends Exception implements NotFoundExceptionInterface
{
    public static function notResolvable(string $id, Throwable $e): NotFoundException
    {
        return new self("Cannot resolve '$id'", previous: $e);
    }
}