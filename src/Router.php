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

        return $this;
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
            case Dispatcher::NOT_FOUND:
                throw new \Exception;
                break;
            case Dispatcher::METHOD_NOT_ALLOWED:
                throw (new HttpMethodNotAllowed)
                    ->setAllowedMethods($routeInfo[1]);
                break;
            case Dispatcher::FOUND:
                list($_, $handler, $arguments) = $routeInfo;
                break;
        }
        return [$handler, $arguments];
    }

}