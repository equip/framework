<?php
namespace Spark\Exception;

use Psr\Http\Message\ResponseInterface;

class HttpBadRequestException extends \RuntimeException
{
    public function getStatusCode()
    {
        return 400;
    }
}
