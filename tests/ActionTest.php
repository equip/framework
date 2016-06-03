<?php

namespace EquipTests;

use Equip\Action;
use Equip\Adr\DomainInterface;
use Equip\Adr\InputInterface;
use Equip\Adr\ResponderInterface;
use Equip\Input;
use Equip\Responder\ChainedResponder;
use PHPUnit_Framework_TestCase as TestCase;

class ActionTest extends TestCase
{
    public function testInstance()
    {
        $domain = get_class($this->createMock(DomainInterface::class));
        $action = new Action($domain);

        $this->assertSame($domain, $action->getDomain());
        $this->assertSame(Input::class, $action->getInput());
        $this->assertSame(ChainedResponder::class, $action->getResponder());

        $responder = get_class($this->createMock(ResponderInterface::class));
        $action = new Action($domain, $responder);

        $this->assertSame($responder, $action->getResponder());

        $input = get_class($this->createMock(InputInterface::class));
        $action = new Action($domain, null, $input);

        $this->assertSame($input, $action->getInput());
        $this->assertSame(ChainedResponder::class, $action->getResponder());
    }
}
