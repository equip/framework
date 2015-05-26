<?php
namespace Spark;

use FastRoute\DataGenerator;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use FastRoute\RouteParser;
use FastRoute\RouteParser\Std as StdRouteParser;
use FastRoute\DataGenerator\GroupCountBased as GroupCountBasedDataGenerator;
use FastRoute\Dispatcher\GroupCountBased as GroupCountBasedDispatcher;
use Spark\Exception\HttpMethodNotAllowed;
use Spark\Exception\HttpNotFound;

class Router extends RouteCollector
{
    const GET     = 'GET';
    const POST    = 'POST';
    const PUT     = 'PUT';
    const PATCH   = 'PATCH';
    const HEAD    = 'HEAD';
    const OPTIONS = 'OPTIONS';

    /**
     * @var array
     */
    protected $routes = [];

    public function __construct(
        RouteParser $routeParser = null,
        DataGenerator $dataGenerator = null
    ) {

        $routeParser   = ($routeParser instanceof RouteParser) ? $routeParser : new StdRouteParser;
        $dataGenerator = ($dataGenerator instanceof DataGenerator) ? $dataGenerator : new GroupCountBasedDataGenerator;

        parent::__construct($routeParser, $dataGenerator);
    }

    public function addRoute($method, $path, $handler)
    {
        parent::addRoute($method, $path, $handler);

        $route = new Route($method, $path, $handler);

        $this->routes[$method.' '.$handler] = $route;

        return $route;
    }

    public function get($path, $handler)
    {
        return $this->addRoute(self::GET, $path, $handler);
    }

    public function post($path, $handler)
    {
        return $this->addRoute(self::POST, $path, $handler);
    }

    public function put($path, $handler)
    {
        return $this->addRoute(self::PUT, $path, $handler);
    }

    public function patch($path, $handler)
    {
        return $this->addRoute(self::PATCH, $path, $handler);
    }

    public function head($path, $handler)
    {
        return $this->addRoute(self::HEAD, $path, $handler);
    }

    public function options($path, $handler)
    {
        return $this->addRoute(self::OPTIONS, $path, $handler);
    }

    public function setInput($input)
    {
        $this->input = $input;
        return $this;
    }

    public function getInput()
    {
        return $this->input;
    }

    public function setResponder($responder)
    {
        $this->responder = $responder;
        return $this;
    }

    public function getResponder()
    {
        return $this->responder;
    }

    /**
     * Get the dispatcher for this Router
     *
     * @return \FastRoute\Dispatcher\GroupCountBased
     */
    protected function createDispatcher()
    {
        return new GroupCountBasedDispatcher($this->getData());
    }

    public function dispatch($method, $path)
    {
        $routeInfo = $this->createDispatcher()->dispatch($method, $path);
        switch ($routeInfo[0]) {
            case Dispatcher::METHOD_NOT_ALLOWED:
                throw (new HttpMethodNotAllowed)->setAllowedMethods($routeInfo[1]);
            case Dispatcher::FOUND:
                list($_, $handler, $arguments) = $routeInfo;
                $route = $this->routes[$method.' '.$handler];
                return [$route, $handler, $arguments];
            case Dispatcher::NOT_FOUND:
            default:
                throw new HttpNotFound;
                break;
        }
    }

}
