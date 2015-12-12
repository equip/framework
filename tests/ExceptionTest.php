<?php
namespace SparkTests;

use PHPUnit_Framework_TestCase as TestCase;
use Spark\Exception\HttpMethodNotAllowed;
use Spark\Exception\HttpNotFound;
use Spark\Router;
use Zend\Diactoros\Response;

class ExceptionTest extends TestCase
{
    public function testHttpNotFound()
    {
        $exception = HttpNotFound::invalidPath('/');

        $this->assertInstanceOf(HttpNotFound::class, $exception);

        $this->assertEquals(404, $exception->getStatusCode());
    }

    public function testHttpMethodNotAllowed()
    {
        $allowed = ['POST', 'PATCH'];

        $exception = HttpMethodNotAllowed::invalidAccessMethod('GET', '/', $allowed);

        $this->assertInstanceOf(HttpMethodNotAllowed::class, $exception);

        $this->assertEquals($allowed, $exception->getAllowedMethods());
        $this->assertEquals(405, $exception->getStatusCode());

        $response = $exception->withResponse(new Response);

        $this->assertEquals(implode(',', $allowed), $response->getHeaderLine('Allow'));
    }
}
