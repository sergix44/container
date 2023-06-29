# XContainer

A simple, fast and PSR-11 compliant service container.

Perfect for libraries that benefit from dependency injection, which integrate with other frameworks, thanks to the
delegation feature.

## 🚀 Installation

```shell
composer require sergix44/container
```

## 🔧 Usage

Register a definition:

```php
$container = new \SergiX44\Container\Container();

$container->register(ServiceInterface::class, MyService::class);

$instance = $container->get(ClassThatUseMyService::class);
```

Register a shared definition (first resolution will be cached, and the same instance will be returned)

```php
$container = new \SergiX44\Container\Container();

$container->register(ServiceInterface::class, MyService::class)->singleton();

$instance = $container->get(ClassThatUseMyService::class);
```

You can define factories as closures:

```php
$container = new \SergiX44\Container\Container();

$value = 'dynamic';

// factory
$container->register(ServiceInterface::class, function (\Psr\Container\ContainerInterface $container) use ($value) {
    return new MyService($container->get(AnotherService::class), $value);
});

// shared/singleton
$container->register(FooServiceInterface::class, function (\Psr\Container\ContainerInterface $container) {
    return new FooService($container->get(ServiceInterface::class));
})->singleton();

$instance = $container->get(ClassThatUseMyService::class);
```

You can set an already resolved instance:

```php
$container = new \SergiX44\Container\Container();

$service = new MyService();

$container->set(ServiceInterface::class, $service);

// or even as string:
// $container->set('service', $service);
// $service = $container->get('service');

$instance = $container->get(ClassThatUseMyService::class);
```

## ⚗️ Testing

To run the test suite:

```shell
vendor/bin/pest
```

## 🏅 Credits

- [Sergio Brighenti](https://github.com/SergiX44)
- [All Contributors](../../contributors)

## 📖 License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.