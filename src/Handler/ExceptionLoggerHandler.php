<?php

namespace Equip\Handler;

use Equip\Handler\ExceptionHandler;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Throwable;

class ExceptionLoggerHandler
{
    /**
     * @var ExceptionHandler
     */
    private $handler;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ExceptionHandler $handler
     * @param LoggerInterface $logger
     */
    public function __construct(
        ExceptionHandler $handler,
        LoggerInterface $logger
    ) {
        $this->handler = $handler;
        $this->logger = $logger;
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
        } catch (Throwable $throwable) {
            return $this->withLoggerException($request, $response, $throwable);
        } catch (Exception $exception) {
            return $this->withLoggerException($request, $response, $exception);
        }
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param Exception $exception
     *
     * @return ResponseInterface
     */
    public function withLoggerException(
        ServerRequestInterface $request,
        ResponseInterface $response,
        $exception
    ) {
        $this->log($exception);

        return $this->handler->withException($request, $response, $exception);
    }

    /**
     * @param Exception $exception
     *
     * @return void
     */
    private function log($exception)
    {
        $message = $exception->getMessage();
        $this->logger->error($exception, compact('message'));
    }
}
