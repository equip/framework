<?php

namespace Equip\Handler;

use Equip\Exception\HttpException;
use Relay\Middleware\JsonContentHandler as AbstractHandler;

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
    protected function throwException($message)
    {
        throw HttpException::badRequest($message);
    }
}
