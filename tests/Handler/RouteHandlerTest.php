<?php
namespace SparkTests\Handler;

use PHPUnit_Framework_TestCase as TestCase;
use SparkTests\Fake\FakeDomain;
use Spark\Handler\RouteHandler;
use Spark\Router;
use SparkTests\Fake\FakeRouteHandler;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;

class RouteHandlerTest extends TestCase
{
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

    public function testDispatching()
    {
        $methods = $this->routeMethodProvider();
        $router = new Router;

        $routeHandler = new RouteHandler($router);

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
            $request = new ServerRequest([], [], $path, $http);
            $response = new Response();
            $resolved = $routeHandler($request, $response, function ($req, $resp) {
                return $resp;
            });

            $this->assertEquals($response, $resolved);
        }
    }

    /**
     * @expectedException \Spark\Exception\HttpException
     * @expectedExceptionRegExp /cannot find any resource at/i
     */
    public function testNotFoundException()
    {
        $router = new Router();

        $handler = new RouteHandler($router);

        $handler->dispatch('GET', '/');
    }

    /**
     * @expectedException \Spark\Exception\HttpException
     * @expectedExceptionRegExp /cannot access resource .* using method/i
     */
    public function testMethodNotAllowedException()
    {
        $router = new Router();

        $router->get('/test', 'SparkTests\Fake\FakeDomain');

        $routeHandler = new RouteHandler($router);

        $routeHandler->dispatch('POST', '/test');
    }
}
