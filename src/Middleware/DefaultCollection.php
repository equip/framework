<?php

namespace Spark\Middleware;

use Spark\Handler\ActionHandler;
use Spark\Handler\ExceptionHandler;
use Spark\Handler\FormContentHandler;
use Spark\Handler\JsonContentHandler;
use Spark\Handler\ResponseHandler;
use Spark\Handler\RouteHandler;

class DefaultCollection extends Collection
{
    public function __construct()
    {
        parent::__construct([
            ResponseHandler::class,
            ExceptionHandler::class,
            RouteHandler::class,
            JsonContentHandler::class,
            FormContentHandler::class,
            ActionHandler::class,
        ]);
    }
}
