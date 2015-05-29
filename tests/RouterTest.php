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
        $router->get('/', '\SparkTests\Fake\FakeDomain');

        /**
         * @var $route Router\ResolvedRoute
         */
        list ($route, $args) = $router->dispatch(Router::GET, '/');
        $this->assertInstanceOf('\Spark\Router\ResolvedRoute', $route);
        $this->assertInstanceOf('\Spark\Responder\JsonResponder', $route->getResponder());
        $this->assertInstanceOf('\Spark\Adr\InputInterface', $route->getInput());
        $this->assertInstanceOf('\Spark\Adr\DomainInterface', $route->getDomain());

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

    public function testDispatching()
    {
        $methods = $this->routeMethodProvider();
        $router = Application::boot()->getRouter();

        $path = '/test';
        $routes = [];

        foreach ($methods as $method) {
            list($type) = $method;
            $class = $this->getMockBuilder('\SparkTests\Fake\FakeDomain')
                ->setMockClassName("FakeDomain{$type}")
                ->getMock();

            $routes[$type] = $router->{$type}($path, get_class($class));
        }

        foreach ($methods as $method) {
            list($type, $http) = $method;
            list($resolved) = $router->dispatch($http, $path);

            $this->assertInstanceOf($routes[$type]->getDomain(), $resolved->getDomain());
        }
    }
}