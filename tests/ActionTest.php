<?php

namespace SparkTests;

use PHPUnit_Framework_TestCase as TestCase;
use Spark\Action;
use Spark\Adr\DomainInterface;
use Spark\Adr\InputInterface;
use Spark\Adr\ResponderInterface;

class ActionTest extends TestCase
{
    public function testInstance()
    {
        $domain = get_class($this->getMock(DomainInterface::class));
        $action = new Action($domain);

        $this->assertSame($domain, $action->getDomain());
        $this->assertSame('Spark\Input', $action->getInput());
        $this->assertSame('Spark\Responder\ChainedResponder', $action->getResponder());

        $responder = get_class($this->getMock(ResponderInterface::class));
        $action = new Action($domain, $responder);

        $this->assertSame($responder, $action->getResponder());

        $input = get_class($this->getMock(InputInterface::class));
        $action = new Action($domain, null, $input);

        $this->assertSame($input, $action->getInput());
        $this->assertSame('Spark\Responder\ChainedResponder', $action->getResponder());
    }
}
