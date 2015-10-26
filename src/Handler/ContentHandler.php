<?php
namespace Spark\Handler;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

abstract class ContentHandler
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
        if ($this->isApplicableMimeType($mime) && null === $request->getParsedBody()) {
            $parsed = $this->getParsedBody((string) $request->getBody());
            $request = $request->withParsedBody($parsed);
        }
        return $next($request, $response);
    }

    /**
     * Check if the content type is appropriate for handling.
     *
     * @param  string $mime
     * @return boolean
     */
    abstract protected function isApplicableMimeType($mime);

    /**
     * Parse the request body.
     *
     * @param string $body
     * @return mixed
     */
    abstract protected function getParsedBody($body);
}
