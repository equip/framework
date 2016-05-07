<?php

namespace EquipTests\Fake;

use Equip\Adr\DomainInterface;
use Equip\Payload;

class FakeDomain implements DomainInterface
{

    public function __invoke(array $input)
    {
        return (new Payload())
            ->withStatus(Payload::STATUS_OK)
            ->withOutput(['success' => true, 'input' => $input]);
    }
}
