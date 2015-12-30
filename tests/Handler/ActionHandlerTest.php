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
    public function testHandle()
    {
        $injector = new Injector();
        $injector->share($injector);

        $injector->alias('Relay\ResolverInterface', 'Spark\Resolver\AurynResolver');

        $request = ServerRequestFactory::fromGlobals();
        $response = new Response();

        $action = new Action('Spark\Input', 'SparkTests\Fake\FakeDomain', 'Spark\Responder\ChainedResponder');
        $request = $request->withAttribute('spark/adr:action', $action)
                        ->withAttribute('test', 'success');

        $actionHandler = $injector->make('Spark\Handler\ActionHandler');

        $response = $actionHandler($request, $response, function ($req, $resp) {
            $this->assertInstanceOf('Zend\Diactoros\Response', $resp);
            return $resp;
        });

        $body = json_decode($response->getBody());
        $this->assertEquals('success', $body->input->test);

    }
}
