<?php

namespace SparkTests\Handler;

use Auryn\Injector;
use Spark\Handler\ExceptionHandler;
use Spark\Configuration\AurynConfiguration;
use Spark\Configuration\NegotiationConfiguration;
use Spark\Configuration\WhoopsConfiguration;
use Spark\Exception\HttpException;
use SparkTests\Configuration\ConfigurationTestCase;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Response;

class ExceptionHandlerTest extends ConfigurationTestCase
{
    protected function getConfigurations()
    {
        return [
            new AurynConfiguration,
            new NegotiationConfiguration,
            new WhoopsConfiguration,
        ];
    }

    private function execute(callable $next, $request = null, $response = null)
    {
        return call_user_func(
            $this->injector->make(ExceptionHandler::class),
            $request ?: new ServerRequest,
            $response ?: new Response,
            $next
        );
    }

    public function dataTypes()
    {
        return [
            ['text/html'],
            ['application/javascript'],
            ['application/json'],
            ['applicaiton/ld+json'],
            ['application/vnd.api+json'],
            ['application/vnd.geo+json'],
            ['application/xml'],
            ['application/atom+xml'],
            ['application/rss+xml'],
            ['text/plain'],
        ];
    }

    /**
     * @dataProvider dataTypes
     */
    public function testHandle($mime)
    {
        $request = new ServerRequest;
        $request = $request->withHeader('Accept', $mime);

        $response = $this->execute(function ($request, $response) {
            throw new \Exception;
        }, $request);

        $this->assertEquals(500, $response->getStatusCode());
        $this->assertEquals($mime, $response->getHeaderLine('Content-Type'));
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
}
