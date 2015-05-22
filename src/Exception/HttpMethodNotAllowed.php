<?php
namespace Spark\Exception;

use Psr\Http\Message\ResponseInterface;

class HttpMethodNotAllowed extends \RuntimeException
{
    private $allowed = [];

    public function setAllowedMethods(array $allowed)
    {
        $this->allowed = $allowed;
        return $this;
    }

    public function getAllowedMethods()
    {
        return $this->allowed;
    }

    public function getStatusCode()
    {
        return 405;
    }

    public function withResponse(ResponseInterface $response)
    {
        return $response
            ->withHeader('Allow', implode(',', $this->getAllowedMethods()));
    }
}