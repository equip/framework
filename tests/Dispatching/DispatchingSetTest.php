<?php

namespace EquipTests\Dispatching;

use Auryn\Injector;
use Equip\Contract\ActionInterface;
use Equip\Directory;
use Equip\Exception\DispatchingException;
use Equip\Dispatching\DispatchingSet;
use PHPUnit_Framework_TestCase as TestCase;

class DispatchingSetTest extends TestCase
{
    public function testWithInvalidEntries()
    {
        $this->setExpectedExceptionRegExp(
            DispatchingException::class,
            '/Dispatcher .* is not invokable/i'
        );

        new DispatchingSet([__CLASS__]);
    }

    public function testWithValidEntries()
    {
        $dispatchers = [
            function () {
            }
        ];
        $dispatching = new DispatchingSet($dispatchers);
        $this->assertSame($dispatchers, $dispatching->toArray());
    }

    public function testPrepareDirectory()
    {
        $dispatchers = [
            function (Directory $directory) {
                return $directory->get('/', $this->createMock(ActionInterface::class));
            }
        ];

        $dispatching = new DispatchingSet($dispatchers);
        $directory = new Directory;

        $prepared = $dispatching($directory, $this->createMock(Injector::class));

        $this->assertInstanceOf(Directory::class, $prepared);
        $this->assertNotSame($prepared, $directory);
    }
}
