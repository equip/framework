<?php

namespace Spark;

use Auryn\Injector;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Relay\Relay;

class Application
{
    /**
     * Application bootstrapping.
     *
     * @param  Injector $injector
     * @return Application
     */
    public static function boot(Injector $injector = null)
    {
        if (!$injector) {
            $injector = new Injector;
        }

        $injector->share($injector);

        // By default, we use the Zend implementation of PSR-7
        $injector->alias(
            'Psr\Http\Message\ResponseInterface',
            'Zend\Diactoros\Response'
        );
        $injector->delegate(
            'Psr\Http\Message\ServerRequestInterface',
            'Zend\Diactoros\ServerRequestFactory::fromGlobals'
        );

        // By default, we use Relay (relayphp.com)
        $injector->alias(
            'Relay',
            'Relay\RelayBuilder'
        );

        // By default, we use our internal Resolver
        $injector->alias(
            'Spark\Resolver\ResolverInterface',
            'Spark\Resolver\AurynResolver'
        );

        // By default, we use the standard content negotiator
        $injector->alias(
            'Negotiation\NegotiatorInterface',
            'Negotiation\Negotiator'
        );

        return $injector->make(static::class);
    }

    /**
     * @var Injector
     */
    protected $injector;

    /**
     * @var Router
     */
    protected $router;

    /**
     * @var array
     */
    protected $middleware = [];

    /**
     * @param Injector $injector
     * @param Router   $router
     */
    public function __construct(
        Injector $injector,
        Router   $router
    ) {
        $this->injector = $injector;
        $this->router   = $router;

        $this->injector->share($router);
    }

    /**
     * Get the application injector
     *
     * @return Injector
     */
    public function getInjector()
    {
        return $this->injector;
    }

    /**
     * Gets the resolver for dependency injection
     * @return callable
     */
    public function getResolver()
    {
        return $this->injector->make('Spark\Resolver\ResolverInterface');
    }

    /**
     * Return the router.
     *
     * @return Router
     */
    public function getRouter()
    {
        return $this->router;
    }

    /**
     * Add a large group of routes
     *
     * @param callable $func
     * @return $this
     */
    public function addRoutes(callable $func)
    {
        $func($this->getRouter());
        return $this;
    }

    /**
     * Run the application.
     *
     * @param  ServerRequestInterface $request
     * @param  ResponseInterface      $response
     * @return void
     */
    public function run(
        ServerRequestInterface $request  = null,
        ResponseInterface      $response = null
    ) {
        ob_start();

        if (!$request) {
            $request = $this->getInjector()->make('Psr\Http\Message\ServerRequestInterface');
        }
        if (!$response) {
            $response = $this->getInjector()->make('Psr\Http\Message\ResponseInterface');
        }

        $this->handle($request, $response);

        ob_end_flush();
    }

    /**
     * Handle the request.
     *
     * @param  ServerRequestInterface $request
     * @param  ResponseInterface      $response
     * @param  bool                   $catch
     * @return ResponseInterface
     * @throws \Exception
     * @throws \LogicException
     */
    public function handle(ServerRequestInterface $request, ResponseInterface $response, $catch = true)
    {
        $builder = $this->injector->make('Relay', [$this->getResolver()]);

        $dispatcher = $builder->newInstance($this->getMiddleware());

        return $dispatcher($request, $response);

    }

    /**
     * Add application wide middleware
     *
     * @param array $middleware
     */
    public function addMiddleware($middleware)
    {
        $this->middleware[] = $middleware;
    }

    /**
     * Sets middleware stack for the application.
     *
     * @param array $middleware
     */
    public function setMiddleware(array $middleware)
    {
        $this->middleware = $middleware;
    }


    /**
     * Get the application middleware
     *
     * @return array
     */
    public function getMiddleware()
    {
        return $this->middleware;
    }
}
