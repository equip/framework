<?php

namespace Equip;

use Equip\Adr\InputInterface;
use Psr\Http\Message\ServerRequestInterface;

class Input implements InputInterface
{
    /**
     * Flatten all input from the request.
     *
     * @param ServerRequestInterface $request
     *
     * @return array
     */
    public function __invoke(
        ServerRequestInterface $request
    ) {
        $attrs = $request->getAttributes();
        $body = $this->body($request);
        $cookies = $request->getCookieParams();
        $query = $request->getQueryParams();
        $uploads = $request->getUploadedFiles();

        // Order matters here! Important values go last!
        return array_replace(
            $query,
            $body,
            $uploads,
            $cookies,
            $attrs
        );
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return array
     */
    private function body(ServerRequestInterface $request)
    {
        $body = $request->getParsedBody();

        if (empty($body)) {
            return [];
        }

        if (is_object($body)) {
            // Because the parsed body may also be represented as an object,
            // additional parsing is required. This is a bit dirty but works
            // very well for anonymous objects.
            $body = json_decode(json_encode($body), true);
        }

        return $body;
    }
}
