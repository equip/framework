<?php

namespace Spark;

use Psr\Http\Message\ServerRequestInterface;
use Spark\Adr\InputInterface;

class Input implements InputInterface
{
    /**
     * Flatten all input from the request
     *
     * @param ServerRequestInterface $request
     *
     * @return array
     */
    public function __invoke(
        ServerRequestInterface $request
    ) {
        $attrs = $request->getAttributes();
        $body = $request->getParsedBody();
        $cookies = $request->getCookieParams();
        $query = $request->getQueryParams();
        $uploads = $request->getUploadedFiles();

        if (empty($body)) {
            $body = [];
        } elseif (is_object($body)) {
            // Because the parsed body may also be represented as an object,
            // additional parsing is required. This is a bit dirty but works
            // very well for anonymous objects.
            $body = json_decode(json_encode($body), true);
        }

        // Order matters here! Important values go last!
        return array_replace(
            $query,
            $body,
            $uploads,
            $cookies,
            $attrs
        );
    }
}
