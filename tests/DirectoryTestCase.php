<?php

namespace EquipTests;

use Equip\Action;
use Equip\Adr\DomainInterface;
use PHPUnit_Framework_TestCase as TestCase;

abstract class DirectoryTestCase extends TestCase
{
    /**
     * @param string $domain
     * @param string $responder
     * @param string $input
     *
     * @return Action
     */
    protected function getMockAction(
        $domain = null,
        $input = null,
        $responder = null
    ) {
        if (!$domain) {
            $domain = get_class($this->getMockDomain());
        }

        $action = $this->getMockBuilder(Action::class);

        $action->setConstructorArgs([
            $domain,
            $input,
            $responder,
        ]);

        return $action->getMock();
    }

    /**
     * @return DomainInterface
     */
    protected function getMockDomain()
    {
        return $this->createMock(DomainInterface::class);
    }
}
