<?php
namespace SparkTests;

use PHPUnit_Framework_TestCase as TestCase;
use Spark\Exception\HttpMethodNotAllowed;
use Spark\Exception\HttpNotFound;
use Spark\Router;

class ExceptionTest extends TestCase
{

    public function testHttpNotFound()
    {
        $http = new HttpNotFound();
        $this->assertEquals(404, $http->getStatusCode());
    }

    public function testHttpMethodNotAllowed()
    {
        $http = new HttpMethodNotAllowed();
        $this->assertEquals(405, $http->getStatusCode());
    }

}