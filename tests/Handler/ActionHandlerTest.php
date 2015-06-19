<?php
namespace SparkTests\Handler;

use Auryn\Injector;
use PHPUnit_Framework_TestCase as TestCase;
use Spark\Router\Route;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequestFactory;

class ActionHandlerTest extends TestCase
{

    /**
     * @expectedException \Exception
     */
    public function testMissingRoute()
    {
        $injector = new Injector();
        $injector->share($injector);

        $request = ServerRequestFactory::fromGlobals();
        $response = new Response();

        $actionHandler = $injector->make('Spark\Handler\ActionHandler');

        $actionHandler($request, $response, function($req, $resp) {

            $this->assertInstanceOf('Zend\Diactoros\Response', $resp);

        });

    }

    public function testRouteInjection()
    {
        $injector = new Injector();
        $injector->share($injector);

        $request = ServerRequestFactory::fromGlobals();
        $response = new Response();

        $route = new Route('SparkTests\Fake\FakeDomain', 'Spark\Adr\Input', 'Spark\Responder\JsonResponder');
        $request = $request->withAttribute('spark/adr:route', $route)
                        ->withAttribute('test', 'success');

        $actionHandler = $injector->make('Spark\Handler\ActionHandler');

        $response = $actionHandler($request, $response, function($req, $resp) {

            $this->assertInstanceOf('Zend\Diactoros\Response', $resp);

            return $resp;
        });

        $body = json_decode($response->getBody());
        $this->assertEquals('success', $body->input->test);

    }
}