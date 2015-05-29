<?php
namespace SparkTests;

use Auryn\Injector;
use PHPUnit_Framework_TestCase as TestCase;
use Spark\Application;
use Spark\Router;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequestFactory;

class ApplicationTest extends TestCase
{
    protected $app;

    public function setUp()
    {
        $this->app = Application::boot();
    }

    public function testBoot()
    {
        $this->assertTrue($this->app instanceof Application);
        $this->assertTrue($this->app->getInjector() instanceof Injector);
        $this->assertTrue($this->app->getRouter() instanceof Router);

    }

    public function testExceptionHandler()
    {
        $errorHandler = $this->app->getExceptionHandler();
        $handler = new $errorHandler;
        $this->assertInstanceOf('\Spark\Handler\ExceptionHandler', $handler);

        $response = new Response();
        $response = $handler($response, new \Exception);
        $this->assertInstanceOf('\Psr\Http\Message\ResponseInterface', $response);

    }

    public function testGetSetHandlers()
    {
        $exceptionHandler = '\SparkTests\Fake\FakeExceptionHandler';
        $this->app->setExceptionHandler($exceptionHandler);
        $this->assertEquals($exceptionHandler, $this->app->getExceptionHandler());

        $actionHandler = '\SparkTests\Fake\FakeActionHandler';
        $this->app->setActionHandler($actionHandler);
        $this->assertEquals($actionHandler, $this->app->getActionHandler());

    }

    public function testLogger()
    {
        $this->assertInstanceOf('\Monolog\Logger', $this->app->getLogger());
    }

    public function testConfig()
    {
        $this->app->setConfig("test", true);

        $this->assertTrue($this->app->getConfig("test"));
        $this->assertEquals("default", $this->app->getConfig("undefined", "default"));
        $this->assertNotTrue($this->app->getConfig("not_here"));
    }

    public function testHandle()
    {

        $request = ServerRequestFactory::fromGlobals();
        $response = new Response();

        $handledResponse = $this->app->handle($request, $response);

        $this->assertInstanceOf('\Zend\Diactoros\Response', $handledResponse);
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

        $this->expectOutputString('{"success":true}');
    }


}