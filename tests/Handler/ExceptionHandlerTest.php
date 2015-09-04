<?php

namespace SparkTests\Handler;

use Spark\Handler\ExceptionHandler;
use Spark\Exception\HttpNotFound;
use Spark\Exception\HttpMethodNotAllowed;

class ExceptionHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->request  = $this->getMockBuilder('Psr\Http\Message\ServerRequestInterface')->getMock();
        $this->response = $this->getMockBuilder('Psr\Http\Message\ResponseInterface')->getMock();
        $this->body     = $this->getMockBuilder('Psr\Http\Message\StreamInterface')->getMock();

        $this->response
            ->method('getBody')
            ->willReturn($this->body);

        $this->body
            ->method('write')
            ->with($this->callback(function ($content) {
                return $this->isJson($content);
            }))
             ->willReturn($this->body);
    }

    public function testGeneric()
    {
        $handler = new ExceptionHandler(__DIR__);

        $this->response
            ->method('withStatus')
            ->with($this->equalTo(500))
            ->willReturn($this->response);

        $this->response
            ->method('withHeader')
            ->with($this->equalTo('Content-Type'), $this->isType('string'))
            ->willReturn($this->response);

        $next = function ($request, $response) {
            $this->assertInstanceOf('Psr\Http\Message\ServerRequestInterface', $request);
            $this->assertInstanceOf('Psr\Http\Message\ResponseInterface', $response);

            throw new \Exception;
        };

        $handler($this->request, $this->response, $next);
    }

    public function testNotFound()
    {
        $handler = new ExceptionHandler;

        $this->response
            ->method('withStatus')
            ->with($this->equalTo(404))
            ->willReturn($this->response);

        $this->response
            ->method('withHeader')
            ->with($this->equalTo('Content-Type'), $this->isType('string'))
            ->willReturn($this->response);

        $next = function ($request, $response) {
            throw new HttpNotFound;
        };

        $handler($this->request, $this->response, $next);
    }

    public function testMethodNotAllowed()
    {
        $handler = new ExceptionHandler;

        $this->response
            ->method('withStatus')
            ->with($this->equalTo(405))
            ->willReturn($this->response);

        $this->response
            ->method('withHeader')
            ->withConsecutive(
                [$this->equalTo('Content-Type'), $this->isType('string')],
                [$this->equalTo('Allow'), $this->equalTo('GET,PUT')]
            )
            ->willReturn($this->response);

        $next = function ($request, $response) {
            throw (new HttpMethodNotAllowed)->setAllowedMethods(['GET', 'PUT']);
        };

        $handler($this->request, $this->response, $next);
    }
}
