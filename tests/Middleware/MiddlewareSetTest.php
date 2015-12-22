<?php
namespace SparkTests\Middleware;

use PHPUnit_Framework_TestCase as TestCase;
use Relay\MiddlewareInterface;
use Spark\Middleware\MiddlewareSet;

class MiddlewareSetTest extends TestCase
{
    /**
     * @expectedException \DomainException
     * @expectedExceptionRegExp /must be callable/i
     */
    public function testWithInvalidEntries()
    {
        $middleware = ['foo'];
        $collection = new MiddlewareSet($middleware);
    }

    public function testWithValidEntries()
    {
        $middleware = [
            $this->getMock(MiddlewareInterface::class),
            function () {
            }
        ];
        $collection = new MiddlewareSet($middleware);
        $this->assertSame($middleware, $collection->toArray());
    }

    public function testAdd()
    {
        $collection = new MiddlewareSet;
        $this->assertEmpty($collection->toArray());

        $m1 = $this->getMiddlewareClass();
        $m2 = $this->getMiddlewareClass();
        $m3 = $this->getMiddlewareClass();

        $collection = $collection->withValue($m1);
        $this->assertContains($m1, $collection);

        // Insert the second middleware before the first and append the third.
        $collection = $collection->withValueBefore($m2, $m1);
        $collection = $collection->withValueAfter($m3, $m1);

        $this->assertSame([$m2, $m1, $m3], $collection->toArray());
    }

    /**
     * @return string
     */
    private function getMiddlewareClass()
    {
        return get_class($this->prophesize(MiddlewareInterface::class)->reveal());
    }
}
