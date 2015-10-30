<?php

namespace Spark\Middleware;

use Relay\Middleware\ResponseSender;
use Spark\Handler\ContentHandler;
use Spark\Handler\ExceptionHandler;
use Spark\Handler\RouteHandler;

class DefaultCollection extends MiddlewareCollection
{
    public function __construct()
    {
        parent::__construct([
            ResponseSender::class,
            ExceptionHandler::class,
            RouteHandler::class,
            ContentHandler::class,
            ActionHandler::class,
        ]);
    }
}
