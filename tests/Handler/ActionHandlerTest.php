<?php
namespace SparkTests\Handler;

use Arbiter\Action;
use Auryn\Injector;
use PHPUnit_Framework_TestCase as TestCase;
use Spark\Resolver\AurynResolver;
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

        $injector->alias('Spark\Resolver\ResolverInterface', 'Spark\Resolver\AurynResolver');
        $injector->alias('Negotiation\NegotiatorInterface', 'Negotiation\Negotiator');

        $request = ServerRequestFactory::fromGlobals();
        $response = new Response();

        $action = new Action('Spark\Adr\Input', 'SparkTests\Fake\FakeDomain', 'Spark\Responder\ChainedResponder');
        $request = $request->withAttribute('spark/adr:action', $action)
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
