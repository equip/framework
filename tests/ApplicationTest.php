<?php
namespace SparkTests;

use Auryn\Injector;
use PHPUnit_Framework_TestCase as TestCase;
use Spark\Application;
use Spark\Configuration\DefaultConfigurationSet;
use Spark\Router;
use SparkTests\Fake\FakeDomain;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequestFactory;
use Zend\Diactoros\Uri;

class ApplicationTest extends TestCase
{
    /**
     * @var Application
     */
    protected $app;

    /**
     * @var Injector
     */
    protected $injector;

    /**
     * @var Router
     */
    protected $router;

    public function setUp()
    {
        $this->injector = new Injector;
        $configuration = new DefaultConfigurationSet;
        $configuration->apply($this->injector);
        $this->router = new Router;
        $this->app = new Application($this->injector, $this->router);
        $this->app->setMiddleware([
            'Relay\Middleware\ResponseSender',
            'Spark\Handler\RouteHandler',
            'Spark\Handler\ActionHandler',
        ]);
    }

    public function testHandleArguments()
    {
        $this->app->setMiddleware([
            'Spark\Handler\RouteHandler',
            'Spark\Handler\ActionHandler',
        ]);

        $request = ServerRequestFactory::fromGlobals()
            ->withUri(new Uri('/testing-is-fun'));
        $response = new Response();

        $this->app->addRoutes(function(Router $router) {
            $router->get('/{arg}', '\SparkTests\Fake\FakeDomain');
        });

        $handledResponse = $this->app->handle($request, $response);
        $this->assertInstanceOf('\Zend\Diactoros\Response', $handledResponse);

        $body = json_decode($handledResponse->getBody());
        $this->assertEquals('testing-is-fun', $body->input->arg);
    }

    /**
     * @expectedException \Spark\Exception\HttpNotFound
     */
    public function testHandleException()
    {
        $request = ServerRequestFactory::fromGlobals();
        $response = new Response();

        $this->app->handle($request, $response, false);
    }

    /**
     * @runInSeparateProcess
     */
    public function testRun()
    {
        $this->app->addRoutes(function(Router $router) {
            $router->get('/', '\SparkTests\Fake\FakeDomain');
        });

        $this->app->run();

        $this->expectOutputString('{"success":true,"input":[]}');
    }

    public function testAddMiddleware()
    {
        $this->assertCount(3, $this->app->getMiddleware());

        $this->app->addMiddleware('Spark\Handler\ExceptionHandler');

        $this->assertCount(4, $this->app->getMiddleware());
        $this->assertEquals('Spark\Handler\ExceptionHandler', $this->app->getMiddleware()[3]);
    }
}
