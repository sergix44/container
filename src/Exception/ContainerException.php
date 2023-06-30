<?php

namespace SergiX44\Container\Exception;

use Exception;
use Psr\Container\ContainerExceptionInterface;
use ReflectionParameter;

class ContainerException extends Exception implements ContainerExceptionInterface
{

    public static function invalidDefinition(string $id): ContainerException
    {
        return new self("Cannot resolve definition '$id'");
    }

    public static function parameterNotResolvable(ReflectionParameter $param): ContainerException
    {
        return new self("Cannot resolve constructor parameter '\${$param->getName()}::{$param->getDeclaringClass()?->getName()}'");
    }

    public static function invalidCallable(): ContainerException
    {
        return new self("Invalid callable specified");
    }
}