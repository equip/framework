<?php

namespace Spark;

use Auryn\Injector;
use Monolog\Logger;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Spark\Adr\RouteInterface;
use Spark\Handler\ActionHandler;
use Spark\Handler\ExceptionHandler;
use Zend\Diactoros\ServerRequestFactory;

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
     * @var string
     */
    protected $exceptionHandler = 'Spark\Handler\ExceptionHandler';

    /**
     * @var string
     */
    protected $actionHandler = 'Spark\Adr\ActionHandler';

    /**
     * @var string
     */
    protected $responseInterface;

    /**
     * @var array
     */
    protected $config = [];

    /**
     * @var array
     */
    protected $loggers = [];

    /**
     * New Application.
     *
     * @param Injector $injector Application IoC injector
     * @param Router $router Application request routing
     */
    public function __construct(
        Injector $injector = null,
        Router $router = null,
        array $options = []
    ) {

        $options += [
            'ResponseInterface' => '\Zend\Diactoros\Response',
        ];

        $this->injector = ($injector instanceof Injector) ? $injector : new Injector;
        $this->router = ($router instanceof Router) ? $router : new Router;

        $this->responseInterface = $options['ResponseInterface'];

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
     * @param ServerRequestInterface|null $request
     *
     * @return void
     */
    public function run(ServerRequestInterface $request = null)
    {
        ob_start();

        if ($request === null) {
            $request = ServerRequestFactory::fromGlobals();
        }

        $this->getInjector()->alias('\Psr\Http\Message\ServerRequestInterface', get_class($request));
        $this->getInjector()->alias('\Psr\Http\Message\ResponseInterface', $this->responseInterface);

        $response = $this->handle($request);

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
     * @param ServerRequestInterface $request
     * @param int                    $type
     * @param bool                   $catch
     *
     * @throws \Exception
     * @throws \LogicException
     *
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request, $type = 1, $catch = true)
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

            $response = new $this->responseInterface;

            // Inject the route
            $route = $this->injectRoute($route);

            $this->getInjector()->alias('\Psr\Http\Message\RouteInterface', get_class($route));

            $this->getInjector()
                ->share($route)
                ->share($request)
                ->share($response);

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
                throw new \LogicException('Exception decorator did not return an instance of Psr\Http\Message\ResponseInterface');
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
