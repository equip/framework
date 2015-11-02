<?php

namespace Spark\Handler;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Relay\MiddlewareInterface;
use Relay\Middleware\ResponseSender;

/**
 * This class is intended to be a stopgap measure for passing type checks in
 * Spark\Middleware\Collection until Relay middlewares explicitly implement its
 * own MiddlewareInterface.
 *
 * Composition is used here rather than inheritance here partly because
 * ResponseSender does not conform to MiddlewareInterface due to its $next
 * parameter not being optional, so it's not possible to both extend
 * ResponseSender and implement MiddlewareInterface without errors.
 *
 * @see https://github.com/relayphp/Relay.Middleware/issues/12
 */
class ResponseHandler implements MiddlewareInterface
{
    /**
     * @var ResponseSender
     */
    protected $middleware;

    /**
     * @param ResponseSender $middleware
     */
    public function __construct(ResponseSender $middleware)
    {
        $this->middleware = $middleware;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(
        RequestInterface  $request,
        ResponseInterface $response,
        callable          $next = null
    ) {
        if ($next === null) {
            $next = function () { };
        }
        return call_user_func($this->middleware, $request, $response, $next);
    }
}
