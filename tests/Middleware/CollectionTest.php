<?php
namespace SparkTests\Middleware;

use PHPUnit_Framework_TestCase as TestCase;
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
        $callback = function() { };
        $middlewares = [$callback];
        $collection = new MiddlewareCollection($middlewares);
        foreach ($collection as $actual) {
            $this->assertSame($callback, $actual);
        }
    }
}
