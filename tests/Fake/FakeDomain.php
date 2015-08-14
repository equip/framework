<?php
namespace SparkTests\Fake;

use Spark\Payload;
use Spark\Adr\DomainInterface;

class FakeDomain implements DomainInterface
{

    public function __invoke(array $input)
    {
        unset($input['spark/adr:route']);
        return (new Payload())
            ->withStatus(Payload::OK)
            ->withOutput(['success' => true, 'input' => $input]);
    }

}
