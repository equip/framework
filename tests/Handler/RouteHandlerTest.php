<?php
namespace SparkTests\Handler;

use PHPUnit_Framework_TestCase as TestCase;
use Spark\Application;
use Spark\Exception\HttpMethodNotAllowed;
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
        $app = Application::boot();
        $router = $app->getRouter();

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
            $resolved = $routeHandler($request, $response, function($req, $resp) {
                return $resp;
            });

            $this->assertEquals($response, $resolved);
        }
    }

    /**
     * @expectedException \Spark\Exception\HttpMethodNotAllowed
     */
    public function testMethodNotAllowedException()
    {
        $router = new Router();
        $router->get('/test', 'SparkTests\Fake\FakeDomain');
        $router->post('/test', 'SparkTests\Fake\FakeDomain');

        $routeHandler = new RouteHandler($router);
        $error = null;

        $response = new Response();

        try {
            $routeHandler->dispatch('PATH', '/test');
        } catch (HttpMethodNotAllowed $e) {
            $this->assertEquals(['GET', 'POST'], $e->getAllowedMethods());

            $response = $e->withResponse($response);
            $this->assertEquals('GET,POST', $response->getHeader('Allow')[0]);
        }

        $routeHandler->dispatch('PUT', '/test');

    }

}