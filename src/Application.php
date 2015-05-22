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
use Spark\Handler\ExceptionHandler;
use Spark\Handler\ResponseHandler;
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
     * @var \callable
     */
    protected $exceptionHandler;

    /**
     * @var \callable
     */
    protected $responseHandler;
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
        Router $router = null
    ) {

        $this->injector = ($injector instanceof Injector) ? $injector : new Injector;
        $this->router = ($router instanceof Router) ? $router : new Router;

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
     * @return callable
     */
    public function getExceptionHandler()
    {
        if (!$this->exceptionHandler) {
            $this->exceptionHandler = new ExceptionHandler;
        }
        return $this->exceptionHandler;
    }

    /**
     * Set the request decorator.
     *
     * @param ResponseHandler $func
     *
     * @return void
     */
    public function setResponseHandler(ResponseHandler $func)
    {
        $this->responseHandler = $func;
    }

    /**
     * @return callable
     */
    public function getResponseHandler()
    {
        if (!$this->responseHandler) {
            $this->responseHandler = new ResponseHandler;
        }
        return $this->responseHandler;
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

            list($handler, $args) = $this->getRouter()->dispatch(
                $request->getMethod(),
                $request->getUri()->getPath()
            );

            foreach ($args as $key => $value) {
                $request = $request->withAttribute($key, $value);
            }

            $this->getInjector()->share($request);

            $response = $this->getInjector()->execute($handler, $args);

            if (!$response instanceof ResponseInterface) {
                $response = call_user_func($this->getResponseHandler(), $response);
            }

        } catch (\Exception $e) {

            if (!$catch) {
                throw $e;
            }

            $response = call_user_func($this->getExceptionHandler(), $e);

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