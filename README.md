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

_TODO: more examples_

## ⚗️ Testing

```shell
vendor/bin/pest
```

## 🏅 Credits

- [Sergio Brighenti](https://github.com/SergiX44)
- [All Contributors](../../contributors)

## 📖 License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.