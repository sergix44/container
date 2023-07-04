<?php

namespace SergiX44\Container;

use Closure;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionParameter;
use SergiX44\Container\Exception\ContainerException;
use SergiX44\Container\Exception\NotFoundException;
use Throwable;

class Container implements ContainerInterface
{
    /**
     * @var Definition[]
     */
    protected array $definitions = [];

    protected ?ContainerInterface $delegate = null;

    public function __construct()
    {
        $this->set(ContainerInterface::class, $this);
    }

    /**
     * @template T
     *
     * @param  class-string<T>  $id
     * @return T
     *
     * @inheritDoc
     */
    public function get(string $id)
    {
        if (array_key_exists($id, $this->definitions)) {
            return $this->definitions[$id]?->make($this);
        }

        if ($this->delegate !== null && $this->delegate->has($id)) {
            return $this->delegate->get($id);
        }

        try {
            return $this->resolve($id);
        } catch (Throwable $e) {
            throw NotFoundException::notResolvable($id, $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function has(string $id): bool
    {
        // check if is something we match right away
        if (array_key_exists($id, $this->definitions)) {
            return true;
        }

        // check if the delegate can resolve it, if defined
        if ($this->delegate !== null && $this->delegate->has($id)) {
            return true;
        }

        return false;
    }

    public function bind(string $abstract, mixed $resolverOrConcrete): Definition
    {
        return $this->definitions[$abstract] = new Definition($abstract, resolver: $resolverOrConcrete);
    }

    public function singleton(string $abstract, mixed $resolverOrConcrete): Definition
    {
        return $this->bind($abstract, $resolverOrConcrete)->singleton();
    }

    public function set(string $abstract, object $concrete): void
    {
        $this->definitions[$abstract] = new Definition($abstract, instance: $concrete);
    }

    public function delegate(ContainerInterface $container): void
    {
        $this->delegate = $container;
    }

    /**
     * @throws ContainerException
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function call(callable|string|array $callable, array $arguments = []): mixed
    {
        $method = null;
        if (is_array($callable) && count($callable) === 2) {
            // handles array[class-string, method] case
            if (is_string($callable[0]) && class_exists($callable[0])) {
                $callable[0] = $this->get($callable[0]);
            }
            if (method_exists(...$callable)) {
                $method = new ReflectionMethod(...$callable);
            }
        } elseif ($callable instanceof Closure || (is_string($callable) && function_exists($callable))) {
            // handles closures and plain functions
            $method = new ReflectionFunction($callable);
        } elseif (is_callable($callable) || (is_string($callable) && class_exists($callable))) {
            // handles class-string case
            if (is_string($callable) && class_exists($callable)) {
                $callable = $this->get($callable);
            }
            if (method_exists($callable, '__invoke')) {
                $method = new ReflectionMethod($callable, '__invoke');
            }
        }

        if ($method === null) {
            throw ContainerException::invalidCallable();
        }

        $args = $this->getArguments($method->getParameters(), $arguments);

        return call_user_func_array($callable, $args);
    }

    /**
     * @param  string  $class
     * @return object|string|null
     *
     * @throws ContainerException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    protected function resolve(string $class): object|string|null
    {
        $targetClass = new ReflectionClass($class);

        if (
            ($constructor = $targetClass->getConstructor()) === null ||
            ($parameters = $constructor->getParameters()) === []
        ) {
            return $targetClass->newInstance();
        }

        return $targetClass->newInstanceArgs($this->getArguments($parameters));
    }

    /**
     * @param  ReflectionParameter[]  $parameters
     * @return array|null[]|object[]|string[]
     *
     * @throws ContainerException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    protected function getArguments(array $parameters, $additional = []): array
    {
        $positionalArgs = array_filter($additional, 'is_numeric', ARRAY_FILTER_USE_KEY);

        $resolved = [];
        foreach ($parameters as $param) {
            $type = $param->getType()?->getName();

            // variadic parameters can only be the last one
            if ($param->isVariadic()) {
                $resolved = array_merge($resolved, $additional[$param->getName()] ?? $positionalArgs);
                break;
            }

            $resolved[] = match (true) {
                $type !== null && $this->has($type) => $this->get($type), // via definitions
                array_key_exists(
                    $param->getName(),
                    $additional
                ) => $additional[$param->getName()], // defined by the user
                !empty($positionalArgs) => array_shift($positionalArgs),
                $param->isOptional() && $param->isDefaultValueAvailable() => $param->getDefaultValue(), // use default when available
                $type !== null && class_exists($type) && !enum_exists($type) => $this->resolve($type), // via reflection
                default => throw ContainerException::parameterNotResolvable($param),
            };
        }

        return $resolved;
    }
}
