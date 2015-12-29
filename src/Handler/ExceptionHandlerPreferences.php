<?php

namespace Spark\Handler;

use Destrukt\Dictionary;
use Whoops\Handler\JsonResponseHandler;
use Whoops\Handler\PlainTextHandler;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Handler\XmlResponseHandler;

class ExceptionHandlerPreferences extends Dictionary
{
    /**
     * @inheritDoc
     */
    public function __construct(array $data = [])
    {
        $data += [
            'text/html' => PrettyPageHandler::class,
            'application/javascript' => JsonResponseHandler::class,
            'application/json' => JsonResponseHandler::class,
            'applicaiton/ld+json' => JsonResponseHandler::class,
            'application/vnd.api+json' => JsonResponseHandler::class,
            'application/vnd.geo+json' => JsonResponseHandler::class,
            'application/xml' => XmlResponseHandler::class,
            'application/atom+xml' => XmlResponseHandler::class,
            'application/rss+xml' => XmlResponseHandler::class,
            'text/plain' => PlainTextHandler::class,
        ]; // @codeCoverageIgnore

        parent::__construct($data);
    }
}
