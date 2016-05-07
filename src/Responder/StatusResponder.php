<?php

namespace Equip\Responder;

use Equip\Adr\PayloadInterface;
use Equip\Adr\ResponderInterface;
use Lukasoppermann\Httpstatus\Httpstatus;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class StatusResponder implements ResponderInterface
{
    /**
     * @var Httpstatus
     */
    private $http_status;

    /**
     * @param Httpstatus $http_status
     */
    public function __construct(Httpstatus $http_status)
    {
        $this->http_status = $http_status;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        PayloadInterface $payload
    ) {
        if ($this->hasStatus($payload)) {
            $response = $response->withStatus($this->status($payload));
        }

        return $response;
    }

    /**
     * Determine if the payload has a status.
     *
     * @param PayloadInterface $payload
     *
     * @return boolean
     */
    private function hasStatus(PayloadInterface $payload)
    {
        return (bool) $payload->getStatus();
    }

    /**
     * Get the response status from the payload.
     *
     * @param PayloadInterface $payload
     *
     * @return integer
     */
    private function status(PayloadInterface $payload)
    {
        $status = $payload->getStatus();

        return $this->http_status->getStatusCode($status);
    }
}
