<?php

namespace Spark;

use Auryn\Injector;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use Spark\Exception\HttpMethodNotAllowed;
use Spark\Exception\HttpNotFound;
use Spark\Router\Route;
use Spark\Router\ResolvedRoute;

class Router
{
    const GET     = 'GET';
    const POST    = 'POST';
    const PUT     = 'PUT';
    const PATCH   = 'PATCH';
    const HEAD    = 'HEAD';
    const OPTIONS = 'OPTIONS';

    /**
     * @var Injector
     */
    private $injector;

    /**
     * @var array
     */
    private $routes = [];

    /**
     * @var string
     */
    private $input = 'Spark\Adr\Input';

    /**
     * @var string
     */
    private $responder = 'Spark\Responder\JsonResponder';

    /**
     * @param Injector $injector
     */
    public function __construct(
        Injector $injector
    ) {
        $this->injector = $injector;
    }

    /**
     * Set the default input handler spec.
     *
     * @param  strign $spec
     * @return $this
     */
    public function setDefaultInput($spec)
    {
        $this->input = $spec;
        return $this;
    }

    /**
     * Set the default responder handler spec.
     *
     * @param  strign $spec
     * @return $this
     */
    public function setDefaultResponder($spec)
    {
        $this->responder = $spec;
        return $this;
    }

    /**
     * Create a new Route
     * @param  string $method
     * @param  string $path
     * @param  string $domain
     * @return Route
     */
    private function addRoute($method, $path, $domain)
    {
        return $this->routes["$method $path"] = new Route(
            $domain,
            $this->input,
            $this->responder
        );
    }

    /**
     * @param  string $path
     * @param  string $domain
     * @return Route
     */
    public function get($path, $domain)
    {
        return $this->addRoute(self::GET, $path, $domain);
    }

    /**
     * @param  string $path
     * @param  string $domain
     * @return Route
     */
    public function post($path, $domain)
    {
        return $this->addRoute(self::POST, $path, $domain);
    }

    /**
     * @param  string $path
     * @param  string $domain
     * @return Route
     */
    public function put($path, $domain)
    {
        return $this->addRoute(self::PUT, $path, $domain);
    }

    /**
     * @param  string $path
     * @param  string $domain
     * @return Route
     */
    public function patch($path, $domain)
    {
        return $this->addRoute(self::PATCH, $path, $domain);
    }

    /**
     * @param  string $path
     * @param  string $domain
     * @return Route
     */
    public function head($path, $domain)
    {
        return $this->addRoute(self::HEAD, $path, $domain);
    }

    /**
     * @param  string $path
     * @param  string $domain
     * @return Route
     */
    public function options($path, $domain)
    {
        return $this->addRoute(self::OPTIONS, $path, $domain);
    }

    /**
     * @param  string $method
     * @param  string $path
     * @return array [$route, $arguments]
     * @throws HttpNotFound
     * @throws HttpMethodNotAllowed
     */
    public function dispatch($method, $path)
    {
        $routeInfo = $this->getDispatcher()->dispatch($method, $path);

        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                throw new HttpNotFound;
                break;
            case Dispatcher::METHOD_NOT_ALLOWED:
                throw (new HttpMethodNotAllowed)
                    ->setAllowedMethods($routeInfo[1]);
                break;
            case Dispatcher::FOUND:
                list($_, $route, $arguments) = $routeInfo;
                break;
        }

        $route = $this->getResolvedRoute($route);

        return [$route, $arguments];
    }

    /**
     * @return Dispatcher
     */
    protected function getDispatcher()
    {
        return \FastRoute\simpleDispatcher(function (RouteCollector $collector) {
            foreach ($this->routes as $name => $route) {
                list($method, $path) = explode(' ', $name, 2);
                $collector->addRoute($method, $path, $route);
            }
        });
    }

    /**
     * @param  Route $route
     * @return Spark\Adr\RouteInterface
     */
    protected function getResolvedRoute(Route $route)
    {
        return new ResolvedRoute(
            $this->injector->make($route->getDomain()),
            $this->injector->make($route->getInput()),
            $this->injector->make($route->getResponder())
        );
    }
}
