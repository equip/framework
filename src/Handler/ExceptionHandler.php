<?php

namespace Equip\Handler;

use Equip\Exception\HttpException;
use Exception;
use InvalidArgumentException;
use Negotiation\Negotiator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Relay\ResolverInterface;
use Whoops\Run as Whoops;

class ExceptionHandler
{
    /**
     * @var Negotiator
     */
    private $negotiator;

    /**
     * @var ExceptionHandlerPreferences
     */
    private $preferences;

    /**
     * @var ResolverInterface
     */
    private $resolver;

    /**
     * @var Whoops
     */
    private $whoops;

    /**
     * @var LoggerInterface|null
     */
    private $logger;

    /**
     * @param ExceptionHandlerPreferences $preferences
     * @param Negotiator $negotiator
     * @param ResolverInterface $resolver
     * @param Whoops $whoops
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        ExceptionHandlerPreferences $preferences,
        Negotiator $negotiator,
        ResolverInterface $resolver,
        Whoops $whoops,
        LoggerInterface $logger = null
    ) {
        $this->preferences = $preferences;
        $this->logger = $logger;
        $this->negotiator = $negotiator;
        $this->resolver = $resolver;
        $this->whoops = $whoops;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param callable $next
     *
     * @return ResponseInterface
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next
    ) {
        try {
            return $next($request, $response);
        } catch (Exception $e) {
            if ($this->logger) {
                $this->logger->error($e->getMessage(), [
                    'exception' => $e
                ]);
            }

            $type = $this->type($request);

            $response = $response->withHeader('Content-Type', $type);

            try {
                if (method_exists($e, 'getHttpStatus')) {
                    $code = $e->getHttpStatus();
                } else {
                    $code = $e->getCode();
                }
                $response = $response->withStatus($code);
            } catch (InvalidArgumentException $_) {
                // Exception did not contain a valid code
                $response = $response->withStatus(500);
            }

            if ($e instanceof HttpException) {
                $response = $e->withResponse($response);
            }

            $handler = $this->handler($type);
            $this->whoops->pushHandler($handler);

            $body = $this->whoops->handleException($e);
            $response->getBody()->write($body);

            $this->whoops->popHandler();

            return $response;
        }
    }

    /**
     * Determine the preferred content type for the current request
     *
     * @param ServerRequestInterface $request
     *
     * @return string
     */
    private function type(ServerRequestInterface $request)
    {
        $accept = $request->getHeaderLine('Accept');
        $priorities = $this->preferences->toArray();

        if (!empty($accept)) {
            $preferred = $this->negotiator->getBest($accept, array_keys($priorities));
        }

        if (!empty($preferred)) {
            return $preferred->getValue();
        }

        return key($priorities);
    }

    /**
     * Retrieve the handler to use for the given type
     *
     * @param string $type
     *
     * @return \Whoops\Handler\HandlerInterface
     */
    private function handler($type)
    {
        return call_user_func($this->resolver, $this->preferences[$type]);
    }
}
