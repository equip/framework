# Authentication

[equip/auth](https://github.com/equip/auth) is an optional middleware for implementing authentication that integrates with Equip.

## Authentication Handler

[`AuthHandler`](https://github.com/equip/auth/blob/master/src/AuthHandler.php) is the middleware class that coordinates the authentication process. See Equip documentation for how to [add middleware](/en/latest/#middleware) to an application.

The constructor for [`AuthHandler`](https://github.com/equip/auth/blob/master/src/AuthHandler.php) takes four parameters, which are discussed in the next few sections and should be configured in the [injector](index.md#dependency-injection-container).

### Token Extractor

The middleware checks the [`ServerRequestInterface`](https://github.com/php-fig/http-message/blob/master/src/ServerRequestInterface.php) instance representing the application request for an existing authentication token. To do this, it must know how to extract that token from that request.

This method of extraction is represented by an implementation of [`Token\ExtractorInterface`](https://github.com/equip/auth/blob/master/src/Token/ExtractorInterface.php), which is the first parameter passed to the [`AuthHandler`](https://github.com/equip/auth/blob/master/src/AuthHandler.php) constructor.

These token extractor implementations are bundled with this library:

* [`HeaderExtractor`](https://github.com/equip/auth/blob/master/src/Token/HeaderExtractor.php) extracts the token from a request header. Its constructor takes the header name.
* [`QueryExtractor`](https://github.com/equip/auth/blob/master/src/Token/QueryExtractor.php) extracts the token from a query string parameter taken from the request URI. Its constructor takes the name of the parameter.

The injector can be configured to use a specific extractor implementation like so:

```php
use Equip\Auth\Token\ExtractorInterface;
use Equip\Auth\Token\QueryExtractor;

$injector->alias(
    ExtractInterface::class,
    QueryExtractor::class
);
$injector->define(
    QueryExtractor::class,
    [':parameter' => 'al']
);
```

## Credentials Extractor

If no authentication token is present in the request, the middleware then checks for credentials representing a user to authenticate. As with tokens, it must know how to extract these credentials from the [`ServerRequestInterface`](https://github.com/php-fig/http-message/blob/master/src/ServerRequestInterface.php) instance.

This method of extraction is represented by an implementation of [`Credentials\ExtractorInterface`](https://github.com/equip/auth/blob/master/src/Credentials/ExtractorInterface.php), which is the second parameter passed to the [`AuthHandler`](https://github.com/equip/auth/blob/master/src/AuthHandler.php) constructor.

These credentials extractor implementations are bundled with this library:

* [`BodyExtractor`](https://github.com/equip/auth/blob/master/src/Credentials/BodyExtractor.php) extracts the credentials from top-level properties of the parsed request body. Its constructor takes the names of the properties containing the user identifier and password.

The injector can be configured to use a specific extractor implementation like so:

```php
use Equip\Auth\Credentials\ExtractorInterface;
use Equip\Auth\Credentials\BodyExtractor;

$injector->alias(
    ExtractorInterface::class,
    BodyExtractor::class
);
```

**NOTE**: When using `BodyExtractor` it is expected that any [`*ContentHandler` middleware](/en/latest/#middleware) will be placed *before* `AuthHandler` to ensure that the request body has been parsed before authentication is attempted.

## Adapter

If the middleware does not find either an authentication token nor user credentials in the request, it will handle throwing an instance of [`UnauthorizedException`](https://github.com/equip/auth/blob/master/src/Exception/UnauthorizedException.php). If it does find either one, it must know how to validate them, i.e. verify that the authentication token exists and has not expired or that the credentials represent an existing user.

This method of validation is represented by an implementation of [`AdapterInterface`](https://github.com/equip/auth/blob/master/src/AdapterInterface.php), which is the third parameter passed to the [`AuthHandler`](https://github.com/equip/auth/blob/master/src/AuthHandler.php) constructor.

This library presently contains no bundled implementations. This is due in part to the number of potential implementations based on factors such as varying persistent stores used for tokens and user credentials, password hashing algorithms, etc.

It is possible that implementations will be added for common use cases in the future. Until then, it is recommended that you [create an implementation](#writing-custom-adapters) of this interface specific to your use case.

## Request Filter

The middleware may need to skip authentication altogether depending on the request. A common use case for this is requests with the `OPTIONS` method, which are used for implementing [CORS](https://en.wikipedia.org/wiki/Cross-origin_resource_sharing).

The check for determining whether authentication should happen is represented by an implementation of [`RequestFilterInterface`](https://github.com/equip/auth/blob/master/src/RequestFilterInterface.php), which is the fourth parameter passed to the [`AuthHandler`](https://github.com/equip/auth/blob/master/src/AuthHandler.php) constructor. This parameter is optional; if no value is specified, authentication will happen for all requests.

This library presently contains no bundled implementations. It is possible that implementations will be added for common use cases in the future. Until then, it is recommended that you create an implementation of this interface specific to your use case.

## Writing Custom Adapters

[`AdapterInterface`](https://github.com/equip/auth/blob/master/src/AdapterInterface.php) contains two methods that its implementations must include.

`validateToken()` accepts a string representing an authentication token extracted from the application request. It is the responsibility of the adapter to handle any necessary decoding of token.

`validateCredentials()` accepts an instance of [`Credentials`](https://github.com/equip/auth/blob/master/src/Credentials.php), which contains the user identifier and password extracted from the application request.

[`AuthHandler`](https://github.com/equip/auth/blob/master/src/AuthHandler.php) will call whichever method is appropriate depending on what data to authenticate is included in the application request.

If authentication is successful, the called method must return a populated instance of [`Token`](https://github.com/equip/auth/blob/master/src/Token.php) representing either the existing validated token or a new token corresponding to the existing user.

If the specified token or credentials are invalid, the called method must throw an instance of [`InvalidException`](https://github.com/equip/auth/blob/master/src/Exception/InvalidException.php).

If some other error condition occurs such that authentication cannot be completed successfully, the called method must throw an instance of [`AuthException`](https://github.com/equip/auth/blob/master/src/Exception/AuthException.php).

The injector can be configured to use your adapter implementation like so:

```php
use Equip\Auth\AdapterInterface;
use My\Auth\Adapter;

$injector->alias(
    AdapterInterface::class,
    Adapter::class
);
```

## JSON Web Tokens

If you are using [JWT](https://en.wikipedia.org/wiki/JSON_Web_Token), you of course have the option of using a related library directly in your adapter.

Another option is to use bundled library adapters. There are two related interfaces, [`Jwt\GeneratorInterface`](https://github.com/equip/auth/blob/master/src/Jwt/GeneratorInterface.php) and [`Jwt\ParserInterface`](https://github.com/equip/auth/blob/master/src/Jwt/ParserInterface.php), which handle generating and parsing JWT tokens respectively. You can code your authentication adapter against these library adapter interfaces and then easily swap out implementations.

```php
use Equip\Auth\AdapterInterface as Adapter;
use Equip\Auth\Jwt\GeneratorInterface as Generator;
use Equip\Auth\Jwt\ParserInterface as Parser;

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

        // $parsed is an instance of \Equip\Auth\Token. You can call its
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

### Lcobucci

To use the [lcobucci/jwt](https://packagist.org/packages/lcobucci/jwt) library:

```
composer require "lcobucci/jwt:^3"
```

```php
$injector->define(
    'Equip\\Auth\\Jwt\\Configuration',
    [
        ':key' => '...',
        ':ttl' => 3600, // in seconds, e.g. 1 hour
    ]
);
$injector->alias(
    'Equip\\Auth\\Jwt\\GeneratorInterface',
    'Equip\\Auth\\Jwt\\LcobucciGenerator'
);
$injector->alias(
    'Equip\\Auth\\Jwt\\ParserInterface',
    'Equip\\Auth\\Jwt\\LcobucciParser'
);
$injector->alias(
    'Lcobucci\\JWT\\Signer',
    'Lcobucci\\JWT\\Signer\\Hmac\\Sha256'
);
```

### Firebase

To use the [firebase/php-jwt](https://packagist.org/packages/firebase/php-jwt) library:

```
composer require "firebase/php-jwt:^3"
```

```php
$injector->define(
    'Equip\\Auth\\Jwt\\Configuration',
    [
        ':key' => '...',
        ':ttl' => 3600, // in seconds, e.g. 1 hour
        ':algorithm' => 'HS256',
    ]
);
$injector->alias(
    'Equip\\Auth\\Jwt\\GeneratorInterface',
    'Equip\\Auth\\Jwt\\FirebaseGenerator'
);
$injector->alias(
    'Equip\\Auth\\Jwt\\ParserInterface',
    'Equip\\Auth\\Jwt\\FirebaseParser'
);
```
