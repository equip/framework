<?php
namespace Spark\Handler;

use Zend\Diactoros\Response;

class ResponseHandler
{

    public function __invoke($content)
    {
        $response = (new Response)
            ->withHeader('Content-Type', 'application/json');
        $response->getBody()->write(json_encode($content));
        return $response;
    }
}