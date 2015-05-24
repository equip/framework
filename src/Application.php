<?php
/**
 * The Spark Micro-Framework.
 *
 * @author  Daniel Olfelt <dolfelt@gmail.com>
 * @license MIT
 */

namespace Spark;

use Auryn\Injector;
use Monolog\Logger;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Spark\Handler\ActionHandler;
use Spark\Handler\ExceptionHandler;
use Spark\Handler\ResponseHandler;
use Spark\Router\Router;
use Zend\Diactoros\ServerRequestFactory;

/**
 * Proton Application Class.
 */
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
    protected $exceptionHandler;

    /**
     * @var string
     */
    protected $responseHandler;

    /**
     * @var string
     */
    protected $actionHandler;

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


        // Inject the injector for use in the ActionHandler
        $this->injector->share($this->injector);
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
        if (!$this->exceptionHandler) {
            $this->exceptionHandler = new ExceptionHandler;
        }
        return $this->exceptionHandler;
    }

    /**
     * Set the response handler.
     *
     * @param ResponseHandler $func
     *
     * @return $this
     */
    public function setResponseHandler($func)
    {
        $this->responseHandler = $func;
        return $this;
    }

    /**
     * Get the class for the response handler
     * @return string
     */
    public function getResponseHandler()
    {
        if (!$this->responseHandler) {
            $this->responseHandler = 'Spark\Handler\ResponseHandler';
        }
        return $this->responseHandler;
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
        if (!$this->actionHandler) {
            $this->actionHandler = 'Spark\Handler\ActionHandler';
        }
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
                header($name.': '.$value, false, $response->getStatusCode());
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

            $this->getInjector()
                ->share($route)
                ->share($request)
                ->share($response);

            $this->getInjector()->alias('\Psr\Http\Message\RouteInterface', get_class($route));


            // TODO: Perhaps load this through the DI. I'm conflicted because you would need to inject the injector
            // Load the ActionHandler and execute the request.
            /**
             * @var $handler callable
             */
            $handler = $this->getInjector()->make($this->getActionHandler());
            $response = $handler($request, $response, $route);

            if (!$response instanceof ResponseInterface) {
                $response = $this->getInjector()->execute($this->getResponseHandler(), [':content' => $response]);
            }

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