# Spark

Welcome! Spark is a tiny and powerful PHP micro-framework. It attempts to comply with [PSR-1](http://www.php-fig.org/psr/psr-1/), [PSR-2](http://www.php-fig.org/psr/psr-2/), [PSR-4](http://www.php-fig.org/psr/psr-4/) and [PSR-7](http://www.php-fig.org/psr/psr-7/). It is based the [ADR](https://github.com/pmjones/adr) pattern.

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

* [`AurynConfiguration`](https://github.com/sparkphp/spark/blob/master/src/Configuration/AurynConfiguration.php) - Use the `Injector` instance as a singleton and to resolve [actions](https://github.com/pmjones/adr#controller-vs-action)
* [`DiactorosConfiguration`](https://github.com/sparkphp/spark/blob/master/src/Configuration/DiactorosConfiguration.php) - Use [Diactoros](https://github.com/zendframework/zend-diactoros/) for the framework [PSR-7](http://www.php-fig.org/psr/psr-7/) implementation
* [`NegotiationConfiguration`](https://github.com/sparkphp/spark/blob/master/src/Configuration/NegotiationConfiguration.php) - Use [Negotiation](https://github.com/willdurand/negotiation) for [content negotiation](https://en.wikipedia.org/wiki/Content_negotiation)
* [`RelayConfiguration`](https://github.com/sparkphp/spark/blob/master/src/Configuration/RelayConfiguration.php) - Use [Relay](http://relayphp.com) for the framework middleware dispatcher

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

1. Create one or more classes implementing [`ConfigurationInterface`](https://github.com/sparkphp/spark/blob/master/src/Configuration/ConfigurationInterface.php) to apply configuration appropriate for your project dependencies.
2. Create a subclass of [`ConfigurationSet`](https://github.com/sparkphp/spark/blob/master/src/Configuration/ConfigurationSet.php) that declares a constructor and passes an array containing the names of these configuration classes to the parent class constructor. Note that this subclass should either include [`DefaultConfigurationSet`](https://github.com/sparkphp/spark/blob/master/src/Configuration/DefaultConfigurationSet.php) or some equivalent configuration for Spark core dependencies.
3. Use the [`ConfigurationSet`](https://github.com/sparkphp/spark/blob/master/src/Configuration/ConfigurationSet.php) subclass in your bootstrap file.

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

// web/index.php
require __DIR__ . '/../vendor/autoload.php';
$injector = new \Auryn\Injector;
$configuration = new \My\ConfigurationSet;
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

    public function prepare(Router $router, Injector $injector)
    {
        $routes = $injector->make(Routes::class);
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

[Responders](https://github.com/pmjones/adr#view-vs-responder) accept the payload returned by the domain and return a [PSR-7 Response object](https://github.com/php-fig/http-message/blob/master/src/ResponseInterface.php). They implement [`ResponderInterface`](https://github.com/sparkphp/adr/blob/master/src/ResponderInterface.php), which like [`DomainInterface`](http://heeris.id.au/2013/this-is-why-you-shouldnt-interrupt-a-programmer://github.com/sparkphp/domain/blob/master/src/DomainInterface.php) declares a single method `__invoke()`. Instead of a third callable parameter, however, it receives an instance of [`PayloadInterface`](https://github.com/sparkphp/domain/blob/master/src/PayloadInterface.php).

Spark provides a few native responder implementations.

### Formatted Responder

[`FormattedResponder`](https://github.com/sparkphp/spark/blob/master/src/Responder/FormattedResponder.php) uses the [Negotiation](https://github.com/willdurand/negotiation) library to support [content negotiation](https://en.wikipedia.org/wiki/Content_negotiation). When a desirable format has been founded, it uses an appropriate implementation of [`AbstractFormatter`](https://github.com/sparkphp/spark/blob/master/src/Formatter/AbstractFormatter.php) to encode the payload data and return it as a string.

Here are the formatter implementations that are natively supported.

* [`JsonFormatter`](https://github.com/sparkphp/spark/blob/master/src/Formatter/JsonFormatter.php) - Encodes the payload as [JSON](http://www.json.org/)
* [`PlatesFormatter`](https://github.com/sparkphp/spark/blob/master/src/Formatter/PlatesFormatter.php) - Applies the payload data to a [Plates](http://platesphp.com/) template specified in the payload and returns the result

### Chained Responder

[`ChainedResponder`](https://github.com/sparkphp/spark/blob/master/src/Responder/ChainedResponder.php) allows multiple responders to be applied to the same response instance. This is intended to allow for [separation of concerns](https://en.wikipedia.org/wiki/Separation_of_concerns) in configuring different areas of the response.

By default, [`ChainedResponder`](https://github.com/sparkphp/spark/blob/master/src/Responder/ChainedResponder.php) only includes [`FormattedResponder`](https://github.com/sparkphp/spark/blob/master/src/Responder/FormattedResponder.php). Responders can be added using its `withAddedResponder()` method or overwritten entirely using its `withResponders()` method.

## Authentication

[sparkphp/auth](https://github.com/sparkphp/auth) is an optional middleware for implementing authentication that integrates with Spark.

### Authentication Handler

[`AuthHandler`](https://github.com/sparkphp/auth/blob/master/src/AuthHandler.php) is the middleware class that coordinates the authentication process. See Spark documentation for how to [add middleware](http://spark.readthedocs.org/en/latest/#middleware) to an application.

The constructor for [`AuthHandler`](https://github.com/sparkphp/auth/blob/master/src/AuthHandler.php) takes four parameters, which are discussed in the next few sections and should be configured in the [injector](#dependency-injection-container).

### Token Extractor

The middleware checks the [`ServerRequestInterface`](https://github.com/php-fig/http-message/blob/master/src/ServerRequestInterface.php) instance representing the application request for an existing authentication token. To do this, it must know how to extract that token from that request.

This method of extraction is represented by an implementation of [`Token\ExtractorInterface`](https://github.com/sparkphp/auth/blob/master/src/Token/ExtractorInterface.php), which is the first parameter passed to the [`AuthHandler`](https://github.com/sparkphp/auth/blob/master/src/AuthHandler.php) constructor.

These token extractor implementations are bundled with this library:

* [`HeaderExtractor`](https://github.com/sparkphp/auth/blob/master/src/Token/HeaderExtractor.php) extracts the token from a request header. Its constructor takes the header name.
* [`QueryExtractor`](https://github.com/sparkphp/auth/blob/master/src/Token/QueryExtractor.php) extracts the token from a query string parameter taken from the request URI. Its constructor takes the name of the parameter.

The injector can be configured to use a specific extractor implementation like so:

```php
use Spark\Auth\Token\ExtractorInterface;
use Spark\Auth\Token\QueryExtractor;

$injector->alias(
    ExtractInterface::class,
    QueryExtractor::class
);
$injector->define(
    QueryExtractor::class,
    [':parameter' => 'al']
);
```

### Credentials Extractor

If no authentication token is present in the request, the middleware then checks for credentials representing a user to authenticate. As with tokens, it must know how to extract these credentials from the [`ServerRequestInterface`](https://github.com/php-fig/http-message/blob/master/src/ServerRequestInterface.php) instance.

This method of extraction is represented by an implementation of [`Credentials\ExtractorInterface`](https://github.com/sparkphp/auth/blob/master/src/Credentials/ExtractorInterface.php), which is the second parameter passed to the [`AuthHandler`](https://github.com/sparkphp/auth/blob/master/src/AuthHandler.php) constructor.

These credentials extractor implementations are bundled with this library:

* [`JsonExtractor`](https://github.com/sparkphp/auth/blob/master/src/Credentials/JsonExtractor.php) extracts the credentials from top-level properties of a JSON request body. Its constructor takes the names of the properties containing the user identifier and password.

The injector can be configured to use a specific extractor implementation like so:

```php
use Spark\Auth\Credentials\ExtractorInterface;
use Spark\Auth\Credentials\JsonExtractor;

$injector->alias(
    ExtractorInterface::class,
    JsonExtractor::class
);
```

### Adapter

If the middleware does not find either an authentication token nor user credentials in the request, it will handle throwing an instance of [`UnauthorizedException`](https://github.com/sparkphp/auth/blob/master/src/Exception/UnauthorizedException.php). If it does find either one, it must know how to validate them, i.e. verify that the authentication token exists and has not expired or that the credentials represent an existing user.

This method of validation is represented by an implementation of [`AdapterInterface`](https://github.com/sparkphp/auth/blob/master/src/AdapterInterface.php), which is the third parameter passed to the [`AuthHandler`](https://github.com/sparkphp/auth/blob/master/src/AuthHandler.php) constructor.

This library presently contains no bundled implementations. This is due in part to the number of potential implementations based on factors such as varying persistent stores used for tokens and user credentials, password hashing algorithms, etc.

It is possible that implementations will be added for common use cases in the future. Until then, it is recommended that you [create an implementation](#writing-custom-adapters) of this interface specific to your use case.

### Request Filter

The middleware made need to skip authentication altogether depending on the request. A common use case for this is requests with the `OPTIONS` method, which are used for implementing [CORS](https://en.wikipedia.org/wiki/Cross-origin_resource_sharing).

The check for determining whether authentication should happen is represented by an implementation of [`RequestFilterInterface`](https://github.com/sparkphp/auth/blob/master/src/RequestFilterInterface.php), which is the fourth parameter passed to the [`AuthHandler`](https://github.com/sparkphp/auth/blob/master/src/AuthHandler.php) constructor. This parameter is optional; if no value is specified, authentication will happen for all requests.

This library presently contains no bundled implementations. It is possible that implementations will be added for common use cases in the future. Until then, it is recommended that you create an implementation of this interface specific to your use case.

### Writing Custom Adapters

[`AdapterInterface`](https://github.com/sparkphp/auth/blob/master/src/AdapterInterface.php) contains two methods that its implementations must include.

`validateToken()` accepts a string representing an authentication token extracted from the application request. It is the responsibility of the adapter to handle any necessary decoding of token.

`validateCredentials()` accepts an instance of [`Credentials`](https://github.com/sparkphp/auth/blob/master/src/Credentials.php), which contains the user identifier and password extracted from the application request.

[`AuthHandler`](https://github.com/sparkphp/auth/blob/master/src/AuthHandler.php) will call whichever method is appropriate depending on what data to authenticate is included in the application request.

If authentication is successful, the called method must return a populated instance of [`Token`](https://github.com/sparkphp/auth/blob/master/src/Token.php) representing either the existing validated token or a new token corresponding to the existing user.

If the specified token or credentials are invalid, the called method must throw an instance of [`InvalidException`](https://github.com/sparkphp/auth/blob/master/src/Exception/InvalidException.php).

If some other error condition occurs such that authentication cannot be completed successfully, the called method must throw an instance of [`AuthException`](https://github.com/sparkphp/auth/blob/master/src/Exception/AuthException.php).

The injector can be configured to use your adapter implementation like so:

```php
use Spark\Auth\AdapterInterface;
use My\Auth\Adapter;

$injector->alias(
    AdapterInterface::class,
    Adapter::class
);
```

### JSON Web Tokens

If you are using [JWT](https://en.wikipedia.org/wiki/JSON_Web_Token), you of course have the option of using a related library directly in your adapter.

Another option is to use bundled library adapters. There are two related interfaces, [`Jwt\GeneratorInterface`](https://github.com/sparkphp/auth/blob/master/src/Jwt/GeneratorInterface.php) and [`Jwt\ParserInterface`](https://github.com/sparkphp/auth/blob/master/src/Jwt/ParserInterface.php), which handle generating and parsing JWT tokens respectively. You can code your authentication adapter against these library adapter interfaces and then easily swap out implementations.

```php
use Spark\Auth\AdapterInterface as Adapter;
use Spark\Auth\Jwt\GeneratorInterface as Generator;
use Spark\Auth\Jwt\ParserInterface as Parser;

class MyAdapter implements Adapter
{
    protected $generator;
    protected $parser;

    public function __construct(Generator $generator, Parser $parser)
    {
        $this->generator = $generator;
        $this->parser = $parser;
    }

    public function validateToken($token)
    {
        $parsed = $this->parser->parse((string) $token);

        // $parsed is an instance of \Spark\Auth\Token. You can call its
        // getMetadata() method here to get all metadata associated with the
        // token, such as a unique identifier for the user, in order to
        // validate the token.

        return $parsed;
    }

    public function validateCredentials(Credentials $credentials)
    {
        // Validate $credentials here, then assign to $claims an array
        // containing the JWT claims to associate with the generated token.

        return $this->generator->getToken($claims);
    }
}
```

See the JWT RFC for a [list of registered claims](https://tools.ietf.org/html/rfc7519#section-4.1).

#### Lcobucci

To use the [lcobucci/jwt](https://packagist.org/packages/lcobucci/jwt) library:

```
composer require "lcobucci/jwt:^3"
```

```php
$injector->define(
    'Spark\\Auth\\Jwt\\Configuration',
    [
        ':key' => '...',
        ':ttl' => 3600, // in seconds, e.g. 1 hour
    ]
);
$injector->alias(
    'Spark\\Auth\\Jwt\\GeneratorInterface',
    'Spark\\Auth\\Jwt\\LcobucciGenerator'
);
$injector->alias(
    'Spark\\Auth\\Jwt\\ParserInterface',
    'Spark\\Auth\\Jwt\\LcobucciParser'
);
$injector->alias(
    'Lcobucci\\JWT\\Signer',
    'Lcobucci\\JWT\\Signer\\Hmac\\Sha256'
);
```

#### Firebase

To use the [firebase/php-jwt](https://packagist.org/packages/firebase/php-jwt) library:

```
composer require "firebase/php-jwt:^3"
```

```php
$injector->define(
    'Spark\\Auth\\Jwt\\Configuration',
    [
        ':key' => '...',
        ':ttl' => 3600, // in seconds, e.g. 1 hour
        ':algorithm' => 'HS256',
    ]
);
$injector->alias(
    'Spark\\Auth\\Jwt\\GeneratorInterface',
    'Spark\\Auth\\Jwt\\FirebaseGenerator'
);
$injector->alias(
    'Spark\\Auth\\Jwt\\ParserInterface',
    'Spark\\Auth\\Jwt\\FirebaseParser'
);
```
