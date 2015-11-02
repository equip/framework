<?php
namespace SparkTests\Middleware;

use PHPUnit_Framework_TestCase as TestCase;
use Relay\MiddlewareInterface;
use Spark\Middleware\DefaultCollection;

class DefaultCollectionTest extends TestCase
{
    public function testConstructor()
    {
        $collection = new DefaultCollection;
        foreach ($collection as $middleware) {
            $this->assertTrue(is_subclass_of($middleware, MiddlewareInterface::class));
        }
    }
}
