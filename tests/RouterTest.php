<?php
namespace SparkTests;

use PHPUnit_Framework_TestCase as TestCase;
use Spark\Adr\Input;
use Spark\Application;
use Spark\Router;
use Zend\Diactoros\ServerRequest;

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
        $this->assertInstanceOf('\Spark\Responder\Responder', $route->getResponder());
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
}