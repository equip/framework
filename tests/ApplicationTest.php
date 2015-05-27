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

    public function testBoot()
    {
        $app = Application::boot();

        $this->assertTrue($app instanceof Application);
        $this->assertTrue($app->getInjector() instanceof Injector);
        $this->assertTrue($app->getRouter() instanceof Router);

    }

    public function testExceptionHandler()
    {
        $app = Application::boot();

        $errorHandler = $app->getExceptionHandler();
        $handler = new $errorHandler;
        $this->assertInstanceOf('\Spark\Handler\ExceptionHandler', $handler);

        $response = new Response();
        $response = $handler($response, new \Exception);
        $this->assertInstanceOf('\Psr\Http\Message\ResponseInterface', $response);

    }

    public function testGetSetHandlers()
    {
        $app = Application::boot();

        $exceptionHandler = '\SparkTests\Fake\FakeExceptionHandler';
        $app->setExceptionHandler($exceptionHandler);
        $this->assertEquals($exceptionHandler, $app->getExceptionHandler());

        $actionHandler = '\SparkTests\Fake\FakeActionHandler';
        $app->setActionHandler($actionHandler);
        $this->assertEquals($actionHandler, $app->getActionHandler());

    }

    public function testLogger()
    {
        $app = Application::boot();

        $this->assertInstanceOf('\Monolog\Logger', $app->getLogger());

    }

    public function testConfig()
    {
        $app = Application::boot();

        $app->setConfig("test", true);

        $this->assertTrue($app->getConfig("test"));
        $this->assertEquals("default", $app->getConfig("undefined", "default"));
        $this->assertNotTrue($app->getConfig("not_here"));
    }

    public function testHandle()
    {

        $app = Application::boot();

        $request = ServerRequestFactory::fromGlobals();
        $response = new Response();

        $handledResponse = $app->handle($request, $response);

        $this->assertInstanceOf('\Zend\Diactoros\Response', $handledResponse);
    }

    /**
     * @runInSeparateProcess
     */
    public function testRun()
    {
        $app = Application::boot();

        $app->addRoutes(function(Router $router) {
            $router->get('/', '\SparkTests\Fake\FakeDomain');
        });

        $app->run();

        $this->expectOutputString('{"success":true}');
    }


}