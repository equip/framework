<?php

namespace EquipTests;

use Equip\Contract\ActionInterface;
use Equip\Directory;
use Equip\Exception\DirectoryException;
use Equip\Input;
use Equip\Structure\Dictionary;
use PHPUnit_Framework_TestCase as TestCase;

class DirectoryTest extends TestCase
{
    /**
     * @var Directory
     */
    private $directory;

    protected function setUp()
    {
        $this->directory = new Directory;
    }

    public function testDictionary()
    {
        $this->assertInstanceOf(Dictionary::class, $this->directory);
    }

    public function testInvalidAction()
    {
        $this->setExpectedExceptionRegExp(
            DirectoryException::class,
            '/Directory entry .* must be an .* instance/i'
        );

        $this->directory->withValue('GET /', $this);
    }

    public function testAction()
    {
        $action = $this->createMock(ActionInterface::class);
        $directory = $this->directory->action('LIST', '/', $action);

        $this->assertTrue($directory->hasValue('LIST /'));
        $this->assertSame($action, $directory->getValue('LIST /'));
    }

    /**
     * @dataProvider dataHttpMethods
     */
    public function testActionMethods($method)
    {
        $action = $this->createMock(ActionInterface::class);
        $callback = [$this->directory, strtolower($method)];
        $directory = call_user_func($callback, '/', $action);
        $match = constant(get_class($directory).'::'.$method);

        $this->assertTrue($directory->hasValue("$match /"));
        $this->assertSame($action, $directory->getValue("$match /"));
    }

    public function dataHttpMethods()
    {
        return [
            ['ANY'],
            ['GET'],
            ['POST'],
            ['PUT'],
            ['PATCH'],
            ['HEAD'],
            ['DELETE'],
            ['OPTIONS'],
        ];
    }

    public function testPrefix()
    {
        $directory = $this->directory->withPrefix('/test/');

        $this->assertSame('/test/path', $directory->prefix('/path'));

        $directory = $this->directory->withoutPrefix();

        $this->assertSame('/same', $directory->prefix('/same'));
    }
}
