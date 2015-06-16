<?php

namespace Spark;

use Auryn\Injector;
use Monolog\Logger;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Relay\Middleware\ResponseSender;
use Relay\Relay;
use Spark\Handler\ExceptionHandler;
use Spark\Handler\RouteHandler;

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

        $loader = $injector->make('josegonzalez\Dotenv\Loader', [':filepaths' => APP_PATH . '.env']);
        $loader->parse();
        $loader->toEnv(true);

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
     * @var array
     */
    protected $middleware = [];

    /**
     * @param Injector $injector
     * @param Router   $router
     * @param ResponseSender $responseSender
     * @param ExceptionHandler $exceptionHandler
     */
    public function __construct(
        Injector $injector,
        Router   $router,
        ResponseSender $responseSender,
        ExceptionHandler $exceptionHandler
    ) {
        $this->injector = $injector;
        $this->router   = $router;

        // Add top level middleware

        $this->middleware = [
            $responseSender,
            $exceptionHandler,
        ];
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
     * Set the exception handler spec.
     *
     * @param  string $spec
     * @return $this
     */
    public function setExceptionHandler($spec)
    {
        $this->exceptionHandler = $spec;
        return $this;
    }

    /**
     * Get the callable name used to handle exceptions.
     *
     * @return string
     */
    public function getExceptionHandler()
    {
        return $this->exceptionHandler;
    }

    /**
     * Set the action handler spec.
     *
     * @param  string $spec
     * @return $this
     */
    public function setActionHandler($spec)
    {
        $this->actionHandler = $spec;
        return $this;
    }

    /**
     * Get the callable name used to handle actions.
     *
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

        $this->middleware[] = new RouteHandler($this->getRouter());

        $this->middleware[] = $this->getInjector()->make('Spark\Handler\ActionHandler');

        $dispatcher = new Relay($this->middleware);

        return $dispatcher($request, $response);

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
     * Set application wide middleware
     *
     * @param array $middleware
     */
    public function setMiddleware(array $middleware)
    {
        $this->middleware = $middleware;
    }

    /**
     * Get the applications middleware
     *
     * @return array
     */
    public function getMiddleware()
    {
        return $this->middleware;
    }
}
