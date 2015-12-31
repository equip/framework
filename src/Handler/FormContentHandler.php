<?php
namespace Spark\Handler;

class FormContentHandler extends ContentHandler
{
    /**
     * @inheritDoc
     */
    protected function isApplicableMimeType($mime)
    {
        return $mime === 'application/x-www-form-urlencoded';
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
