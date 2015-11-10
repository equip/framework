<?php
namespace SparkTests\Middleware;

use PHPUnit_Framework_TestCase as TestCase;
use Relay\MiddlewareInterface;
use Spark\Middleware\Collection as MiddlewareCollection;

class MiddlewareCollectionTest extends TestCase
{
    /**
     * @param mixed $middlewares
     * @param string $message
     */
    public function testWithInvalidEntries()
    {
        $this->setExpectedException(
            '\\DomainException',
            'All elements of $middlewares must be callable or implement Relay\\MiddlewareInterface'
        );

        $middlewares = ['foo'];
        $collection = new MiddlewareCollection($middlewares);
    }

    public function testWithValidEntries()
    {
        $middleware = $this->getMockBuilder(MiddlewareInterface::class)->getMock();
        $callback = function() { };
        $middlewares = [$callback, $middleware];
        $collection = new MiddlewareCollection($middlewares);
        $this->assertSame($middlewares, $collection->getArrayCopy());
    }
}
