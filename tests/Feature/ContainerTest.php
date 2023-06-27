<?php

use SergiX44\Container\Container;
use SergiX44\Container\Tests\Fixtures\MyClass;
use SergiX44\Container\Tests\Fixtures\MyInterface;

it('can register a simple definition', function () {
    $container = new Container();

    $container->register(MyInterface::class, MyClass::class);

    expect($container->has(MyInterface::class))->toBeTrue();
});

it('can resolve a simple definition', function () {
    $container = new Container();

    $container->register(MyInterface::class, MyClass::class);

    expect($container->get(MyInterface::class))->toBeInstanceOf(MyClass::class);
});