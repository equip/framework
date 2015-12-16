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
        $input = [];

        if ($params = $request->getQueryParams()) {
            $input = array_replace($input, $params);
        }
        if ($params = $request->getParsedBody()) {
            $input = array_replace($input, $params);
        }
        if ($params = $request->getUploadedFiles()) {
            $input = array_replace($input, $params);
        }
        if ($params = $request->getCookieParams()) {
            $input = array_replace($input, $params);
        }
        if ($params = $request->getAttributes()) {
            $input = array_replace($input, $params);
        }

        return $input;
    }
}
