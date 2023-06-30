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

$container->bind(ServiceInterface::class, MyService::class);

$instance = $container->get(ClassThatUseMyService::class);
```

Register a shared definition (first resolution will be cached, and the same instance will be returned)

```php
$container = new \SergiX44\Container\Container();

$container->singleton(ServiceInterface::class, MyService::class);

$instance = $container->get(ClassThatUseMyService::class);
```

You can define factories as closures:

```php
$container = new \SergiX44\Container\Container();

$value = 'dynamic';

// factory
$container->bind(ServiceInterface::class, function (\Psr\Container\ContainerInterface $container) use ($value) {
    return new MyService($container->get(AnotherService::class), $value);
});

// shared/singleton
$container->singleton(FooServiceInterface::class, function (\Psr\Container\ContainerInterface $container) {
    return new FooService($container->get(ServiceInterface::class));
});

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

It can be also used to inject parameters inside any callable:

```php
// InvokableClass.php
class InvokableClass {
    public function __invoke(ServiceInterface $service)
    {
        //
    }
}
// ClassAndMethod.php
class ClassAndMethod {
    public function method(ServiceInterface $service)
    {
        //
    }
}

// --

$container = new \SergiX44\Container\Container();

$container->bind(ServiceInterface::class, MyService::class);

$result = $container->call(InvokableClass::class); // calls __invoke
$result = $container->call(new InvokableClass()); // calls __invoke
$result = $container->call([ClassAndMethod::class, 'method']); // calls method
$result = $container->call([new ClassAndMethod(), 'method']); // calls method
$result = $container->call(function (ServiceInterface $service) {
    //
});
```

It's also possible to pass arbitrary parameters with an associative array:

```php
// InvokableClass.php
class InvokableClass {
    public function __invoke(ServiceInterface $service, string $a, int $b)
    {
        //
    }
}

$container = new \SergiX44\Container\Container();

// map parameter name => value
$result = $container->call(InvokableClass::class, ['a' => 'foo', 'b' => 123]);

// positional
$result = $container->call(InvokableClass::class, ['foo', 123]);
```

It's also possible to mix positional and associative notation:
```php
// InvokableClass.php
class InvokableClass {
    public function __invoke(ServiceInterface $service, string $a, int $b)
    {
        // same result: b=456 and a=foo
    }
}

$container = new \SergiX44\Container\Container();

$result = $container->call(InvokableClass::class, ['b' => 456, 'foo']);
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