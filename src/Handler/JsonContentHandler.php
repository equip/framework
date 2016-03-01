<?php

namespace Equip\Handler;

use Equip\Exception\HttpException;
use Relay\Middleware\JsonContentHandler as AbstractHandler;

/**
 * @deprecated 1.4.0 Switched to Relay.Middleware
 */
class JsonContentHandler extends AbstractHandler
{
    /**
     * @inheritDoc
     */
    public function __construct($assoc = true, $maxDepth = 512, $options = 0)
    {
        return parent::__construct($assoc, $maxDepth, $options);
    }

    /**
     * @inheritDoc
     */
    protected function isApplicableMimeType($mime)
    {
        return preg_match('~^application/([a-z.]+\+)?json($|;)~', $mime);
    }

    /**
     * @inheritDoc
     */
    protected function throwException($message)
    {
        throw HttpException::badRequest($message);
    }
}
