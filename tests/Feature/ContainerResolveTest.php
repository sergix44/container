<?php

use Psr\Container\ContainerInterface;
use SergiX44\Container\Container;
use SergiX44\Container\Exception\ContainerException;
use SergiX44\Container\Exception\NotFoundException;
use SergiX44\Container\Tests\Fixtures\Resolve\AbstractClass;
use SergiX44\Container\Tests\Fixtures\Resolve\ConcreteClass;
use SergiX44\Container\Tests\Fixtures\Resolve\MoreNestedClass;
use SergiX44\Container\Tests\Fixtures\Resolve\NestedClass;
use SergiX44\Container\Tests\Fixtures\Resolve\ResolvableClassWithConstant;
use SergiX44\Container\Tests\Fixtures\Resolve\ResolvableClassWithDefault;
use SergiX44\Container\Tests\Fixtures\Resolve\ResolvableClassWithEnum;
use SergiX44\Container\Tests\Fixtures\Resolve\SimpleClass;
use SergiX44\Container\Tests\Fixtures\Resolve\SimpleClassWithConstructor;
use SergiX44\Container\Tests\Fixtures\Resolve\SimpleInterface;
use SergiX44\Container\Tests\Fixtures\Resolve\UnresolvableClass;

it('can register a simple definition', function () {
    $container = new Container();

    $container->bind(SimpleInterface::class, SimpleClass::class);

    expect($container->has(SimpleInterface::class))->toBeTrue();
});

it('can resolve a simple definition', function () {
    $container = new Container();

    $container->bind(SimpleInterface::class, SimpleClass::class);

    expect($container->get(SimpleInterface::class))->toBeInstanceOf(SimpleClass::class);
});

it('can resolve a simple definition with abstract classes', function () {
    $container = new Container();

    $container->bind(AbstractClass::class, ConcreteClass::class);

    expect($container->get(AbstractClass::class))->toBeInstanceOf(ConcreteClass::class);
});

it('can resolve a nested class definition', function () {
    $container = new Container();

    $container->bind(SimpleInterface::class, SimpleClass::class);

    $instance = $container->get(NestedClass::class);

    expect($instance)->toBeInstanceOf(NestedClass::class)
        ->and($instance->simple)->toBeInstanceOf(SimpleClass::class)
        ->and($instance->more)->toBeInstanceOf(MoreNestedClass::class)
        ->and($instance->more->r)->toBeInstanceOf(ResolvableClassWithDefault::class);
});


it('can resolve a definition with constructor', function () {
    $container = new Container();

    $container->bind(SimpleInterface::class, SimpleClass::class);

    $instance = $container->get(SimpleClassWithConstructor::class);

    expect($instance)
        ->toBeInstanceOf(SimpleClassWithConstructor::class)
        ->and($instance->class)
        ->toBeInstanceOf(SimpleClass::class);
});

it('throws error with unresolvable classes', function () {
    $container = new Container();

    $container->bind(SimpleInterface::class, SimpleClass::class);

    $container->get(UnresolvableClass::class);
})->expectException(NotFoundException::class);

it('throws error with an invalid definition', function () {
    $container = new Container();

    $container->bind(SimpleInterface::class, 'stuff');

    $container->get(SimpleInterface::class);
})->expectException(ContainerException::class);

it('throws error with unregistered definitions', function () {
    $container = new Container();
    $container->get(SimpleInterface::class);
})->expectException(NotFoundException::class);

it('can resolve a definition with constructor default parameters', function () {
    $container = new Container();

    $container->bind(SimpleInterface::class, SimpleClass::class);

    $instance = $container->get(ResolvableClassWithDefault::class);

    expect($instance)
        ->toBeInstanceOf(ResolvableClassWithDefault::class)
        ->and($instance->simple)
        ->toBeInstanceOf(SimpleClass::class);
});

it('can resolve a definition with constructor default parameters as enum', function () {
    $container = new Container();

    $container->bind(SimpleInterface::class, SimpleClass::class);

    $instance = $container->get(ResolvableClassWithEnum::class);

    expect($instance)
        ->toBeInstanceOf(ResolvableClassWithEnum::class)
        ->and($instance->simple)
        ->toBeInstanceOf(SimpleClass::class);
});

it('can resolve a definition with constructor default parameters as const', function () {
    $container = new Container();

    $container->bind(SimpleInterface::class, SimpleClass::class);

    $instance = $container->get(ResolvableClassWithConstant::class);

    expect($instance)
        ->toBeInstanceOf(ResolvableClassWithConstant::class)
        ->and($instance->simple)
        ->toBeInstanceOf(SimpleClass::class);
});

it('can resolve a definition with a callable', function () {
    $container = new Container();

    $container->bind(SimpleInterface::class, SimpleClass::class);
    $container->bind(UnresolvableClass::class, function (ContainerInterface $container) {
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

    $container->singleton(SimpleInterface::class, SimpleClass::class);

    $one = $container->get(SimpleInterface::class);
    $two = $container->get(SimpleInterface::class);

    expect(spl_object_id($one))->toBe(spl_object_id($two));
});

it('returns different instances by default', function () {
    $container = new Container();

    $container->bind(SimpleInterface::class, SimpleClass::class);

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

it('support abstract as string', function () {
    $container = new Container();

    $container->bind('simple', SimpleClass::class);

    expect($container->get('simple'))->toBeInstanceOf(SimpleClass::class);
});

it('support set as string', function () {
    $container = new Container();

    $i = new stdClass();

    $container->set('simple', $i);

    expect($container->get('simple'))->toBe($i);
});
