<?php

namespace EquipTests\Handler;

use Auryn\Injector;
use EquipTests\Configuration\ConfigurationTestCase;
use Equip\Configuration\AurynConfiguration;
use Equip\Configuration\WhoopsConfiguration;
use Equip\Exception\HttpException;
use Equip\Handler\ExceptionHandler;
use Exception;
use PHPUnit_Framework_TestCase as TestCase;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;

class ExceptionHandlerTest extends ConfigurationTestCase
{
    protected function getConfigurations()
    {
        return [
            new AurynConfiguration,
            new WhoopsConfiguration,
        ];
    }

    private function execute(
        callable $next,
        $request = null,
        $response = null
    ) {
        return call_user_func(
            $this->injector->make(ExceptionHandler::class),
            $request ?: new ServerRequest,
            $response ?: new Response,
            $next
        );
    }

    public function testWithException()
    {
        $response = $this->execute(function ($request, $response) {
            throw new Exception;
        });

        $this->assertEquals(500, $response->getStatusCode());
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
