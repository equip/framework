<?php
namespace SparkTests;

use PHPUnit_Framework_TestCase as TestCase;
use Spark\Adr\Input;
use Spark\Application;
use Spark\Router;
use SparkTests\Fake\FakeInput;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Uri;

class RouterTest extends TestCase
{
    public function testRouterDispatchAndInjection()
    {
        $app = Application::boot();
        $router = $app->getRouter();
        $router->get('/', 'SparkTests\Fake\FakeDomain');

        /**
         * @var $route Router\Route
         */
        $route = current($router->getRoutes());
        $this->assertInstanceOf('\Spark\Router\Route', $route);
        $this->assertEquals('Spark\Responder\JsonResponder', $route->getResponder());
        $this->assertEquals('Spark\Adr\Input', $route->getInput());
        $this->assertEquals('SparkTests\Fake\FakeDomain', $route->getDomain());

    }

    public function testRouteInput()
    {
        $input = new Input();
        $request = (new ServerRequest)
            ->withAttribute("attribute", "true")
            ->withQueryParams(["query" => "true"]);

        $output = $input($request);

        $this->assertEquals("true", $output["query"]);
        $this->assertEquals("true", $output["attribute"]);
    }

    public function testRouteDefaults()
    {
        $app = Application::boot();

        $router = $app->getRouter();

        $input = 'SparkTest\Fake\FakeInput';
        $router->setDefaultInput($input);
        $responder = 'SparkTest\Fake\FakeResponder';
        $router->setDefaultResponder($responder);

        $route = $router->get('/', 'SparkTest\Fake\FakeDomain');

        $this->assertEquals($input, $route->getInput());
        $this->assertEquals($responder, $route->getResponder());

    }

    public function routeMethodProvider()
    {
        return [
            ['get', Router::GET],
            ['post', Router::POST],
            ['put', Router::PUT],
            ['patch', Router::PATCH],
            ['head', Router::HEAD],
            ['delete', Router::DELETE],
            ['options', Router::OPTIONS],
        ];
    }

    /**
     * @dataProvider routeMethodProvider
     */
    public function testRoutes($method)
    {
        $router = Application::boot()->getRouter();
        $route = $router->$method('/test', '\SparkTests\Fake\FakeDomain');

        $this->assertInstanceOf('Spark\Router\Route', $route);
    }

}
