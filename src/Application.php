<?php

namespace Spark;

use Auryn\Injector;
use Monolog\Logger;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Spark\Adr\RouteInterface;
use Spark\Handler\ActionHandler;
use Spark\Handler\ExceptionHandler;

class Application
{
    /**
     * Application bootstrapping.
     *
     * @param  Injector $di
     * @return Application
     */
    public static function boot(Injector $di = null)
    {
        if (!$di) {
            $di = new Injector;
        }

        $di->share($di);

        // By default, we use the Zend implementation of PSR-7
        $di->alias(
            'Psr\Http\Message\ResponseInterface',
            'Zend\Diactoros\Response'
        );
        $di->delegate(
            'Psr\Http\Message\ServerRequestInterface',
            'Zend\Diactoros\ServerRequestFactory::fromGlobals'
        );

        return $di->make(static::class);
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
     * @var string
     */
    protected $exceptionHandler = 'Spark\Handler\ExceptionHandler';

    /**
     * @var string
     */
    protected $actionHandler = 'Spark\Adr\ActionHandler';

    /**
     * @var array
     */
    protected $config = [];

    /**
     * @var array
     */
    protected $loggers = [];

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
     * Return a logger
     *
     * @param string $name
     * @return \Psr\Log\LoggerInterface
     */
    public function getLogger($name = 'default')
    {
        if (isset($this->loggers[$name])) {
            return $this->loggers[$name];
        }

        $logger = new Logger($name);
        $this->loggers[$name] = $logger;
        return $logger;
    }

    /**
     * Set the exception decorator.
     *
     * @param ExceptionHandler $func
     *
     * @return void
     */
    public function setExceptionHandler(ExceptionHandler $func)
    {
        $this->exceptionHandler = $func;
    }

    /**
     * Get the class for the exception handler
     * @return string
     */
    public function getExceptionHandler()
    {
        return $this->exceptionHandler;
    }

    /**
     * Set the request decorator.
     *
     * @param ActionHandler $func
     *
     * @return $this
     */
    public function setActionHandler($func)
    {
        $this->actionHandler = $func;
        return $this;
    }

    /**
     * @return string
     */
    public function getActionHandler()
    {
        return $this->actionHandler;
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

        $response = $this->handle($request, $response);

        // status
        header(sprintf(
            'HTTP/%s %s %s',
            $response->getProtocolVersion(),
            $response->getStatusCode(),
            $response->getReasonPhrase()
        ), true, $response->getStatusCode());

        // headers
        foreach ($response->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                header("$name: $value", false);
            }
        }

        // content
        echo (string) $response->getBody();

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
        try {

            /**
             * @var \Spark\Adr\RouteInterface $route
             */
            list($route, $_, $args) = $this->getRouter()->dispatch(
                $request->getMethod(),
                $request->getUri()->getPath()
            );

            foreach ($args as $key => $value) {
                $request = $request->withAttribute($key, $value);
            }

            // Inject the route
            $route = $this->injectRoute($route);

            // Load the ActionHandler and execute the request.
            /**
             * @var $handler callable
             */
            $handler = $this->getInjector()->make($this->getActionHandler());
            $response = $handler($request, $response, $route);

        } catch (\Exception $e) {

            if (!$catch) {
                throw $e;
            }

            $response = $this->getInjector()->execute($this->getExceptionHandler(), [':e' => $e]);

            if (!$response instanceof ResponseInterface) {
                throw new \LogicException('Exception handler did not return an instance of Psr\Http\Message\ResponseInterface');
            }

        }

        return $response;
    }

    public function injectRoute(RouteInterface $route)
    {
        $new = clone $route;
        $domain = $route->getDomain();
        $input = $route->getInput() ?: $this->getRouter()->getInput();
        $responder = $route->getResponder() ?: $this->getRouter()->getResponder();

        if (is_string($domain)) {
            $new->setDomain($this->injector->make($domain));
        }
        if (is_string($input)) {
            $new->setInput($this->injector->make($input));
        }
        if (is_string($responder)) {
            $new->setResponder($this->injector->make($responder));
        }
        return $new;
    }



    /**
     * Set a config item
     *
     * @param string $key
     * @param mixed  $value
     */
    public function setConfig($key, $value)
    {
        $this->config[$key] = $value;
    }

    /**
     * Get a config key's value
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getConfig($key, $default = null)
    {
        return isset($this->config[$key]) ? $this->config[$key] : $default;
    }
}
