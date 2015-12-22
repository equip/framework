<?php

namespace Spark;

use Auryn\Injector;
use Spark\Configuration\ConfigurationSet;
use Spark\Middleware\MiddlewareSet;
use Spark\Router;

class Application
{
    /**
     * Create a new application
     *
     * @param Injector $injector
     * @param ConfigurationSet $configuration
     * @param MiddlewareSet $middleware
     *
     * @return static
     */
    public static function build(
        Injector $injector = null,
        ConfigurationSet $configuration = null,
        MiddlewareSet $middleware = null
    ) {
        return new static($injector, $configuration, $middleware);
    }

    /**
     * @var Injector
     */
    private $injector;

    /**
     * @var ConfigurationSet
     */
    private $configuration;

    /**
     * @var MiddlewareSet
     */
    private $middleware;

    /**
     * @var callable
     */
    private $routing;

    /**
     * @param Injector $injector
     * @param ConfigurationSet $configuration
     * @param MiddlewareSet $middleware
     */
    public function __construct(
        Injector $injector = null,
        ConfigurationSet $configuration = null,
        MiddlewareSet $middleware = null
    ) {
        $this->injector = $injector ?: new Injector;
        $this->configuration = $configuration ?: new ConfigurationSet;
        $this->middleware = $middleware ?: new MiddlewareSet;
    }

    /**
     * Change configuration values
     *
     * @param array $configuration
     *
     * @return self
     */
    public function setConfiguration(array $configuration)
    {
        $this->configuration = $this->configuration->withData($configuration);
        return $this;
    }

    /**
     * Change middleware
     *
     * @param array $middleware
     *
     * @return self
     */
    public function setMiddleware(array $middleware)
    {
        $this->middleware = $this->middleware->withData($middleware);
        return $this;
    }

    /**
     * Change routing
     *
     * @param callable $routing
     *
     * @return self
     */
    public function setRouting(callable $routing)
    {
        $this->routing = $routing;
        return $this;
    }

    /**
     * Run the application
     *
     * @param string $runner
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function run($runner = 'Relay\Relay')
    {
        foreach ($this->configuration as $entry) {
            $this->injector->make($entry)->apply($this->injector);
        }

        return $this->injector
            ->share($this->middleware)
            ->prepare('Spark\Router', $this->routing)
            ->execute($runner);
    }
}
