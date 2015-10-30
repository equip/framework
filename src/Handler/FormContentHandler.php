<?php
namespace Spark\Handler;

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
