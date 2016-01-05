<?php
namespace EquipTests\Fake;

use Exception;
use Psr\Http\Message\ResponseInterface;
use Equip\Handler\ExceptionHandler;

class FakeExceptionHandler extends ExceptionHandler
{

    public function __invoke(ResponseInterface $response, Exception $e)
    {

    }

}
