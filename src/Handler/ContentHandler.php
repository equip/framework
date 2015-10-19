<?php
namespace Spark\Handler;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ContentHandler
{
    /**
     * Parses request bodies based on content type
     *
     * @param  Request  $request
     * @param  Response $response
     * @param  callable $next
     * @return Response
     */
    public function __invoke(Request $request, Response $response, callable $next)
    {
        $mime = strtolower($request->getHeaderLine('Content-Type'));

        if ($this->isJsonType($mime)) {
            $body = json_decode((string) $request->getBody(), true);
        } elseif ($this->isFormType($mime) && null === $request->getParsedBody()) {
            parse_str((string) $request->getBody(), $body);
        }

        if (isset($body) && is_array($body)) {
            $request = $request->withParsedBody($body);
        }

        return $next($request, $response);
    }

    /**
     * Check if the content type is JSON encoded
     *
     * @param  string $mime
     * @return boolean
     */
    protected function isJsonType($mime)
    {
        return 'application/json' === $mime
            || 'application/vnd.api+json' === $mime;
    }

    /**
     * Check if the content type is form encoded
     *
     * @param  string $mime
     * @return boolean
     */
    protected function isFormType($mime)
    {
        return 'application/x-www-form-urlencoded' === $mime;
    }
}
