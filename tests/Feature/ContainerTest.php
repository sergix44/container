<?php

use Psr\Container\ContainerInterface;
use SergiX44\Container\Container;
use SergiX44\Container\Exception\ContainerException;
use SergiX44\Container\Exception\NotFoundException;
use SergiX44\Container\Tests\Fixtures\ResolvableClassWithDefault;
use SergiX44\Container\Tests\Fixtures\SimpleClass;
use SergiX44\Container\Tests\Fixtures\SimpleClassWithConstructor;
use SergiX44\Container\Tests\Fixtures\SimpleInterface;
use SergiX44\Container\Tests\Fixtures\UnresolvableClass;

it('can register a simple definition', function () {
    $container = new Container();

    $container->register(SimpleInterface::class, SimpleClass::class);

    expect($container->has(SimpleInterface::class))->toBeTrue();
});

it('can resolve a simple definition', function () {
    $container = new Container();

    $container->register(SimpleInterface::class, SimpleClass::class);

    expect($container->get(SimpleInterface::class))->toBeInstanceOf(SimpleClass::class);
});

it('can resolve a definition with constructor', function () {
    $container = new Container();

    $container->register(SimpleInterface::class, SimpleClass::class);

    $instance = $container->get(SimpleClassWithConstructor::class);

    expect($instance)
        ->toBeInstanceOf(SimpleClassWithConstructor::class)
        ->and($instance->class)
        ->toBeInstanceOf(SimpleClass::class);
});

it('throws error with unresolvable classes', function () {
    $container = new Container();

    $container->register(SimpleInterface::class, SimpleClass::class);

    $container->get(UnresolvableClass::class);
})->expectException(ContainerException::class);

it('throws error with unregistered definitions', function () {
    $container = new Container();
    $container->get(SimpleInterface::class);
})->expectException(NotFoundException::class);


it('can resolve a definition with constructor default parameters', function () {
    $container = new Container();

    $container->register(SimpleInterface::class, SimpleClass::class);

    $instance = $container->get(ResolvableClassWithDefault::class);

    expect($instance)
        ->toBeInstanceOf(ResolvableClassWithDefault::class)
        ->and($instance->simple)
        ->toBeInstanceOf(SimpleClass::class);
});

it('can resolve a definition with a callable', function () {
    $container = new Container();

    $container->register(SimpleInterface::class, SimpleClass::class);
    $container->register(UnresolvableClass::class, function (ContainerInterface $container) {
        return new UnresolvableClass($container->get(SimpleInterface::class), 12);
    });

    $instance = $container->get(UnresolvableClass::class);

    expect($instance)
        ->toBeInstanceOf(UnresolvableClass::class)
        ->and($instance->simple)
        ->toBeInstanceOf(SimpleClass::class)
        ->and($instance->mandatory)->toBe(12);
});

it('returns the same singleton definition', function () {
    $container = new Container();

    $container
        ->register(SimpleInterface::class, SimpleClass::class)
        ->singleton();

    $one = $container->get(SimpleInterface::class);
    $two = $container->get(SimpleInterface::class);

    expect(spl_object_id($one))->toBe(spl_object_id($two));
});

it('returns different instances by default', function () {
    $container = new Container();

    $container->register(SimpleInterface::class, SimpleClass::class);

    $one = $container->get(SimpleInterface::class);
    $two = $container->get(SimpleInterface::class);

    expect(spl_object_id($one))->not->toBe(spl_object_id($two));
});

it('stores an instance as definition', function () {
    $container = new Container();

    $i = new SimpleClass();
    $container->set(SimpleInterface::class, $i);

    $get = $container->get(SimpleInterface::class);

    expect(spl_object_id($get))->toBe(spl_object_id($i));
});

it('can resolve a simple definition via delegator', function () {
    $di = new \DI\ContainerBuilder();
    $di->addDefinitions([
        SimpleInterface::class => \DI\create(SimpleClass::class),
    ]);
    $diContainer = $di->build();

    expect($diContainer->has(SimpleInterface::class))->toBeTrue();

    $container = new Container();

    expect($container->has(SimpleInterface::class))->toBeFalse();

    $container->delegateTo($diContainer);

    expect($container->has(SimpleInterface::class))->toBeTrue()
        ->and($container->get(SimpleInterface::class))->toBeInstanceOf(SimpleClass::class);
});