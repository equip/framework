# Spark

Welcome! Spark is a tiny and powerful PHP micro-framework created and maintained by the engineering team at [When I Work](http://wheniwork.com/). It attempts to comply with [PSR-1](http://www.php-fig.org/psr/psr-1/), [PSR-2](http://www.php-fig.org/psr/psr-2/), [PSR-4](http://www.php-fig.org/psr/psr-4/) and [PSR-7](http://www.php-fig.org/psr/psr-7/). It is based on the [ADR](https://github.com/pmjones/adr) pattern.

## Installation

To install Spark, [use Composer](https://getcomposer.org/doc/01-basic-usage.md#installing-dependencies).

```bash
composer require sparkphp/spark
```

Subsequent examples will assume your project has a directory structure similar to this:

```
.
├── composer.json
├── composer.lock
├── src
├── vendor
└── web
    └── index.php
```

## Dependencies

The majority of the code written on top of Spark is located in [domain](https://github.com/pmjones/adr#model-vs-domain) classes and others [composed](https://en.wikipedia.org/wiki/Object_composition) by them. Additional classes can be used to customize other aspects of the application including [responders](#responders), [middleware](#middleware), and so forth. One commonality that all of these classes have is that they generally require external dependencies in order to serve their purpose.

In order to handle instantiating and wiring together classes on which your code is dependent, a [dependency injection](https://en.wikipedia.org/wiki/Dependency_injection) container (DIC) is used. In this case of Spark, this DIC is [Auryn](https://github.com/rdlowrey/Auryn), specifically its [`Injector`](https://github.com/rdlowrey/auryn/blob/master/lib/Injector.php) class. Auryn is different from most other PHP DIC implementations in that it uses parameter types and names, rather than separate user-assigned semantic names, to identify individual dependencies.

When a dependency is needed, Spark internally calls the `make()` method of the Auryn [`Injector`](https://github.com/rdlowrey/auryn/blob/master/lib/Injector.php) class. This method uses reflection to inspect types and names of constructor parameters, recursively resolve all dependencies corresponding to those parameters, instantiate the class with those dependencies, and return the configured instance.

In order to have Auryn handle passing the dependencies into a class written for your project, such as a domain class, all you need to do is use appropriate [typehints](http://php.net/manual/en/functions.arguments.php#functions.arguments.type-declaration) when declaring its constructor parameters.

Subsequent subsections of this section review other common dependency injection use cases. For additional information, refer to the [Auryn documentation](https://github.com/rdlowrey/Auryn#auryn-).

### Subclasses and Interfaces

Two common use cases with dependencies involve [type aliasing](https://github.com/rdlowrey/Auryn#type-hint-aliasing) using the `alias()` method of the injector:

1. Using an instance of a subclass anywhere a typehint against a superclass is used; or
2. Using an instance of a class implementing an interface anywhere a typehint against that interface is used.

```php
class SuperClassName { }
class SubClassName extends SuperClassName { }
$injector->alias('SuperClassName', 'SubClassName');
$instance = $injector->make('SuperClassName');
echo get_class($instance); // SubClassName

interface InterfaceName { }
class ClassImplementingInterfaceName implements InterfaceName { }
$injector->alias('InterfaceName', 'ClassImplementingInterfaceName');
$instance = $injector->make('InterfaceName');
echo get_class($instance); // ClassImplementingInterfaceName
```

### Scalar Parameters

Type aliasing doesn't apply to scalar parameters. Values for these parameters can be specified using an [injection definition](https://github.com/rdlowrey/Auryn#using-existing-instances-in-injection-definitions) via the `define()` method of the injector:

```php
$injector->define('ClassName', [':scalarParameter' => 'scalarValue']);
```

Note that the parameter name is prefixed with a colon to indicate that the corresponding value should be used as-is. When the colon prefix is excluded, the specified value is assumed to be an inline specification of a class to be aliased to a superclass or interface parameter for that specific dependency.

### Delegation

In some cases, instantiation of a class is complex enough that Auryn can't handle it alone. An example of this is when values passed to the class constructor must be derived from configuration (e.g. programmatically constructing the DSN for a PDO connection from its components) as opposed to taken directly from configuration (e.g. making the entire DSN a configuration setting unto itself).

In cases such as these, instantiation can be [delegated](https://github.com/rdlowrey/Auryn#instantiation-delegates) to [callbacks](http://php.net/manual/en/language.types.callable.php) using the `delegate()` method of the injector, where the callback is expected to return the instance:

```php
$injector->delegate('ClassOrInterfaceName', function() {
    // ...
    return new ClassOrClassImplementingInterfaceName;
});
```

Note that such a callback can be defined to receive typehinted parameters. Just as with constructor parameters, Auryn will [provision instances of object parameters](https://github.com/rdlowrey/auryn#injecting-for-execution) and pass them in when it invokes the callback.

### Preparation

If an instance needs to have logic performed on it after it's created, such as calling a setter method to inject an optional dependency, the injector [`prepare()` method](https://github.com/rdlowrey/auryn#prepares-and-setter-injection) can be used to specify a callback for this. As with delegate callbacks, Auryn will handle provisioning and passing any typehinted objects to this callback.

```php
$injector->prepare('ClassName', function(ClassName $instance) {
    $instance->doThingNotDoneInTheConstructor();
    // ...
});
```

## Configuration

In Spark, configuration of the injector is encapsulated in classes implementing [`ConfigurationInterface`](https://github.com/sparkphp/spark/blob/master/src/Configuration/ConfigurationInterface.php). This interface has a single method `apply()` that applies some configuration to a given Auryn `Injector` instance. The purpose of this is to allow for clean [separation of concerns](https://en.wikipedia.org/wiki/Separation_of_concerns) and [reusability](https://en.wikipedia.org/wiki/Reusability) of configuration logic.

To facilitate ease of reuse for groupings of configuration, Spark provides a [`ConfigurationSet`](https://github.com/sparkphp/spark/blob/master/src/Configuration/ConfigurationSet.php) class, which takes in a list of configuration classes and applies them to an injector instance.

For a Spark application to function properly, the `Injector` instance it uses will need some configuration. This configuration is defined using the [`Application`](https://github.com/sparkphp/spark/blob/master/src/Application.php) `setConfiguration()` method, which accepts an array of configuration classes to be applied. It is also possible to provide a `ConfigurationSet` when calling `Application::build()` to be used as the default set.

### Default Configuration

The following configurations are typically used by default:

* [`AurynConfiguration`](https://github.com/sparkphp/spark/blob/master/src/Configuration/AurynConfiguration.php) - Use the `Injector` instance as a singleton and to resolve [actions](https://github.com/pmjones/adr#controller-vs-action)
* [`DiactorosConfiguration`](https://github.com/sparkphp/spark/blob/master/src/Configuration/DiactorosConfiguration.php) - Use [Diactoros](https://github.com/zendframework/zend-diactoros/) for the framework [PSR-7](http://www.php-fig.org/psr/psr-7/) implementation
* [`NegotiationConfiguration`](https://github.com/sparkphp/spark/blob/master/src/Configuration/NegotiationConfiguration.php) - Use [Negotiation](https://github.com/willdurand/negotiation) for [content negotiation](https://en.wikipedia.org/wiki/Content_negotiation)
* [`PayloadConfiguration`](https://github.com/sparkphp/spark/blob/master/src/Configuration/PayloadConfiguration.php) - Use the default Spark class as the implementation for [`PayloadInterface`](https://github.com/sparkphp/adr/blob/master/src/PayloadInterface.php)
* [`RelayConfiguration`](https://github.com/sparkphp/spark/blob/master/src/Configuration/RelayConfiguration.php) - Use [Relay](http://relayphp.com) for the framework middleware dispatcher

### Optional Configurations

The following configurations are available but not used by default:

* [`PlatesResponderConfiguration`](https://github.com/sparkphp/spark/blob/master/src/Configuration/PlatesResponderConfiguration.php) - Use [Plates](http://platesphp.com/) as the default [responder](#responders)

## Bootstrap

Configure your web server to use `web` or the equivalent as the host document root and to route all non-file requests to the `index.php` file contained there. The [Symfony documentation](http://silex.sensiolabs.org/doc/web_servers.html) has some good examples of how to do this for commonly used web servers.

Here's an example of what this `index.php` file might look like.

```php
require __DIR__ . '/../vendor/autoload.php';

Spark\Application::build()
->setConfiguration([
    Spark\Configuration\AurynConfiguration::class,
    Spark\Configuration\DiactorosConfiguration::class,
    Spark\Configuration\NegotiationConfiguration::class,
    Spark\Configuration\PayloadConfiguration::class,
    Spark\Configuration\RelayConfiguration::class,
])
->setMiddleware([
    Relay\Middleware\ResponseSender::class,
    Spark\Handler\ExceptionHandler::class,
    Spark\Handler\DispatchHandler::class,
    Spark\Handler\JsonContentHandler::class,
    Spark\Handler\FormContentHandler::class,
    Spark\Handler\ActionHandler::class,
])
->setRouting(function (Spark\Directory $directory) {
    return $directory
    ->get(/* ... */)
    // ...
    ;
})
->run();
```

Let's walk through the role of each block.

### Autoloader

Hopefully you're already familiar with Composer and the [autoloader it generates](https://getcomposer.org/doc/01-basic-usage.md#autoloading). This is included first to allow for autoloading of code from Spark and its dependencies as well as code specific to your project.

Composer creates the `vendor` directory, downloads all project dependencies into it, and generates an autoloader at the path `vendor/autoload.php`. Since the bootstrap file is located under the `web` directory, this file must be referenced in relation to that directory when it is included.

### Dependency Injection Container

[Configuration](#configuration) was discussed in an earlier section. In addition to the configurations supported by the Spark core, custom configurations specific to your project can also be applied.

First, create one or more classes implementing [`ConfigurationInterface`](https://github.com/sparkphp/spark/blob/master/src/Configuration/ConfigurationInterface.php) to apply configuration appropriate for your project dependencies.

```php
// src/Configuration/FooConfiguration.php
namespace Acme\Configuration;

use Auryn\Injector;
use Spark\Configuration\ConfigurationInterface;

class FooConfiguration implements ConfigurationInterface
{
    public function apply(Injector $injector)
    {
        // ...
    }
}
```

Now you can apply the configuration you've created in your bootstrap file:

```php
require __DIR__ . '/../vendor/autoload.php';

Spark\Application::build()
->setConfiguration([
    // ...
    Acme\Configuration\FooConfiguration::class,
])
// ...
->run();
```

### Routing

Spark uses [FastRoute](https://github.com/nikic/FastRoute) internally for routing. As such, it uses that library's URI pattern syntax; see [its documentation](https://github.com/nikic/FastRoute#defining-routes) for more details.

The directory maps URIs to the corresponding [domain](https://github.com/pmjones/adr#model-vs-domain) that the should be used. This is implemented in the Spark [`Directory`](https://github.com/sparkphp/spark/blob/master/src/Directory.php) class. Here is an example of what configuring an instance of it could look like:

```php
use Acme\Domain;

Spark\Application::build()
// ...
->setRouting(function (Spark\Directory $directory) {
    return $directory
    ->get('/providers', Domain\GetProviders::class)
    ->get('/providers/{provider}', Domain\GetProvider::class)
    ->post('/providers/{provider}', Domain\SynchronizeProvider::class)
    ->post('/providers/{provider}/connection', Domain\ActivateProvider::class)
    ->delete('/providers/{provider}/connection', Domain\DeactivateProvider::class)
    ->get('/providers/{provider}/configuration', Domain\GetProviderConfiguration::class)
    ->put('/providers/{provider}/configuration', Domain\ChangeProviderConfiguration::class)
    ; // End of routing
})
->run();
```

*It is very important to remember that __the `Directory` object is immutable__! You must __always__ return the directory or changes will be lost.*

It is also possible to provide an [`Action`](https://github.com/sparkphp/spark/blob/master/src/Action.php) object instead of a domain class if you want to modify the responder or input class that will be used to handle the action:

```php
$directory->get('/login', new Spark\Action(
    Domain\Login::class,
    Acme\Responder::class,
    Acme\Input::class
));
```

If an `Action` object is not provided one will be constructed with the provided `Domain` reference instead.


#### Object Routing

An alternative way to implement routing configuration involves using an [invokable](http://php.net/manual/en/language.oop5.magic.php#object.invoke) object. This encapsulation allows for niceties such as non-public methods that can make routing code more concise.

```php
// src/Routing.php
namespace Acme;

use Acme\Domain;
use Spark\Directory;

class Routing
{
    public function __invoke(Directory $directory)
    {
        return $directory
        ->get('/providers', Domain\GetProviders::class)
        ->get('/providers/{provider}', Domain\GetProvider::class)
        ->post('/providers/{provider}', Domain\SynchronizeProvider::class)
        ->post('/providers/{provider}/connection', Domain\ActivateProvider::class)
        ->delete('/providers/{provider}/connection', Domain\DeactivateProvider::class)
        ->get('/providers/{provider}/configuration', Domain\GetProviderConfiguration::class)
        ->put('/providers/{provider}/configuration', Domain\ChangeProviderConfiguration::class)
        ; // End of routing
    }
}
```

Now you can use this class to provide routing:

```php
use Acme\Domain;

Spark\Application::build()
// ...
->setRouting(Acme\Routing::class);
```

### Middleware

[Relay](http://relayphp.com/) is the recommended middleware dispatcher to use with Spark. It [creates instances of middleware classes](http://relayphp.com/#resolvers) and [invokes them](http://relayphp.com/#middleware-logic) in a chain-like fashion. A consequence of this invocation approach is that the order in which middlewares are specified can be important.

For example, in the `setMiddleware()` call shown earlier, the [`ExceptionHandler`](https://github.com/sparkphp/spark/blob/master/src/Handler/ExceptionHandler.php) -- the Spark handler for dealing with exceptions -- is specified fairly early in the class list contained within its constructor. This is to allow exceptions thrown by any subsequent middlewares in the chain to be handled properly.

For a Spark application to handle requests properly it will require some middleware to be defined using the [`Application`](https://github.com/sparkphp/spark/blob/master/src/Application.php) `setMiddleware` method, which accepts an array of middleware classes to be used. It is also possible to provide a `MiddlewareSet` when calling `Application::build()` to be used as the default set.

#### Default Middleware

The following middlewares are typically used by default, in this order:

* [`Relay\Middleware\ResponseSender`](https://github.com/relayphp/Relay.Middleware/blob/master/src/ResponseSender.php) - Outputs data from the [PSR-7 Response object](https://github.com/php-fig/http-message/blob/master/src/ResponseInterface.php) to be sent back to the client
* [`Spark\Handler\ExceptionHandler`](https://github.com/sparkphp/spark/blob/master/src/Handler/ExceptionHandler.php) - Handles exceptions thrown by subsequent middlewares and domains by returning an appropriate application-level response
* [`Spark\Handler\RouteHandler`](https://github.com/sparkphp/spark/blob/master/src/Handler/RouteHandler.php) - Resolves the request route to the corresponding action to execute
* [`Spark\Handler\ContentHandler`](https://github.com/sparkphp/spark/blob/master/src/Handler/ContentHandler.php) - Parses request bodies encoded in common formats and makes the parsed version available via the `getParsedBody()` method of the [PSR-7 Request object](https://github.com/php-fig/http-message/blob/master/src/ServerRequestInterface.php)
* [`Spark\Handler\ActionHandler`](https://github.com/sparkphp/spark/blob/master/src/Handler/ActionHandler.php) - Invokes the [domain](https://github.com/pmjones/adr#model-vs-domain) corresponding to the resolved [action](https://github.com/pmjones/adr#controller-vs-action), applies the [responder](https://github.com/pmjones/adr#view-vs-responder) to the resulting payload, and returns the resulting response

#### Custom Middleware

Custom middleware can also be used to further customize application behavior by creating an invokable class:

```php
namespace Acme;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class FooMiddleware
{
    public function __invoke(
        RequestInterface $request,
        ResponseInterface $response,
        callable $next
    ) {
        // ...
        return $next($request, $response);
    }
}
```

Now you can use the middleware you've created in your bootstrap file:

```php
require __DIR__ . '/../vendor/autoload.php';

Spark\Application::build()
// ...
->setMiddleware([
    // ...
    Acme\Middleware\FooMiddleware::class,
    // ...
])
// ...
->run();
```

*Typically you will want to place your custom middleware immediately before the `ActionHandler`.*

## Domains

[Domain](https://github.com/pmjones/adr#model-vs-domain) classes are the application entry point into your project-specific code. They implement [`DomainInterface`](https://github.com/sparkphp/domain/blob/master/src/DomainInterface.php), which contains a single method `__invoke()` that takes in an array and returns an instance of a class implementing [`PayloadInterface`](https://github.com/sparkphp/domain/blob/master/src/PayloadInterface.php).

The array accepted by `__invoke()` is created internally via the Spark [`Input`](https://github.com/sparkphp/adr/blob/master/src/Input.php) class, which aggregates data from the request in a fashion similar to how PHP itself aggregates request data into the [`$_REQUEST`](http://php.net/manual/en/reserved.variables.request.php) superglobal.

Spark provides a native implementation of [`PayloadInterface`](https://github.com/sparkphp/domain/blob/master/src/PayloadInterface.php) in the form of its [`Payload`](https://github.com/sparkphp/spark/blob/master/src/Payload.php) class. Once the domain class has returned the payload instance, Spark then passes it off to the appropriate [responder](https://github.com/pmjones/adr#view-vs-responder) to be used in constructing the application response.

Rather than having the domain class directly instantiate [`Payload`](https://github.com/sparkphp/spark/blob/master/src/Payload.php) or another implementation of [`PayloadInterface`](https://github.com/sparkphp/domain/blob/master/src/PayloadInterface.php), it's recommended that you make domain classes accept an initial payload instance as a constructor parameter, ideally [typehinted](http://php.net/manual/en/functions.arguments.php#functions.arguments.type-declaration) against [`PayloadInterface`](https://github.com/sparkphp/domain/blob/master/src/PayloadInterface.php). This allows domains to be unit tested independently of any particular payload implementation.

Here's an example of a domain class.

```php
namespace Acme\Domain;

use Spark\Adr\DomainInterface;
use Spark\Adr\PayloadInterface;

class Foo implements DomainInterface
{
    protected $pdo;
    protected $payload;

    public function __construct(
        \PDO $pdo,
        PayloadInterface $payload
    ) {
        $this->pdo = $pdo;
        $this->payload = $payload;
    }

    public function __invoke(array $input)
    {
        // ...
        return $this->payload;
    }
}
```

Note that the constructor of this domain class declares two parameters, a `\PDO` instance and a payload instance. If a request is made for the URI corresponding to this domain class in the [router configuration](#router), Spark will use the [Auryn configuration](#dependency-injection-container) to instantiate the domain class with the dependencies declared in its constructor. Typically, the constructor is used to store references to dependencies in instance properties so as to be able to use them later in `__invoke()`.

Also note that `__invoke()` returns the payload. The core Spark implementation [`Payload`](https://github.com/sparkphp/spark/blob/master/src/Payload.php) provides an immutable implementation of [`PayloadInterface`](https://github.com/sparkphp/adr/blob/master/src/PayloadInterface.php), which also allows for code like this:

```php
return $this->payload
    ->withStatus(Payload::OK)
    ->withOutput(['foo' => 'bar']);
```

## Responders

[Responders](https://github.com/pmjones/adr#view-vs-responder) accept the payload returned by the domain and return a [PSR-7 Response object](https://github.com/php-fig/http-message/blob/master/src/ResponseInterface.php). They implement [`ResponderInterface`](https://github.com/sparkphp/adr/blob/master/src/ResponderInterface.php), which like [`DomainInterface`](https://github.com/sparkphp/domain/blob/master/src/DomainInterface.php) declares a single method `__invoke()`. Instead of a third callable parameter, however, it receives an instance of [`PayloadInterface`](https://github.com/sparkphp/domain/blob/master/src/PayloadInterface.php).

Spark provides a few native responder implementations.

### Formatted Responder

[`FormattedResponder`](https://github.com/sparkphp/spark/blob/master/src/Responder/FormattedResponder.php) uses the [Negotiation](https://github.com/willdurand/negotiation) library to support [content negotiation](https://en.wikipedia.org/wiki/Content_negotiation). When a desirable format has been founded, it uses an appropriate implementation of [`AbstractFormatter`](https://github.com/sparkphp/spark/blob/master/src/Formatter/AbstractFormatter.php) to encode the payload data and return it as a string.

Here are the formatter implementations that are natively supported.

* [`JsonFormatter`](https://github.com/sparkphp/spark/blob/master/src/Formatter/JsonFormatter.php) - Encodes the payload as [JSON](http://www.json.org/)
* [`PlatesFormatter`](https://github.com/sparkphp/spark/blob/master/src/Formatter/PlatesFormatter.php) - Applies the payload data to a [Plates](http://platesphp.com/) template specified in the payload and returns the result

### Chained Responder

[`ChainedResponder`](https://github.com/sparkphp/spark/blob/master/src/Responder/ChainedResponder.php) allows multiple responders to be applied to the same response instance. This is intended to allow for [separation of concerns](https://en.wikipedia.org/wiki/Separation_of_concerns) in configuring different areas of the response.

By default, [`ChainedResponder`](https://github.com/sparkphp/spark/blob/master/src/Responder/ChainedResponder.php) only includes [`FormattedResponder`](https://github.com/sparkphp/spark/blob/master/src/Responder/FormattedResponder.php). Responders can be added using its `withAddedResponder()` method or overwritten entirely using its `withResponders()` method.

### Default Setup

Responders are route-specific. However, the router can be configured with a responder for routes to use by default; this is the recommended practice.

The default configuration looks like this:

* The [`Router`](#router) instance uses [`ChainedResponder`](https://github.com/sparkphp/spark/blob/master/src/Responder/ChainedResponder.php) as its default responder.
* [`ChainedResponder`](https://github.com/sparkphp/spark/blob/master/src/Responder/ChainedResponder.php) composes a single responder by default: [`FormattedResponder`](https://github.com/sparkphp/spark/blob/master/src/Responder/FormattedResponder.php).
* [`FormattedResponder`](https://github.com/sparkphp/spark/blob/master/src/Responder/FormattedResponder.php) configuration includes a single formatter by default: [`JsonFormatter`](https://github.com/sparkphp/spark/blob/master/src/Formatter/JsonFormatter.php).

### Using Plates

Using [`PlatesFormatter`](https://github.com/sparkphp/spark/blob/master/src/Formatter/PlatesFormatter.php) requires changing the formatters used by [`FormattedResponder`](https://github.com/sparkphp/spark/blob/master/src/Responder/FormattedResponder.php). The easiest way to do this is by using the `PlatesResponderConfiguration` as in the example below:

```php
use Spark\Configuration\PlatesResponderConfiguration;

$configuration = new PlatesResponderConfiguration;
$configuration->apply($injector);
```

If you are using the [`DefaultConfigurationSet`](https://github.com/sparkphp/spark/blob/master/src/Configuration/DefaultConfigurationSet.php) you can add this configuration to the default set:

```php
use Spark\Configuration\DefaultConfigurationSet;
use Spark\Configuration\PlatesResponderConfiguration;

$configuration = new DefaultConfigurationSet([
    PlatesResponderConfiguration::class,
]);
$configuration->apply($injector);
```

Note that this will completely replace the default group of formatters of [`FormattedResponder`](https://github.com/sparkphp/spark/blob/master/src/Responder/FormattedResponder.php). If you instead want to add to that group of formatters, you will need to use a custom configuration that modifies the response of `getFormatters()` and then invoke `withFormatters()` method to store the modified array.

### Changing Responders

To use a completely different default responder requires altering the router [configuration](#configuration). Here's an example of a custom router configuration that makes it use [`FormattedResponder`](https://github.com/sparkphp/spark/blob/master/src/Responder/FormattedResponder.php) directly rather than doing so through [`ChainedResponder`](https://github.com/sparkphp/spark/blob/master/src/Responder/ChainedResponder.php) as in the default configuration.

```php
use Auryn\Injector;
use Spark\Configuration\ConfigurationInterface;
use Spark\Responder\FormatterResponder;
use Spark\Router;

class ResponderConfiguration implements ConfigurationInterface
{
    public function apply(Injector $injector)
    {
        $injector->prepare(Router::class, [$this, 'prepareRouter']);
    }

    public function prepareRouter(Router $router)
    {
        $router->setDefaultResponder(FormatterResponder::class);
    }
}
```

### Route-Specific Responders

While using a default responder for all routes is the recommended practice, it is possible to override this and assign a different responder to individual routes. This is done when adding individual routes to the [router](#router): [`Router`](#router) methods for doing so return an instance of [`Route`](https://github.com/sparkphp/spark/blob/master/src/Router/Route.php), which has a `setResponder()` method that accepts the name of a responder class.

Here's an example of changing an individual route to use a specific responder as part of configuring the router.

```php
$router->get('/providers', Domain\GetProviders::class)->setResponder('Acme\Responder');
```
