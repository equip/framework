<?php
namespace Spark\Handler;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class FormContentHandler extends ContentHandler
{
    /**
     * @inheritDoc
     */
    protected function isApplicableMimeType($mime)
    {
        return 'application/x-www-form-urlencoded' === $mime;
    }

    /**
     * @inheritDoc
     */
    protected function getParsedBody($body)
    {
        parse_str($body, $parsed);
        return $parsed;
    }
}
