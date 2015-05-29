<?php
namespace SparkTests\Fake;

use Aura\Payload\Payload;
use Spark\Adr\DomainInterface;

class FakeDomain implements DomainInterface
{

    public function __invoke(array $input)
    {
        return (new Payload())
            ->setStatus(Payload::FOUND)
            ->setOutput(['success' => true, 'input' => $input]);
    }

}
