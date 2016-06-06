<?php

namespace Equip;

use Auryn\Injector;
use Equip\Configuration\ConfigurationSet;
use Equip\Directory;
use Equip\Dispatching\DispatchingSet;
use Equip\Middleware\MiddlewareSet;
use Relay\Relay;

class Application
{
    /**
     * Create a new application
     *
     * @param Injector $injector
     * @param ConfigurationSet $configuration
     * @param MiddlewareSet $middleware
     * @param DispatchingSet $dispatching
     *
     * @return static
     */
    public static function build(
        Injector $injector = null,
        ConfigurationSet $configuration = null,
        MiddlewareSet $middleware = null,
        DispatchingSet $dispatching = null
    ) {
        return new static($injector, $configuration, $middleware, $dispatching);
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
     * @var DispatchingSet
     */
    private $dispatching;

    /**
     * @param Injector $injector
     * @param ConfigurationSet $configuration
     * @param MiddlewareSet $middleware
     * @param DispatchingSet $dispatching
     */
    public function __construct(
        Injector $injector = null,
        ConfigurationSet $configuration = null,
        MiddlewareSet $middleware = null,
        DispatchingSet $dispatching = null
    ) {
        $this->injector = $injector ?: new Injector;
        $this->configuration = $configuration ?: new ConfigurationSet;
        $this->middleware = $middleware ?: new MiddlewareSet;
        $this->dispatching = $dispatching ?: new DispatchingSet;
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
        $this->configuration = $this->configuration->withValues($configuration);
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
        $this->middleware = $this->middleware->withValues($middleware);
        return $this;
    }

    /**
     * Change dispatching
     *
     * @param array $dispatching
     *
     * @return self
     */
    public function setDispatching(array $dispatching)
    {
        $this->dispatching = $this->dispatching->withValues($dispatching);
        return $this;
    }

    /**
     * Run the application
     *
     * @param string $runner
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function run($runner = Relay::class)
    {
        $this->configuration->apply($this->injector);

        return $this->injector
            ->share($this->middleware)
            ->prepare(Directory::class, $this->dispatching)
            ->execute($runner);
    }
}
