# Equip

Welcome! Equip is a tiny and powerful PHP micro-framework created and maintained by the engineering team at [When I Work](http://wheniwork.com/). It attempts to comply with [PSR-1](http://www.php-fig.org/psr/psr-1/), [PSR-2](http://www.php-fig.org/psr/psr-2/), [PSR-3](http://www.php-fig.org/psr/psr-3/), [PSR-4](http://www.php-fig.org/psr/psr-4/) and [PSR-7](http://www.php-fig.org/psr/psr-7/). It is based on the [ADR](https://github.com/pmjones/adr) pattern.

## Installation

To install Equip, [use Composer](https://getcomposer.org/doc/01-basic-usage.md#installing-dependencies).

```bash
composer require equip/framework
```

You will also need to install a package that provides [`psr/http-message-implementation`](https://packagist.org/providers/psr/http-message-implementation). By default Equip only provides configuration for [`zendframework/zend-diactoros`](https://packagist.org/packages/zendframework/zend-diactoros) but any package that provides a `ServerRequestInterface` implementation should work equally well with proper configuration.

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

The majority of the code written on top of Equip is located in [domain](https://github.com/pmjones/adr#model-vs-domain) classes and others [composed](https://en.wikipedia.org/wiki/Object_composition) by them. Additional classes can be used to customize other aspects of the application including [responders](#responders), [middleware](#middleware), and so forth. One commonality that all of these classes have is that they generally require external dependencies in order to serve their purpose.

In order to handle instantiating and wiring together classes on which your code is dependent, a [dependency injection](https://en.wikipedia.org/wiki/Dependency_injection) container (DIC) is used. In this case of Equip, this DIC is [Auryn](https://github.com/rdlowrey/Auryn), specifically its [`Injector`](https://github.com/rdlowrey/auryn/blob/master/lib/Injector.php) class. Auryn is different from most other PHP DIC implementations in that it uses parameter types and names, rather than separate user-assigned semantic names, to identify individual dependencies.

When a dependency is needed, Equip internally calls the `make()` method of the Auryn [`Injector`](https://github.com/rdlowrey/auryn/blob/master/lib/Injector.php) class. This method uses reflection to inspect types and names of constructor parameters, recursively resolve all dependencies corresponding to those parameters, instantiate the class with those dependencies, and return the configured instance.

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

In Equip, configuration of the injector is encapsulated in classes implementing [`ConfigurationInterface`](https://github.com/equip/framework/blob/master/src/Configuration/ConfigurationInterface.php). This interface has a single method `apply()` that applies some configuration to a given Auryn `Injector` instance. The purpose of this is to allow for clean [separation of concerns](https://en.wikipedia.org/wiki/Separation_of_concerns) and [reusability](https://en.wikipedia.org/wiki/Reusability) of configuration logic.

To facilitate ease of reuse for groupings of configuration, Equip provides a [`ConfigurationSet`](https://github.com/equip/framework/blob/master/src/Configuration/ConfigurationSet.php) class, which takes in a list of configuration classes and applies them to an injector instance.

For a Equip application to function properly, the `Injector` instance it uses will need some configuration. This configuration is defined using the [`Application`](https://github.com/equip/framework/blob/master/src/Application.php) `setConfiguration()` method, which accepts an array of configuration classes or objects to be applied. It is also possible to provide a `ConfigurationSet` when calling `Application::build()` to be used as the default set.

### Default Configuration

The following configurations are typically used by default:

* [`AurynConfiguration`](https://github.com/equip/framework/blob/master/src/Configuration/AurynConfiguration.php) - Use the `Injector` instance as a singleton and to resolve [actions](https://github.com/pmjones/adr#controller-vs-action)
* [`DiactorosConfiguration`](https://github.com/equip/framework/blob/master/src/Configuration/DiactorosConfiguration.php) - Use [Diactoros](https://github.com/zendframework/zend-diactoros/) for the framework [PSR-7](http://www.php-fig.org/psr/psr-7/) implementation
* [`PayloadConfiguration`](https://github.com/equip/framework/blob/master/src/Configuration/PayloadConfiguration.php) - Use the default Equip class as the implementation for [`PayloadInterface`](https://github.com/equip/adr/blob/master/src/PayloadInterface.php)
* [`RelayConfiguration`](https://github.com/equip/framework/blob/master/src/Configuration/RelayConfiguration.php) - Use [Relay](http://relayphp.com) for the framework middleware dispatcher
* [`WhoopsConfiguration`](https://github.com/equip/framework/blob/master/src/Configuration/WhoopsConfiguration.php) - Use [Whoops](http://filp.github.io/whoops/) for handling exceptions

### Optional Configurations

The following configurations are available but not used by default:

* [`EnvConfiguration`](https://github.com/equip/framework/blob/master/src/Configuration/EnvConfiguration.php) - Use [Dotenv](https://github.com/josegonzalez/php-dotenv) to populate the content of [`Env`](https://github.com/equip/framework/blob/master/src/Env.php)
* [`MonologConfiguration`](https://github.com/equip/framework/blob/master/src/Configuration/MonologConfiguration.php) - Use [Monolog](https://github.com/Seldaek/monolog/) for the framework [PSR-3](http://www.php-fig.org/psr/psr-3/) implementation
* [`PlatesConfiguration`](https://github.com/equip/framework/blob/master/src/Configuration/PlatesConfiguration.php) - Configure the [Plates](http://platesphp.com/) template engine
* [`PlatesResponderConfiguration`](https://github.com/equip/framework/blob/master/src/Configuration/PlatesResponderConfiguration.php) - Use [Plates](http://platesphp.com/) as the default [responder](#responders)
* [`RedisConfiguration`](https://github.com/equip/framework/blob/master/src/Configuration/RedisConfiguration.php) - Use [Redis](http://redis.io) for in-memory store

#### Setting The Env File

When using `EnvConfiguration` it may be desirable to define the path to the `.env` file if it is outside of your project root or simply to avoid automatic detection. This can be done by providing a constructed instance of the `EnvConfiguration` during bootstrapping:

```php
Equip\Application::build()
->setConfiguration([
    new Equip\Configuration\EnvConfiguration('path/to/.env'),
    // ...
])
// ...
```

*See the [bootstrap](#bootstrap) section for more details.*

### Env Configuration

Equip comes with a [`Env`](https://github.com/equip/framework/blob/master/src/Env.php) class that can be used as a configuration store. Populating this value object is typically done with an "env loader" such as [`josegonzalez/dotenv`](https://github.com/josegonzalez/dotenv). Once configured this class can be injected into other configuration to use secrets like database passwords or API access tokens.

The `Env` class is immutable and can used as an array:

```php
public function __construct(Env $env)
{
    $this->user = $env['db']['username'];
    $this->pass = $env['db']['password'];
}
```

## Bootstrap

Configure your web server to use `web` or the equivalent as the host document root and to route all non-file requests to the `index.php` file contained there. The [Symfony documentation](http://silex.sensiolabs.org/doc/web_servers.html) has some good examples of how to do this for commonly used web servers.

Here's an example of what this `index.php` file might look like.

```php
require __DIR__ . '/../vendor/autoload.php';

Equip\Application::build()
->setConfiguration([
    Equip\Configuration\AurynConfiguration::class,
    Equip\Configuration\DiactorosConfiguration::class,
    Equip\Configuration\PayloadConfiguration::class,
    Equip\Configuration\RelayConfiguration::class,
    Equip\Configuration\WhoopsConfiguration::class,
])
->setMiddleware([
    Relay\Middleware\ResponseSender::class,
    Equip\Handler\ExceptionHandler::class,
    Equip\Handler\DispatchHandler::class,
    Equip\Handler\JsonContentHandler::class,
    Equip\Handler\FormContentHandler::class,
    Equip\Handler\ActionHandler::class,
])
->setRouting(function (Equip\Directory $directory) {
    return $directory
    ->get(/* ... */)
    // ...
    ;
})
->run();
```

Let's walk through the role of each block.

### Autoloader

Hopefully you're already familiar with Composer and the [autoloader it generates](https://getcomposer.org/doc/01-basic-usage.md#autoloading). This is included first to allow for autoloading of code from Equip and its dependencies as well as code specific to your project.

Composer creates the `vendor` directory, downloads all project dependencies into it, and generates an autoloader at the path `vendor/autoload.php`. Since the bootstrap file is located under the `web` directory, this file must be referenced in relation to that directory when it is included.

### Dependency Injection Container

[Configuration](#configuration) was discussed in an earlier section. In addition to the configurations supported by the Equip core, custom configurations specific to your project can also be applied.

First, create one or more classes implementing [`ConfigurationInterface`](https://github.com/equip/framework/blob/master/src/Configuration/ConfigurationInterface.php) to apply configuration appropriate for your project dependencies.

```php
// src/Configuration/FooConfiguration.php
namespace Acme\Configuration;

use Auryn\Injector;
use Equip\Configuration\ConfigurationInterface;

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

Equip\Application::build()
->setConfiguration([
    // ...
    Acme\Configuration\FooConfiguration::class,
])
// ...
->run();
```

### Routing

Equip uses [FastRoute](https://github.com/nikic/FastRoute) internally for routing. As such, it uses that library's URI pattern syntax; see [its documentation](https://github.com/nikic/FastRoute#defining-routes) for more details.

The directory maps URIs to the corresponding [domain](https://github.com/pmjones/adr#model-vs-domain) that the should be used. This is implemented in the Equip [`Directory`](https://github.com/equip/framework/blob/master/src/Directory.php) class. Here is an example of what configuring an instance of it could look like:

```php
use Acme\Domain;

Equip\Application::build()
// ...
->setRouting(function (Equip\Directory $directory) {
    return $directory
    ->any('/', Domain\Providers::class)
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

It is also possible to provide an [`Action`](https://github.com/equip/framework/blob/master/src/Action.php) object instead of a domain class if you want to modify the responder or input class that will be used to handle the action:

```php
$directory->get('/login', new Equip\Action(
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
use Equip\Directory;

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

Equip\Application::build()
// ...
->setRouting(Acme\Routing::class);
```

### Middleware

[Relay](http://relayphp.com/) is the recommended middleware dispatcher to use with Equip. It [creates instances of middleware classes](http://relayphp.com/#resolvers) and [invokes them](http://relayphp.com/#middleware-logic) in a chain-like fashion. A consequence of this invocation approach is that the order in which middlewares are specified can be important.

For example, in the `setMiddleware()` call shown earlier, the [`ExceptionHandler`](https://github.com/equip/framework/blob/master/src/Handler/ExceptionHandler.php) -- the Equip handler for dealing with exceptions -- is specified fairly early in the class list contained within its constructor. This is to allow exceptions thrown by any subsequent middlewares in the chain to be handled properly.

For a Equip application to handle requests properly it will require some middleware to be defined using the [`Application`](https://github.com/equip/framework/blob/master/src/Application.php) `setMiddleware` method, which accepts an array of middleware classes to be used. It is also possible to provide a `MiddlewareSet` when calling `Application::build()` to be used as the default set.

#### Default Middleware

The following middlewares are typically used by default, in this order:

* [`Relay\Middleware\ResponseSender`](https://github.com/relayphp/Relay.Middleware/blob/master/src/ResponseSender.php) - Outputs data from the [PSR-7 Response object](https://github.com/php-fig/http-message/blob/master/src/ResponseInterface.php) to be sent back to the client
* [`Equip\Handler\ExceptionHandler`](https://github.com/equip/framework/blob/master/src/Handler/ExceptionHandler.php) - Handles exceptions thrown by subsequent middlewares and domains by [logging the exception](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md#13-context) and returning an appropriate application-level response
* [`Equip\Handler\RouteHandler`](https://github.com/equip/framework/blob/master/src/Handler/RouteHandler.php) - Resolves the request route to the corresponding action to execute
* [`Equip\Handler\ContentHandler`](https://github.com/equip/framework/blob/master/src/Handler/ContentHandler.php) - Parses request bodies encoded in common formats and makes the parsed version available via the `getParsedBody()` method of the [PSR-7 Request object](https://github.com/php-fig/http-message/blob/master/src/ServerRequestInterface.php)
* [`Equip\Handler\ActionHandler`](https://github.com/equip/framework/blob/master/src/Handler/ActionHandler.php) - Invokes the [domain](https://github.com/pmjones/adr#model-vs-domain) corresponding to the resolved [action](https://github.com/pmjones/adr#controller-vs-action), applies the [responder](https://github.com/pmjones/adr#view-vs-responder) to the resulting payload, and returns the resulting response

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

Equip\Application::build()
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

[Domain](https://github.com/pmjones/adr#model-vs-domain) classes are the application entry point into your project-specific code. They implement [`DomainInterface`](https://github.com/equip/domain/blob/master/src/DomainInterface.php), which contains a single method `__invoke()` that takes in an array and returns an instance of a class implementing [`PayloadInterface`](https://github.com/equip/domain/blob/master/src/PayloadInterface.php).

The array accepted by `__invoke()` is created internally via the Equip [`Input`](https://github.com/equip/adr/blob/master/src/Input.php) class, which aggregates data from the request in a fashion similar to how PHP itself aggregates request data into the [`$_REQUEST`](http://php.net/manual/en/reserved.variables.request.php) superglobal.

Equip provides a native implementation of [`PayloadInterface`](https://github.com/equip/domain/blob/master/src/PayloadInterface.php) in the form of its [`Payload`](https://github.com/equip/framework/blob/master/src/Payload.php) class. Once the domain class has returned the payload instance, Equip then passes it off to the appropriate [responder](https://github.com/pmjones/adr#view-vs-responder) to be used in constructing the application response.

Rather than having the domain class directly instantiate [`Payload`](https://github.com/equip/framework/blob/master/src/Payload.php) or another implementation of [`PayloadInterface`](https://github.com/equip/domain/blob/master/src/PayloadInterface.php), it's recommended that you make domain classes accept an initial payload instance as a constructor parameter, ideally [typehinted](http://php.net/manual/en/functions.arguments.php#functions.arguments.type-declaration) against [`PayloadInterface`](https://github.com/equip/domain/blob/master/src/PayloadInterface.php). This allows domains to be unit tested independently of any particular payload implementation.

Here's an example of a domain class.

```php
namespace Acme\Domain;

use Equip\Adr\DomainInterface;
use Equip\Adr\PayloadInterface;

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

Note that the constructor of this domain class declares two parameters, a `\PDO` instance and a payload instance. If a request is made for the URI corresponding to this domain class in the [router configuration](#router), Equip will use the [Auryn configuration](#dependency-injection-container) to instantiate the domain class with the dependencies declared in its constructor. Typically, the constructor is used to store references to dependencies in instance properties so as to be able to use them later in `__invoke()`.

Also note that `__invoke()` returns the payload. The core Equip implementation [`Payload`](https://github.com/equip/framework/blob/master/src/Payload.php) provides an immutable implementation of [`PayloadInterface`](https://github.com/equip/adr/blob/master/src/PayloadInterface.php), which also allows for code like this:

```php
return $this->payload
    ->withStatus(Payload::OK)
    ->withOutput(['foo' => 'bar']);
```

## Responders

[Responders](https://github.com/pmjones/adr#view-vs-responder) accept the payload returned by the domain and return a [PSR-7 Response object](https://github.com/php-fig/http-message/blob/master/src/ResponseInterface.php). They implement [`ResponderInterface`](https://github.com/equip/adr/blob/master/src/ResponderInterface.php), which like [`DomainInterface`](https://github.com/equip/domain/blob/master/src/DomainInterface.php) declares a single method `__invoke()`. Instead of a third callable parameter, however, it receives an instance of [`PayloadInterface`](https://github.com/equip/domain/blob/master/src/PayloadInterface.php).

Equip provides a few native responder implementations.

### Chained Responder

[`ChainedResponder`](https://github.com/equip/framework/blob/master/src/Responder/ChainedResponder.php) is the default responder which allows multiple responders to be applied to the same response instance. This is intended to allow for [separation of concerns](https://en.wikipedia.org/wiki/Separation_of_concerns) in configuring different areas of the response. The `ChainedResponder` extends [`Equip\Structure\Set`](https://github.com/equip/structure/blob/master/src/Set.php).

By default [`ChainedResponder`](https://github.com/equip/framework/blob/master/src/Responder/ChainedResponder.php) includes [all the default responders](https://github.com/equip/framework/blob/master/src/Responder). Responders can be added using its `withValue()` method or overwritten entirely using its `withValues()` method.

### Formatted Responder

[`FormattedResponder`](https://github.com/equip/framework/blob/master/src/Responder/FormattedResponder.php) uses the [Negotiation](https://github.com/willdurand/negotiation) library to support [content negotiation](https://en.wikipedia.org/wiki/Content_negotiation). When a desirable format has been founded, it uses an appropriate implementation of [`FormatterInterface`](https://github.com/equip/framework/blob/master/src/Formatter/FormatterInterface.php) to encode the payload data and return it as a string. The `FormattedResponder` extends [`Equip\Structure\Dictionary`](https://github.com/equip/structure/blob/master/src/Dictionary.php).

Here are the formatter implementations that are natively supported:

* [`JsonFormatter`](https://github.com/equip/framework/blob/master/src/Formatter/JsonFormatter.php) - Encodes the payload as [JSON](http://www.json.org/)
* [`PlatesFormatter`](https://github.com/equip/framework/blob/master/src/Formatter/PlatesFormatter.php) - Applies the payload data to a [Plates](http://platesphp.com/) template specified in the payload and returns the result

By default [`FormattedResponder`](https://github.com/equip/framework/blob/master/src/Responder/FormattedResponder.php) includes `JsonFormatter`. Responders can be added using its `withValue()` method or overwritten entirely using its `withValues()` method.

### Redirect

[`RedirectResponder`](https://github.com/equip/framework/blob/master/src/Responder/RedirectResponder.php) allows a redirection to be embedded in the payload. In order to activate it requires a `redirect` message to be attached to the [`PayloadInterface`](https://github.com/equip/adr/blob/master/src/PayloadInterface.php). Optionally the `status` can be set when a [`302 Found`](https://en.wikipedia.org/wiki/HTTP_302) is not the correct response.

Modifying the payload to trigger a redirect is very easy:

```php
// For a basic redirect
return $payload->withMessages([
    'redirect' => '/login',
]);

// For a permanent redirect, also set "status"
return $payload->withMessages([
    'redirect' => '/permalink',
    'status' => 301,
]);
```

### Default Setup

Actions, responders, and formatters work together to generate a response. By default `Action` instances created by [routing](#routing) use [`ChainedResponder`](https://github.com/equip/framework/blob/master/src/Responder/ChainedResponder.php) as the responder. By using a custom action you can change the responder for that action. By default the [`ChainedResponder`](https://github.com/equip/framework/blob/master/src/Responder/ChainedResponder.php) includes the [`FormattedResponder`](https://github.com/equip/framework/blob/master/src/Responder/FormattedResponder.php) which delegates formatting to [`JsonFormatter`](https://github.com/equip/framework/blob/master/src/Formatter/JsonFormatter.php).

### Using Plates

Using [`PlatesFormatter`](https://github.com/equip/framework/blob/master/src/Formatter/PlatesFormatter.php) requires changing the formatters used by [`FormattedResponder`](https://github.com/equip/framework/blob/master/src/Responder/FormattedResponder.php). The easiest way to do this is by using the `PlatesResponderConfiguration` as in the example below:

```php
Equip\Application::build()
->setConfiguration([
    // ...
    Equip\Configuration\PlatesResponderConfiguration::class
])
// ...
```
