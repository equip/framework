<?php

namespace EquipTests\Handler;

use Auryn\Injector;
use EquipTests\Configuration\ConfigurationTestCase;
use Equip\Configuration\AurynConfiguration;
use Equip\Configuration\ConfigurationInterface;
use Equip\Configuration\WhoopsConfiguration;
use Equip\Exception\HttpException;
use Equip\Handler\ExceptionHandler;
use PHPUnit_Framework_TestCase as TestCase;
use Psr\Log\LoggerInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;

class ExceptionHandlerTest extends ConfigurationTestCase
{
    protected function getConfigurations()
    {
        return [
            new AurynConfiguration,
            new MockMonologConfiguration,
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

    public function testHandleWithHttpStatusCode()
    {
        $request = new ServerRequest;

        $response = $this->execute(function ($request, $response) {
            throw new ExceptionWithHttpStatusCode('foo');
        }, $request);

        $this->assertEquals(400, $response->getStatusCode());
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

class ExceptionWithHttpStatusCode extends \Exception
{
    public function getHttpStatus()
    {
        return 400;
    }
}

class MockMonologConfiguration extends TestCase implements ConfigurationInterface
{
    public function apply(Injector $injector)
    {
        $injector->delegate(LoggerInterface::class, function () {
            $loggerMock = $this->getMock(LoggerInterface::class);

            $loggerMock
                ->expects($this->atLeastOnce())
                ->method('error');

            return $loggerMock;
        });
        $injector->share(LoggerInterface::class);
    }
}
