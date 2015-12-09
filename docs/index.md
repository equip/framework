# Spark

Welcome! Spark is a tiny and powerful PHP micro-framework created and maintained by the engineering team at [When I Work](http://wheniwork.com/). It attempts to comply with [PSR-1](http://www.php-fig.org/psr/psr-1/), [PSR-2](http://www.php-fig.org/psr/psr-2/), [PSR-4](http://www.php-fig.org/psr/psr-4/) and [PSR-7](http://www.php-fig.org/psr/psr-7/). It is based the [ADR](https://github.com/pmjones/adr) pattern.

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

To faciliate ease of reuse for groupings of configuration, Spark provides a [`ConfigurationSet`](https://github.com/sparkphp/spark/blob/master/src/Configuration/ConfigurationSet.php) class, which takes in a list of configuration classes and applies them to an injector instance.

For a Spark application to function properly, the `Injector` instance it uses must have a minimum level of configuration. This configuration is applied using the [`DefaultConfigurationSet`](https://github.com/sparkphp/spark/blob/master/src/Configuration/DefaultConfigurationSet.php) class, which extends [`ConfigurationSet`](https://github.com/sparkphp/spark/blob/master/src/Configuration/ConfigurationSet.php) to provide a grouping of configurations for several libraries.

### Default Configuration

The following configurations are used by `DefaultConfigurationSet`:

* [`AurynConfiguration`](https://github.com/sparkphp/spark/blob/master/src/Configuration/AurynConfiguration.php) - Use the `Injector` instance as a singleton and to resolve [actions](https://github.com/pmjones/adr#controller-vs-action)
* [`DiactorosConfiguration`](https://github.com/sparkphp/spark/blob/master/src/Configuration/DiactorosConfiguration.php) - Use [Diactoros](https://github.com/zendframework/zend-diactoros/) for the framework [PSR-7](http://www.php-fig.org/psr/psr-7/) implementation
* [`NegotiationConfiguration`](https://github.com/sparkphp/spark/blob/master/src/Configuration/NegotiationConfiguration.php) - Use [Negotiation](https://github.com/willdurand/negotiation) for [content negotiation](https://en.wikipedia.org/wiki/Content_negotiation)
* [`RelayConfiguration`](https://github.com/sparkphp/spark/blob/master/src/Configuration/RelayConfiguration.php) - Use [Relay](http://relayphp.com) for the framework middleware dispatcher

### Optional Configurations

The following configurations are available but not used by default:

* [`PlatesResponderConfiguration`](https://github.com/sparkphp/spark/blob/master/src/Configuration/PlatesResponderConfiguration.php) - Use [Plates](http://platesphp.com/) as the default [responder](#responders)

## Bootstrap

Configure your web server to use `web` or the equivalent as the host document root and to route all non-file requests to the `index.php` file contained there. The [Symfony documentation](http://silex.sensiolabs.org/doc/web_servers.html) has some good examples of how to do this for commonly used web servers.

Here's an example of what this `index.php` file might look like.

```php
// Include Composer autoloader
require __DIR__ . '/../vendor/autoload.php';

// Configure the dependency injection container
$injector = new \Auryn\Injector;
$configuration = new \Spark\Configuration\DefaultConfigurationSet;
$configuration->apply($injector);

// Configure middleware
$injector->alias(
    '\\Spark\\Middleware\\Collection',
    '\\Spark\\Middleware\\DefaultCollection'
);

// Configure the router
$injector->prepare(
    '\\Spark\\Router',
    function(\Spark\Router $router) {
        // ...
    }
);

// Bootstrap the application
$dispatcher = $injector->make('\\Relay\\Relay');
$dispatcher(
    $injector->make('Psr\\Http\\Message\\ServerRequestInterface'),
    $injector->make('Psr\\Http\\Message\\ResponseInterface')
);
```

Let's walk through the role of each block.

### Autoloader

Hopefully you're already familiar with Composer and the [autoloader it generates](https://getcomposer.org/doc/01-basic-usage.md#autoloading). This is included first to allow for autoloading of code from Spark and its dependencies as well as code specific to your project.

Composer creates the `vendor` directory, downloads all project dependencies into it, and generates an autoloader at the path `vendor/autoload.php`. Since the bootstrap file is located under the `web` directory, this file must be referenced in relation to that directory when it is included.

### Dependency Injection Container

[Configuration](#configuration) was discussed in an earlier section. In addition to the configurations supported by the Spark core, custom configurations specific to your project can also be applied.

First, create one or more classes implementing [`ConfigurationInterface`](https://github.com/sparkphp/spark/blob/master/src/Configuration/ConfigurationInterface.php) to apply configuration appropriate for your project dependencies.

```php
// src/FooConfiguration.php
namespace My;

class FooConfiguration implements \Spark\Configuration\ConfigurationInterface
{
    public function apply(\Auryn\Injector $injector)
    {
        // ...
    }
}
```

Next, configurations must be added to a configuration set. There are two potential approaches for this.

One approach is to create a subclass of [`ConfigurationSet`](https://github.com/sparkphp/spark/blob/master/src/Configuration/ConfigurationSet.php) that declares a constructor and passes an array containing the names of these configuration classes to the parent class constructor.

```php
// src/ConfigurationSet.php
namespace My;

class ConfigurationSet extends \Spark\Configuration\ConfigurationSet
{
    public function __construct()
    {
        parent::__construct([
            '\\Spark\\Configuration\\DefaultConfigurationSet',
            '\\My\\FooConfiguration',
            // ...
        ]);
    }
}
```

Note that this subclass must either extend [`DefaultConfigurationSet`](https://github.com/sparkphp/spark/blob/master/src/Configuration/DefaultConfigurationSet.php) or manually include [`DefaultConfigurationSet`](https://github.com/sparkphp/spark/blob/master/src/Configuration/DefaultConfigurationSet.php) in the list of classes passed to the parent constructor. This is to ensure that configuration for Spark core dependencies is included.

Another approach involves creating an instance of [`DefaultConfigurationSet`](https://github.com/sparkphp/spark/blob/master/src/Configuration/DefaultConfigurationSet.php) and passing to its constructor an array containing the names of your configuration classes. These classes will be added to the default list of Spark core dependencies.

```php
$configuration = new \Spark\Configuration\DefaultConfigurationSet([
    '\\My\\FooConfiguration',
    // ...
]);
```

Finally, you must apply the configuration set you've created to the injector in your bootstrap file.

```php
// web/index.php
require __DIR__ . '/../vendor/autoload.php';

// Create the injector
$injector = new \Auryn\Injector;

// If you've created a configuration set subclass, instantiate it
$configuration = new \My\ConfigurationSet;

// If you've created an instance of the default configuration set, use it inline
$configuration = new \Spark\Configuration\DefaultConfigurationSet([
    '\\My\\FooConfiguration',
    // ...
]);

// Regardless of which method you've chosen, apply the chosen configuration set
// to the injector
$configuration->apply($injector);

// ...
```

### Router

The router maps URIs to the corresponding [domain](https://github.com/pmjones/adr#model-vs-domain) that the action should use. This is implemented in the Spark [`Router`](https://github.com/sparkphp/spark/blob/master/src/Router.php) class. Here is an example of what configuring an instance of it could look like.

```php
use MyApp\Domain;

$injector->prepare(
    '\\Spark\\Router',
    function(\Spark\Router $router) {
        $router->get('/providers', Domain\GetProviders::class);
        $router->get('/providers/{provider}', Domain\GetProvider::class);
        $router->post('/providers/{provider}', Domain\SynchronizeProvider::class);
        $router->post('/providers/{provider}/connection', Domain\ActivateProvider::class);
        $router->delete('/providers/{provider}/connection', Domain\DeactivateProvider::class);
        $router->get('/providers/{provider}/configuration', Domain\GetProviderConfiguration::class);
        $router->put('/providers/{provider}/configuration', Domain\ChangeProviderConfiguration::class);
    }
);
```

Spark uses [FastRoute](https://github.com/nikic/FastRoute) internally for routing. As such, it uses that library's URI pattern syntax; see [its documentation](https://github.com/nikic/FastRoute#defining-routes) for more details.

An alternative way to implement route configuration involves using an [invokable](http://php.net/manual/en/language.oop5.magic.php#object.invoke) class and custom [configuration class](#class). This encapsulation allows for niceties such as non-public methods that can make router configuration code more concise.

```php
// src/Router/Routes.php
namespace My\Router;

class Routes
{
    protected $router;

    public function __invoke(\Spark\Router $router)
    {
        $this->router = $router;

        // Compare this:
        $this->get('/providers', 'GetProviders');

        // To this:
        $router->get('/providers', Domain\GetProviders::class);
    }

    protected function get($uri, $domain)
    {
        $this->router->get($uri, 'MyApp\\Domain\\' . $domain);
    }
}

// src/Router/Configuration.php
namespace My\Router;

use Auryn\Injector;
use Spark\Configuration\ConfigurationInterface;
use Spark\Router;

class Configuration implements ConfigurationInterface
{
    public function apply(Injector $injector)
    {
        $injector->prepare(Router::class, [$this, 'prepare']);
    }

    public function prepare(Router $router, Routes $routes)
    {
        $routes($router);
    }
}
```

### Middleware

Middleware applies transformations to requests and responses that are route-independent. This is useful for low-level functionality like exception handling as well as higher-level functionality like authentication.

[Relay](http://relayphp.com/) is the recommended middleware dispatcher to use with Spark. It [creates instances of middleware classes](http://relayphp.com/#resolvers) and [invokes them](http://relayphp.com/#middleware-logic) in a chain-like fashion. A consequence of this invocation approach is that the order in which middlewares are specified can be important.

For example, in [`DefaultConfigurationSet`](https://github.com/sparkphp/spark/blob/master/src/Configuration/DefaultConfigurationSet.php), which is used in the bootstrap file example shown earlier, [`ExceptionHandler`](https://github.com/sparkphp/spark/blob/master/src/Handler/ExceptionHandler.php) -- the Spark handler for dealing with exceptions -- is specified fairly early in the class list contained within its constructor. This is to allow exceptions thrown by any subsequent middlewares in the chain to be handled properly.

Here is a list of middlewares recommended for use in most Spark applications, as contained in [`DefaultConfigurationSet`](https://github.com/sparkphp/spark/blob/master/src/Configuration/DefaultConfigurationSet.php).

* [`Relay\Middleware\ResponseSender`](https://github.com/relayphp/Relay.Middleware/blob/master/src/ResponseSender.php) - Outputs data from the [PSR-7 Response object](https://github.com/php-fig/http-message/blob/master/src/ResponseInterface.php) to be sent back to the client
* [`Spark\Handler\ExceptionHandler`](https://github.com/sparkphp/spark/blob/master/src/Handler/ExceptionHandler.php) - Handles exceptions thrown by subsequent middlewares and domains by returning an appropriate application-level response
* [`Spark\Handler\RouteHandler`](https://github.com/sparkphp/spark/blob/master/src/Handler/RouteHandler.php) - Resolves the request route to the corresponding action to execute
* [`Spark\Handler\ContentHandler`](https://github.com/sparkphp/spark/blob/master/src/Handler/ContentHandler.php) - Parses request bodies encoded in common formats and makes the parsed version available via the `getParsedBody()` method of the [PSR-7 Request object](https://github.com/php-fig/http-message/blob/master/src/ServerRequestInterface.php)
* [`Spark\Handler\ActionHandler`](https://github.com/sparkphp/spark/blob/master/src/Handler/ActionHandler.php) - Invokes the [domain](https://github.com/pmjones/adr#model-vs-domain) corresponding to the resolved [action](https://github.com/pmjones/adr#controller-vs-action), applies the [responder](https://github.com/pmjones/adr#view-vs-responder) to the resulting payload, and returns the resulting response

Custom middleware can also be implemented to further customize application behavior.

1. Create one or more classes implementing [`MiddlewareInterface`](https://github.com/relayphp/Relay.Relay/blob/master/src/MiddlewareInterface.php).
2. Create a subclass of [`Collection`](https://github.com/sparkphp/spark/blob/master/src/Middleware/Collection.php) that declares a constructor and passes an array containing the names of these middleware classes to the parent class constructor. Note that this subclass should either include [`DefaultCollection`](https://github.com/sparkphp/spark/blob/master/src/Middleware/DefaultCollection.php) or some equivalent configuration for Spark core dependencies.
3. Use the [`Collection`](https://github.com/sparkphp/spark/blob/master/src/Middleware/Collection.php) subclass in your bootstrap file.

```php
// src/FooMiddleware.php
namespace My;

class FooMiddleware implements \Relay\MiddlewareInterface
{
    public function __invoke(
        \Psr\Http\Message\RequestInterface,
        \Psr\Http\Message\ResponseInterface,
        callable $next
    ) {
        // ...
    }
}

// src/MiddlewareCollection.php
namespace My;

class MiddlewareCollection extends \Spark\Middleware\Collection
{
    public function __construct(\Spark\Middleware\DefaultCollection $defaults)
    {
        $middlewares = array_merge($defaults->getArrayCopy(), [
            FooMiddleware::class,
            // ...
        ]);
        parent::__construct($middlewares);
    }
}

// web/index.php
// ...
$injector->alias(
    '\\Spark\\Middleware\\Collection',
    '\\My\\MiddlewareCollection'
);
// ...
```

## Domains

[Domain](https://github.com/pmjones/adr#model-vs-domain) classes are the application entry point into your project-specific code. They implement [`DomainInterface`](https://github.com/sparkphp/domain/blob/master/src/DomainInterface.php), which contains a single method `__invoke()` that takes in an array and returns an instance of a class implementing [`PayloadInterface`](https://github.com/sparkphp/domain/blob/master/src/PayloadInterface.php).

The array accepted by `__invoke()` is created internally via the Spark [`Input`](https://github.com/sparkphp/adr/blob/master/src/Input.php) class, which aggregates data from the request in a fashion similar to how PHP itself aggregates request data into the [`$_REQUEST`](http://php.net/manual/en/reserved.variables.request.php) superglobal.

Spark provides a native implementation of [`PayloadInterface`](https://github.com/sparkphp/domain/blob/master/src/PayloadInterface.php) in the form of its [`Payload`](https://github.com/sparkphp/spark/blob/master/src/Payload.php) class. Once the domain class has returned the payload instance, Spark then passes it off to the appropriate [responder](https://github.com/pmjones/adr#view-vs-responder) to be used in constructing the application response.

Rather than having the domain class directly instantiate [`Payload`](https://github.com/sparkphp/spark/blob/master/src/Payload.php) or another implementation of [`PayloadInterface`](https://github.com/sparkphp/domain/blob/master/src/PayloadInterface.php), it's recommended that you make domain classes accept an initial payload instance as a constructor parameter, ideally [typehinted](http://php.net/manual/en/functions.arguments.php#functions.arguments.type-declaration) against [`PayloadInterface`](https://github.com/sparkphp/domain/blob/master/src/PayloadInterface.php). This allows domains to be unit tested independently of any particular payload implementation.

Here's an example of a domain class.

```php
namespace My;

class Foo implements \Spark\Adr\DomainInterface
{
    protected $pdo;
    protected $payload;

    public function __construct(
        \PDO $pdo,
        \Spark\Domain\PayloadInterface $payload
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

Also note that `__invoke()` returns the payload. The core Spark implementation [`Payload`](https://github.com/sparkphp/spark/blob/master/src/Payload.php) provides an immutable implementation of [`PayloadInterface`](https://github.com/sparkphp/domain/blob/master/src/PayloadInterface.php), which also allows for code like this:

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
    PlatesResponderConfiguration::class
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
$router->get('/providers', Domain\GetProviders::class)->setResponder('My\\Responder');
```
