<?php
namespace Equip\Handler;

use Equip\Exception\HttpException;

class JsonContentHandler extends ContentHandler
{
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
    protected function getParsedBody($body)
    {
        $body = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $message = 'JSON ' . json_last_error_msg();
            throw HttpException::badRequest($message);
        }

        return $body;
    }
}
