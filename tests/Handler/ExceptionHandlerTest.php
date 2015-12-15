<?php

namespace SparkTests\Handler;

use Spark\Handler\ExceptionHandler;
use Spark\Exception\HttpException;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Response;

class ExceptionHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ExceptionHandler
     */
    private $handler;

    public function setUp()
    {
        $this->handler = new ExceptionHandler;
    }

    private function execute(callable $next)
    {
        return call_user_func($this->handler, new ServerRequest, new Response, $next);
    }

    public function testGeneric()
    {
        $response = $this->execute(function ($request, $response) {
            throw new \Exception;
        });

        $this->assertEquals(500, $response->getStatusCode());
        $this->assertEquals('application/json', $response->getHeaderLine('Content-Type'));
        $this->assertJson((string) $response->getBody());

        return $response;
    }

    public function testNotFound()
    {
        $response = $this->execute(function ($request, $response) {
            throw HttpException::notFound($request->getUri()->getPath());
        });

        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testMethodNotAllowed()
    {
        $response = $this->execute(function ($request, $response) {
            throw HttpException::methodNotAllowed('POST', '/', ['GET', 'PUT']);
        });

        $this->assertEquals(405, $response->getStatusCode());
        $this->assertEquals('GET,PUT', $response->getHeaderLine('Allow'));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionRegExp /directory .* does not exist/i
     */
    public function testCannotUseInvalidRoot()
    {
        $this->handler->withRoot('totally-invalid-directory-name');
    }

    public function testFilesHaveRelativeRoot()
    {
        $this->handler = $this->handler->withRoot(__DIR__);

        $response = $this->execute(function ($request, $response) {
            throw new \Exception;
        });

        $body = json_decode((string) $response->getBody(), true);

        // The current directory should not contained in the trace
        $this->assertNotContains(__DIR__, $body['file']);
        foreach ($body['trace'] as $trace) {
            if (!empty($trace['file'])) {
                $this->assertNotContains(__DIR__, $trace['file']);
            }
        }
    }
}
