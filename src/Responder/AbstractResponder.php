<?php
namespace Spark\Responder;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Spark\Adr\PayloadInterface;
use Spark\Adr\ResponderInterface;

abstract class AbstractResponder implements ResponderInterface
{
    /**
     * Get the content type of the response body.
     *
     * @return string
     */
    abstract protected function type();

    /**
     * Get the response body from the payload.
     *
     * @param  PayloadInterface $payload
     * @return string
     */
    abstract protected function body(PayloadInterface $payload);

    /**
     * Get the response status from the payload.
     *
     * @param  PayloadInterface $payload
     * @return integer
     */
    protected function getHttpStatus(PayloadInterface $payload)
    {
        $status = $payload->getStatus();

        if ($status >= PayloadInterface::OK && $status < PayloadInterface::ERROR) {
            return 200;
        }

        if ($status >= PayloadInterface::ERROR && $status < PayloadInterface::INVALID) {
            return 500;
        }

        if ($status >= PayloadInterface::INVALID && $status < PayloadInterface::UNKNOWN) {
            return 400;
        }

        return 520;
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        PayloadInterface $payload
    ) {
        $response = $response->withStatus($this->getHttpStatus($payload));
        $response = $response->withHeader('Content-Type', $this->type());

        // Overwrite the body instead of making a copy and dealing with the stream.
        $response->getBody()->write($this->body($payload));

        return $response;
    }
}
