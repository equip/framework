<?php
namespace Spark\Responder;

use Aura\Payload_Interface\PayloadInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Spark\Adr\ResponderInterface;

class JsonResponder extends AbstractResponder
{
    
    public static function accepts()
    {
        return ['application/json'];
    }

    protected function responseBody($data)
    {
        if (isset($data)) {
            $this->response = $this->response->withHeader('Content-Type', 'application/json');
            $this->response->getBody()->write(json_encode($data));
        }
    }
    
}