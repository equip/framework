<?php
namespace Spark\Domain;

use Aura\Payload\Payload;
use Spark\Adr\DomainInterface;
use Spark\Adr\InputInterface;

class Hello implements DomainInterface
{

    public function __invoke(
        InputInterface $input = null
    ) {
        $payload = new Payload();
        $payload->setStatus(Payload::FOUND);
        $payload->setOutput(['hello' => '', 'user_id' => $input]);

        return $payload;
    }
}