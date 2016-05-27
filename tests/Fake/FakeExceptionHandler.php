<?php

namespace EquipTests\Fake;

use Equip\Handler\ExceptionHandler;
use Exception;
use Psr\Http\Message\ResponseInterface;

class FakeExceptionHandler extends ExceptionHandler
{

    public function __invoke(ResponseInterface $response, Exception $e)
    {

    }

}
