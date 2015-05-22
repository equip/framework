<?php

namespace Spark;

use Exception;
use Zend\Diactoros\Response;

class ExceptionDecorator
{
    public function __invoke(Exception $e)
    {
        $response = (new Response)
            ->withStatus(
                method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500
            )
            ->withHeader('Content-Type', 'application/json');

        if (method_exists($e, 'withResponse')) {
            $response = $e->withResponse($response);
        }

        $body = $this->getRelativeFiles([
            'error' => $e->getMessage() ?: $response->getReasonPhrase(),
            'code'  => $e->getCode(),
            'type'  => get_class($e),
            'file'  => $e->getFile(),
            'line'  => $e->getLine(),
            'trace' => $e->getTrace(),
        ]);

        $response->getBody()->write(json_encode($body));

        return $response;
    }

    private function getRelativeFiles(array $stack)
    {
        foreach ($stack as $key => $value) {
            if ($key === 'file' && is_string($value)) {
                $stack[$key] = str_replace(APP_PATH, '', $value);
            } else if (is_array($value)) {
                $stack[$key] = $this->getRelativeFiles($value);
            }
        }
        return $stack;
    }
}