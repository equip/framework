<?php

namespace EquipTests\Middleware;

use Equip\Exception\MiddlewareException;
use Equip\Middleware\MiddlewareSet;
use PHPUnit_Framework_TestCase as TestCase;
use Relay\MiddlewareInterface;

class MiddlewareSetTest extends TestCase
{
    public function testWithInvalidEntries()
    {
        $this->setExpectedExceptionRegExp(
            MiddlewareException::class,
            '/Middleware .* is not invokable/i'
        );

        new MiddlewareSet(['foo']);
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
