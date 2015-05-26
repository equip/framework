<?php
namespace Spark\Exception;

class HttpNotFound extends \RuntimeException
{
    public function getStatusCode()
    {
        return 404;
    }
}