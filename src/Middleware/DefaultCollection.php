<?php

namespace Spark\Middleware;

use Relay\Middleware\ResponseSender;
use Spark\Handler\ActionHandler;
use Spark\Handler\ExceptionHandler;
use Spark\Handler\FormContentHandler;
use Spark\Handler\JsonContentHandler;
use Spark\Handler\RouteHandler;

class DefaultCollection extends Collection
{
    public function __construct()
    {
        parent::__construct([
            ResponseSender::class,
            ExceptionHandler::class,
            RouteHandler::class,
            JsonContentHandler::class,
            FormContentHandler::class,
            ActionHandler::class,
        ]);
    }
}
