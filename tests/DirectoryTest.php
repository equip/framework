<?php

namespace EquipTests;

use Equip\Adr\DomainInterface;
use Equip\Input;
use Equip\Directory;

class DirectoryTest extends DirectoryTestCase
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
        $this->assertInstanceOf('Equip\Structure\Dictionary', $this->directory);
    }

    /**
     * @expectedException \Equip\Exception\DirectoryException
     * @expectedExceptionRegExp /entry .* is not an action/i
     */
    public function testInvalidAction()
    {
        $directory = $this->directory->withValue('GET /', $this);
    }

    public function testAction()
    {
        $action = $this->getMockAction();
        $directory = $this->directory->action('LIST', '/', $action);

        $this->assertTrue($directory->hasValue('LIST /'));
        $this->assertSame($action, $directory->getValue("LIST /"));
    }

    public function testActionWithDomain()
    {
        $domain = get_class($this->getMockDomain());
        $directory = $this->directory->action('LIST', '/', $domain);
        $action = $directory->getValue('LIST /');

        $this->assertSame($domain, $action->getDomain());
    }

    /**
     * @dataProvider dataHttpMethods
     */
    public function testActionMethods($method)
    {
        $action = $this->getMockAction();
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
            ['OPTIONS']
        ];
    }
}
