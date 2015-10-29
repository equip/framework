<?php
namespace SparkTests;

use PHPUnit_Framework_TestCase as TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Relay\Relay;
use Spark\Application;

class ApplicationTest extends TestCase
{
    /**
     * @var Relay
     */
    protected $dispatcher;

    /**
     * @var ServerRequestInterface
     */
    protected $request;

    /**
     * @var ResponseInterface
     */
    protected $response;

    /**
     * @var Application
     */
    protected $app;

    public function setUp()
    {
        $this->dispatcher = $this->getMockBuilder(Relay::class)->disableOriginalConstructor()->getMock();
        $this->request = $this->getMockBuilder(ServerRequestInterface::class)->getMock();
        $this->response = $this->getMockBuilder(ResponseInterface::class)->getMock();
        $this->app = new Application($this->dispatcher, $this->request, $this->response);
    }

    public function testHandle()
    {
        $this->dispatcher
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->request, $this->response);

        $this->app->handle();
    }

    public function testRun()
    {
        $this->expectOutputString('output');

        $this->dispatcher
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->request, $this->response)
            ->will($this->returnCallback(function() { echo 'output'; }));

        $this->app->run();
    }
}
