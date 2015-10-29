<?php

namespace Spark;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Relay\Relay;

class Application
{
    /**
     * @var Relay
     */
    protected $dispatcher;

    /**
     * @var ServerRequestInterface
     */
    protected $request;

    /**
     * @var ResponseInterface
     */
    protected $response;

    /**
     * @param Relay $dispatcher
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     */
    public function __construct(
        Relay $dispatcher,
        ServerRequestInterface $request,
        ResponseInterface $response
    ) {
        $this->dispatcher = $dispatcher;
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * Run the application.
     */
    public function run()
    {
        ob_start();
        $this->handle();
        ob_end_flush();
    }

    /**
     * Handle the request.
     *
     * @return ResponseInterface
     * @throws \Exception
     * @throws \LogicException
     */
    public function handle()
    {
        return call_user_func(
            $this->dispatcher,
            $this->request,
            $this->response
        );
    }
}
