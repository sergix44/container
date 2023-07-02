<?php

use SergiX44\Container\Container;
use SergiX44\Container\Tests\Fixtures\Resolve\AbstractClass;
use SergiX44\Container\Tests\Fixtures\Resolve\AnotherConcreteClass;
use SergiX44\Container\Tests\Fixtures\Resolve\ConcreteClass;
use SergiX44\Container\Tests\Fixtures\Resolve\SimpleClass;
use SergiX44\Container\Tests\Fixtures\Resolve\SimpleInterface;

it('can resolve a simple definition via delegator', function () {
    $di = new \DI\ContainerBuilder();
    $di->addDefinitions([
        SimpleInterface::class => \DI\create(SimpleClass::class),
    ]);
    $delegator = $di->build();

    expect($delegator->has(SimpleInterface::class))->toBeTrue();

    $container = new Container();

    expect($container->has(SimpleInterface::class))->toBeFalse();

    $container->delegate($delegator);

    expect($container->has(SimpleInterface::class))->toBeTrue()
        ->and($container->get(SimpleInterface::class))->toBeInstanceOf(SimpleClass::class);
});

it('can resolve a simple definition via himself as delegator', function () {
    $delegator = new Container();
    $delegator->bind(SimpleInterface::class, SimpleClass::class);

    expect($delegator->has(SimpleInterface::class))->toBeTrue();

    $container = new Container();

    expect($container->has(SimpleInterface::class))->toBeFalse();

    $container->delegate($delegator);

    expect($container->has(SimpleInterface::class))->toBeTrue()
        ->and($container->get(SimpleInterface::class))->toBeInstanceOf(SimpleClass::class);
});

it('returns his definition instead of the delegator', function () {
    $delegator = new Container();
    $delegator->bind(AbstractClass::class, ConcreteClass::class);

    expect($delegator->has(AbstractClass::class))->toBeTrue();

    $container = new Container();
    $container->bind(AbstractClass::class, AnotherConcreteClass::class);

    expect($container->has(AbstractClass::class))->toBeTrue();

    $container->delegate($delegator);

    expect($container->has(AbstractClass::class))->toBeTrue()
        ->and($container->get(AbstractClass::class))->toBeInstanceOf(AnotherConcreteClass::class);
});
