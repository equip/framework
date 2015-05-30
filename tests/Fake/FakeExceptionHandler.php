<?php
namespace SparkTests\Fake;

use Exception;
use Psr\Http\Message\ResponseInterface;
use Spark\Handler\ExceptionHandler;

class FakeExceptionHandler extends ExceptionHandler
{

    public function __invoke(ResponseInterface $response, Exception $e)
    {

    }

}