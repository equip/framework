<?php

namespace EquipTests\Handler;

use EquipTests\Configuration\ConfigurationTestCase;
use Equip\Configuration\AurynConfiguration;
use Equip\Configuration\WhoopsConfiguration;
use Equip\Handler\ExceptionHandler;
use Equip\Handler\ExceptionLoggerHandler;
use Exception;
use Psr\Log\LoggerInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;

class ExceptionLoggerHandlerTest extends ConfigurationTestCase
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
        $handler = $this->injector->make(ExceptionHandler::class);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->atLeastOnce())->method('error');

        $middleware = new ExceptionLoggerHandler(
            $handler,
            $logger
        );

        return call_user_func(
            $middleware,
            $request ?: new ServerRequest,
            $response ?: new Response,
            $next
        );
    }

    public function testWithException()
    {
        $response = $this->execute(function ($request, $response) {
            throw new Exception('Here be Logger');
        });

        $this->assertEquals(500, $response->getStatusCode());
    }
}
