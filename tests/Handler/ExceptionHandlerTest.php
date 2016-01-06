<?php

namespace EquipTests\Handler;

use Auryn\Injector;
use Equip\Configuration\AurynConfiguration;
use Equip\Configuration\LoggerConfiguration;
use Equip\Configuration\WhoopsConfiguration;
use Equip\Env;
use Equip\Handler\ExceptionHandler;
use Equip\Exception\HttpException;
use EquipTests\Configuration\ConfigurationTestCase;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Response;

class ExceptionHandlerTest extends ConfigurationTestCase
{
    protected function getConfigurations()
    {
        return [
            new AurynConfiguration,
            new WhoopsConfiguration,
            new LoggerConfiguration(new Env),
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
            ['application/ld+json'],
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
