<?php
namespace Spark\Handler;

use Psr\Http\Message\ResponseInterface;

class ResponseHandler
{


    public function __invoke(ResponseInterface $response, $content)
    {
        $response->getBody()->write(json_encode($content));
        return $response->withHeader('Content-Type', 'application/json');
    }
}