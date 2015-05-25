<?php
namespace Spark\Responder;

use Aura\Payload_Interface\PayloadInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Spark\Adr\ResponderInterface;

class Responder implements ResponderInterface
{
    /**
     * @var $request ServerRequestInterface
     */
    protected $request;

    /**
     * @var $response ResponseInterface
     */
    protected $response;

    /**
     * @var $payload PayloadInterface
     */
    protected $payload;

    public static function accepts()
    {
        return ['application/json'];
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        PayloadInterface $payload = null
    ) {
        $this->request = $request;
        $this->response = $response;
        $this->payload = $payload;
        $method = $this->getMethodForPayload();
        $this->$method();
        return $this->response;
    }

    protected function getMethodForPayload()
    {
        if (! $this->payload) {
            return 'noContent';
        }

        $method = str_replace('_', '', strtolower($this->payload->getStatus()));
        return method_exists($this, $method) ? $method : 'unknown';
    }

    protected function jsonBody($data)
    {
        if (isset($data)) {
            $this->response = $this->response->withHeader('Content-Type', 'application/json');
            $this->response->getBody()->write(json_encode($data));
        }
    }

    protected function accepted()
    {
        $this->response = $this->response->withStatus(202);
        $this->jsonBody($this->payload->getOutput());
    }

    protected function created()
    {
        $this->response = $this->response->withStatus(201);
        $this->jsonBody($this->payload->getOutput());
    }

    protected function deleted()
    {
        $this->response = $this->response->withStatus(204);
        $this->jsonBody($this->payload->getOutput());
    }

    protected function error()
    {
        $this->response = $this->response->withStatus(500);
        $this->jsonBody([
            'input' => $this->payload->getInput(),
            'error' => $this->payload->getOutput(),
        ]);
    }

    protected function failure()
    {
        $this->response = $this->response->withStatus(400);
        $this->jsonBody($this->payload->getInput());
    }

    protected function found()
    {
        $this->response = $this->response->withStatus(200);
        $this->jsonBody($this->payload->getOutput());
    }

    protected function noContent()
    {
        $this->response = $this->response->withStatus(204);
    }

    protected function notAuthenticated()
    {
        $this->response = $this->response->withStatus(400);
        $this->jsonBody($this->payload->getInput());
    }

    protected function notAuthorized()
    {
        $this->response = $this->response->withStatus(403);
        $this->jsonBody($this->payload->getInput());
    }

    protected function notFound()
    {
        $this->response = $this->response->withStatus(404);
        $this->jsonBody($this->payload->getInput());
    }

    protected function notValid()
    {
        $this->response = $this->response->withStatus(422);
        $this->jsonBody([
            'input' => $this->payload->getInput(),
            'output' => $this->payload->getOutput(),
            'messages' => $this->payload->getMessages(),
        ]);
    }

    protected function processing()
    {
        $this->response = $this->response->withStatus(203);
        $this->jsonBody($this->payload->getOutput());
    }

    protected function success()
    {
        $this->response = $this->response->withStatus(200);
        $this->jsonBody($this->payload->getOutput());
    }

    protected function unknown()
    {
        $this->response = $this->response->withStatus(500);
        $this->jsonBody([
            'error' => 'Unknown domain payload status',
            'status' => $this->payload->getStatus(),
        ]);
    }

    protected function updated()
    {
        $this->response = $this->response->withStatus(303);
        $this->jsonBody($this->payload->getOutput());
    }
}