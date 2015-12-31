<?php
namespace Spark\Handler;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract class ContentHandler
{
    /**
     * Parses request bodies based on content type.
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param callable $next
     *
     * @return Response
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next
    ) {
        $mime = strtolower($request->getHeaderLine('Content-Type'));

        if ($this->isApplicableMimeType($mime) && !$request->getParsedBody()) {
            $body = (string) $request->getBody();
            $parsed = $this->getParsedBody($body);
            $request = $request->withParsedBody($parsed);
        }

        return $next($request, $response);
    }

    /**
     * Check if the content type is appropriate for handling.
     *
     * @param string $mime
     *
     * @return boolean
     */
    abstract protected function isApplicableMimeType($mime);

    /**
     * Parse the request body.
     *
     * @param string $body
     *
     * @return mixed
     */
    abstract protected function getParsedBody($body);
}
