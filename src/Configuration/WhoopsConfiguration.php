<?php

namespace Spark\Configuration;

use Auryn\Injector;
use Whoops\Run as Whoops;
use Whoops\Handler\JsonResponseHandler;
use Whoops\Handler\PlainTextHandler;

class WhoopsConfiguration implements ConfigurationInterface
{
    /**
     * @inheritDoc
     */
    public function apply(Injector $injector)
    {
        $injector->prepare(Whoops::class, [$this, 'prepareWhoops']);
        $injector->prepare(JsonResponseHandler::class, [$this, 'prepareJsonHandler']);
        $injector->prepare(PlainTextHandler::class, [$this, 'preparePlainTextHandler']);
    }

    /**
     * @param Whoops $whoops
     *
     * @return void
     */
    public function prepareWhoops(Whoops $whoops)
    {
        set_error_handler([$whoops, Whoops::ERROR_HANDLER]);

        $whoops->writeToOutput(false);
        $whoops->allowQuit(false);
    }

    /**
     * @param JsonResponseHandler $handler
     *
     * @return void
     */
    public function prepareJsonHandler(JsonResponseHandler $handler)
    {
        $handler->addTraceToOutput(true);
    }

    /**
     * @param PlainTextHandler $handler
     *
     * @return void
     */
    public function preparePlainTextHandler(PlainTextHandler $handler)
    {
        $handler->outputOnlyIfCommandLine(false);
    }
}
