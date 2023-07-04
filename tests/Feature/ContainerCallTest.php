<?php

use SergiX44\Container\Container;
use SergiX44\Container\Exception\ContainerException;
use SergiX44\Container\Tests\Fixtures\Call\ClassAndMethod;
use SergiX44\Container\Tests\Fixtures\Call\InvokableClass;
use SergiX44\Container\Tests\Fixtures\Resolve\SimpleClass;
use SergiX44\Container\Tests\Fixtures\Resolve\SimpleInterface;

it('can call a closure without args', function () {
    $container = new Container();

    $f = function () {
        return 'yay';
    };

    $result = $container->call($f);

    expect($result)->toBe('yay');
});

it('can resolve a call a closure with arguments', function () {
    $container = new Container();
    $container->bind(SimpleInterface::class, SimpleClass::class);

    $f = function (SimpleInterface $simple) {
        return $simple;
    };

    $result = $container->call($f);

    expect($result)->toBeInstanceOf(SimpleClass::class);
});

it('can resolve a call a closure with arguments and arbitrary args', function () {
    $container = new Container();
    $container->bind(SimpleInterface::class, SimpleClass::class);

    $f = function (SimpleInterface $simple, int $b) {
        return [$simple, $b];
    };

    $result = $container->call($f, [123]);

    expect($result)->sequence(
        fn ($e) => $e->toBeInstanceOf(SimpleClass::class),
        fn ($e) => $e->toBe(123),
    );
});

it('can resolve a call a closure with arguments and keyvalue args', function () {
    $container = new Container();
    $container->bind(SimpleInterface::class, SimpleClass::class);

    $f = function (SimpleInterface $simple, int $b, string $a, string $z) {
        return [$simple, $b, $a, $z];
    };

    $result = $container->call($f, [
        'a' => 'hi',
        'b' => 123,
        'z' => 'zzzz',
    ]);

    expect($result)->sequence(
        fn ($e) => $e->toBeInstanceOf(SimpleClass::class),
        fn ($e) => $e->toBe(123),
        fn ($e) => $e->toBe('hi'),
        fn ($e) => $e->toBe('zzzz'),
    );
});

it('can resolve a call a closure with mixed positional and keyvalue', function () {
    $container = new Container();
    $container->bind(SimpleInterface::class, SimpleClass::class);

    $f = function (SimpleInterface $simple, int $b, string $a, string $z) {
        return [$simple, $b, $a, $z];
    };

    $result = $container->call($f, [
        'a' => 'hi',
        123,
        'z' => 'zzzz',
    ]);

    expect($result)->sequence(
        fn ($e) => $e->toBeInstanceOf(SimpleClass::class),
        fn ($e) => $e->toBe(123),
        fn ($e) => $e->toBe('hi'),
        fn ($e) => $e->toBe('zzzz'),
    );
});

it('can resolve a call an invokable class with arguments and keyvalue args as class-string', function () {
    $container = new Container();
    $container->bind(SimpleInterface::class, SimpleClass::class);

    $result = $container->call(InvokableClass::class, [
        'a' => 'hi',
        'b' => 123,
        'z' => 'zzzz',
    ]);

    expect($result)->sequence(
        fn ($e) => $e->toBeInstanceOf(SimpleClass::class),
        fn ($e) => $e->toBe(123),
        fn ($e) => $e->toBe('hi'),
        fn ($e) => $e->toBe('zzzz'),
    );
});

it('can resolve a call an invokable class with arguments and keyvalue args as object', function () {
    $container = new Container();
    $container->bind(SimpleInterface::class, SimpleClass::class);
    $o = new InvokableClass();

    $result = $container->call($o, [
        'a' => 'hi',
        'b' => 123,
        'z' => 'zzzz',
    ]);

    expect($result)->sequence(
        fn ($e) => $e->toBeInstanceOf(SimpleClass::class),
        fn ($e) => $e->toBe(123),
        fn ($e) => $e->toBe('hi'),
        fn ($e) => $e->toBe('zzzz'),
    );
});

it('can resolve a call a class method class with arguments and keyvalue args as class-string', function () {
    $container = new Container();
    $container->bind(SimpleInterface::class, SimpleClass::class);

    $result = $container->call([ClassAndMethod::class, 'superMethod'], [
        'a' => 'hi',
        'b' => 123,
        'z' => 'zzzz',
    ]);

    expect($result)->sequence(
        fn ($e) => $e->toBeInstanceOf(SimpleClass::class),
        fn ($e) => $e->toBe(123),
        fn ($e) => $e->toBe('hi'),
        fn ($e) => $e->toBe('zzzz'),
    );
});

it('can resolve a call a class method class with arguments and keyvalue args as object', function () {
    $container = new Container();
    $container->bind(SimpleInterface::class, SimpleClass::class);
    $o = new ClassAndMethod();

    $result = $container->call([$o, 'superMethod'], [
        'a' => 'hi',
        'b' => 123,
        'z' => 'zzzz',
    ]);

    expect($result)->sequence(
        fn ($e) => $e->toBeInstanceOf(SimpleClass::class),
        fn ($e) => $e->toBe(123),
        fn ($e) => $e->toBe('hi'),
        fn ($e) => $e->toBe('zzzz'),
    );
});

it('throws an error with invalid exception', function () {
    $container = new Container();
    $container->bind(SimpleInterface::class, SimpleClass::class);
    $o = new ClassAndMethod();

    $container->call([$o, 'pem'], [
        'a' => 'hi',
        'b' => 123,
        'z' => 'zzzz',
    ]);
})->expectException(ContainerException::class);

it('can resolve a call a closure with arguments and variadic', function () {
    $container = new Container();
    $container->bind(SimpleInterface::class, SimpleClass::class);

    $f = function (SimpleInterface $simple, string ...$z) {
        return [$simple, $z];
    };

    $result = $container->call($f, ['eee', 'aaa', 'zzz']);

    expect($result)->sequence(
        fn ($e) => $e->toBeInstanceOf(SimpleClass::class),
        fn ($e) => $e->toBe(['eee', 'aaa', 'zzz']),
    );
});

it('can resolve a call a closure with arguments and no variadic', function () {
    $container = new Container();
    $container->bind(SimpleInterface::class, SimpleClass::class);

    $f = function (SimpleInterface $simple, string ...$z) {
        return [$simple, $z];
    };

    $result = $container->call($f);

    expect($result)->sequence(
        fn ($e) => $e->toBeInstanceOf(SimpleClass::class),
        fn ($e) => $e->toBe([]),
    );
});
