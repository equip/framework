<?php

namespace Spark\Handler;

use Exception;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class ExceptionHandler
{
    /**
     * @var string
     */
    private $root;

    /**
     * @param string $root Directory path to strip from filenames.
     */
    public function __construct($root = null)
    {
        $this->root = $root;
    }

    public function __invoke(
        RequestInterface  $request,
        ResponseInterface $response,
        callable          $next
    ) {
        try {
            return $next($request, $response);
        } catch (Exception $e) {
            $response = $response
                ->withStatus(
                    method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500
                )
                ->withHeader('Content-Type', 'application/json');

            if (method_exists($e, 'withResponse')) {
                $response = $e->withResponse($response);
            }

            $body = $this->getRelativeFiles([
                'error' => $e->getMessage() ?: $response->getReasonPhrase(),
                'code' => $e->getCode(),
                'type' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTrace(),
            ]);

            $response->getBody()->write(json_encode($body));

            return $response;
        }
    }

    private function getRelativeFiles(array $stack)
    {
        if (!$this->root) {
            // Assuming that __DIR__ is vendor/sparkphp/spark/src/Handler ...
            // we should be able to remove these last 5 segments from the current
            // directory to get the root path of the application.
            $this->root = dirname(dirname(dirname(dirname(dirname(__DIR__))))) . DIRECTORY_SEPARATOR;
        }

        foreach ($stack as $key => $value) {
            if ($key === 'file' && is_string($value)) {
                $stack[$key] = str_replace($this->root, '', $value);
            } else if (is_array($value)) {
                $stack[$key] = $this->getRelativeFiles($value);
            }
        }
        return $stack;
    }
}
