<?php
namespace SparkTests\Middleware;

use PHPUnit_Framework_TestCase as TestCase;
use Spark\Middleware\Collection as MiddlewareCollection;

class MiddlewareCollectionTest extends TestCase
{
    /**
     * @param mixed $middlewares
     * @param string $message
     * @dataProvider dataInvalidEntries
     */
    public function testWithInvalidEntries($middlewares, $message)
    {
        $this->setExpectedException(
            '\\DomainException',
            $message
        );

        $collection = new MiddlewareCollection($middlewares);
    }

    /**
     * @return array
     */
    public function dataInvalidEntries()
    {
        $data = [];
        $middlewares = '$middlewares must be an array or implement Traversable';
        $elements = 'All elements of $middlewares must be callable or implement Relay\\MiddlewareInterface';

        $data[] = ['foo', $middlewares];
        $data[] = [['foo'], $elements];
        $data[] = [new \ArrayObject(['foo']), $elements];

        return $data;
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
