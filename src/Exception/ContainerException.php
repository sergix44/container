<?php

namespace SergiX44\Container\Exception;

use Exception;
use Psr\Container\ContainerExceptionInterface;
use ReflectionParameter;

class ContainerException extends Exception implements ContainerExceptionInterface
{
    public static function invalidDefinition(string $id): self
    {
        return new self("Cannot resolve definition '$id'");
    }

    public static function parameterNotResolvable(ReflectionParameter $param): self
    {
        return new self("Cannot resolve parameter '\${$param->getName()}::{$param->getDeclaringClass()?->getName()}'");
    }

    public static function invalidCallable(): self
    {
        return new self('Invalid callable specified');
    }
}
