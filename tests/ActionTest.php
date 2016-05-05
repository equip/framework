<?php

namespace EquipTests;

use Equip\Action;
use Equip\Adr\DomainInterface;
use Equip\Adr\InputInterface;
use Equip\Adr\ResponderInterface;
use PHPUnit_Framework_TestCase as TestCase;

class ActionTest extends TestCase
{
    public function testInstance()
    {
        $domain = get_class($this->getMock(DomainInterface::class));
        $action = new Action($domain);

        $this->assertSame($domain, $action->getDomain());
        $this->assertSame('Equip\Input', $action->getInput());
        $this->assertSame('Equip\Responder\ChainedResponder', $action->getResponder());

        $responder = get_class($this->getMock(ResponderInterface::class));
        $action = new Action($domain, $responder);

        $this->assertSame($responder, $action->getResponder());

        $input = get_class($this->getMock(InputInterface::class));
        $action = new Action($domain, null, $input);

        $this->assertSame($input, $action->getInput());
        $this->assertSame('Equip\Responder\ChainedResponder', $action->getResponder());
    }
}
