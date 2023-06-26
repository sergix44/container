<?php

namespace SergiX44\Container\Exception;

use Exception;
use Psr\Container\NotFoundExceptionInterface;
use Throwable;

class NotFoundException extends Exception implements NotFoundExceptionInterface
{
    public function __construct(
        string $message = "Cannot find the specified definition.",
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}