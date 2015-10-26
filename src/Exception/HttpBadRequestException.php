<?php
namespace Spark\Exception;

use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Stream;

class HttpBadRequestException extends \RuntimeException
{
    public function getStatusCode()
    {
        return 400;
    }

    public function withResponse(ResponseInterface $response)
    {
        $stream = new Stream('php://memory', 'w');
        $stream->write(json_encode(['error' => $this->getMessage()]));
        return $response->withBody($stream);
    }
}
