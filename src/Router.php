<?php

namespace Spark;

use Spark\Router\Route;

class Router
{
    const GET     = 'GET';
    const POST    = 'POST';
    const PUT     = 'PUT';
    const PATCH   = 'PATCH';
    const HEAD    = 'HEAD';
    const DELETE  = 'DELETE';
    const OPTIONS = 'OPTIONS';

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
    private $responder = 'Spark\Responder\ChainedResponder';

    /**
     * Set the default input handler spec.
     *
     * @param  string $spec
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
     * @param  string $spec
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
    public function delete($path, $domain)
    {
        return $this->addRoute(self::DELETE, $path, $domain);
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
     * Get routes for the RouteHandler
     * @return array
     */
    public function getRoutes()
    {
        return $this->routes;
    }
}
