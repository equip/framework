<?php
namespace Spark\Responder;

use Aura\Payload_Interface\PayloadInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Spark\Adr\ResponderInterface;

abstract class AbstractResponder implements ResponderInterface
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

    abstract protected function responseBody($data);

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

    protected function accepted()
    {
        $this->response = $this->response->withStatus(202);
        $this->responseBody($this->payload->getOutput());
    }

    protected function created()
    {
        $this->response = $this->response->withStatus(201);
        $this->responseBody($this->payload->getOutput());
    }

    protected function deleted()
    {
        $this->response = $this->response->withStatus(204);
        $this->responseBody($this->payload->getOutput());
    }

    protected function error()
    {
        $this->response = $this->response->withStatus(500);
        $this->responseBody([
            'input' => $this->payload->getInput(),
            'error' => $this->payload->getOutput(),
        ]);
    }

    protected function failure()
    {
        $this->response = $this->response->withStatus(400);
        $this->responseBody($this->payload->getInput());
    }

    protected function found()
    {
        $this->response = $this->response->withStatus(200);
        $this->responseBody($this->payload->getOutput());
    }

    protected function noContent()
    {
        $this->response = $this->response->withStatus(204);
    }

    protected function notAuthenticated()
    {
        $this->response = $this->response->withStatus(400);
        $this->responseBody($this->payload->getInput());
    }

    protected function notAuthorized()
    {
        $this->response = $this->response->withStatus(403);
        $this->responseBody($this->payload->getInput());
    }

    protected function notFound()
    {
        $this->response = $this->response->withStatus(404);
        $this->responseBody($this->payload->getInput());
    }

    protected function notValid()
    {
        $this->response = $this->response->withStatus(422);
        $this->responseBody([
            'input' => $this->payload->getInput(),
            'output' => $this->payload->getOutput(),
            'messages' => $this->payload->getMessages(),
        ]);
    }

    protected function processing()
    {
        $this->response = $this->response->withStatus(203);
        $this->responseBody($this->payload->getOutput());
    }

    protected function success()
    {
        $this->response = $this->response->withStatus(200);
        $this->responseBody($this->payload->getOutput());
    }

    protected function unknown()
    {
        $this->response = $this->response->withStatus(500);
        $this->responseBody([
            'error' => 'Unknown domain payload status',
            'status' => $this->payload->getStatus(),
        ]);
    }

    protected function updated()
    {
        $this->response = $this->response->withStatus(303);
        $this->responseBody($this->payload->getOutput());
    }
}