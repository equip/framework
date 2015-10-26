<?php

namespace Spark;

use Auryn\Injector;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Application
{
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
     * Add a large group of routes
     *
     * @param callable $func
     * @return $this
     */
    public function addRoutes(callable $func)
    {
        $func($this->router);
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
            $request = $this->injector->make('Psr\Http\Message\ServerRequestInterface');
        }
        if (!$response) {
            $response = $this->injector->make('Psr\Http\Message\ResponseInterface');
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
        $resolver = $this->injector->make('Spark\Resolver\ResolverInterface');
        $builder = $this->injector->make('Relay', [$resolver]);

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
