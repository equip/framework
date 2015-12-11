<?php
namespace Spark\Handler;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Spark\Exception\HttpBadRequestException;
use Zend\Diactoros\Stream;

class JsonContentHandler extends ContentHandler
{
    /**
     * @inheritDoc
     */
    protected function isApplicableMimeType($mime)
    {
        return 'application/json' === $mime
            || 'application/vnd.api+json' === $mime;
    }

    /**
     * @inheritDoc
     */
    protected function getParsedBody($body)
    {
        $body = json_decode($body, true);
        if (json_last_error() !== \JSON_ERROR_NONE) {
            $message = 'Error parsing JSON: ' . json_last_error_msg();
            throw new HttpBadRequestException($message);
        }
        return $body;
    }
}
