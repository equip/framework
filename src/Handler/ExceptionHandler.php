<?php

namespace Spark\Handler;

use Exception;
use InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Spark\Exception\HttpException;

class ExceptionHandler
{
    /**
     * @var string
     */
    private $root;

    public function __construct()
    {
        // Assuming that __DIR__ is vendor/sparkphp/spark/src/Handler ...
        // we should be able to remove these last 5 segments from the current
        // directory to get the root path of the application.
        $this->root = dirname(dirname(dirname(dirname(dirname(__DIR__))))) . DIRECTORY_SEPARATOR;
    }

    /**
     * Get a copy with a new root directory
     *
     * @param string $root
     *
     * @return static
     *
     * @throws InvalidArgumentException If the directory does not exist
     */
    public function withRoot($root)
    {
        if (!is_dir($root)) {
            throw new InvalidArgumentException(sprintf(
                'Directory `%s` does not exist',
                $root
            ));
        }

        $copy = clone $this;
        $copy->root = realpath($root) . DIRECTORY_SEPARATOR;

        return $copy;
    }

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param callable $next
     *
     * @return ResponseInterface
     */
    public function __invoke(
        RequestInterface  $request,
        ResponseInterface $response,
        callable          $next
    ) {
        try {
            return $next($request, $response);
        } catch (Exception $e) {
            $response = $response->withHeader('Content-Type', 'application/json');

            try {
                $response = $response->withStatus($e->getCode());
            } catch (InvalidArgumentException $_) {
                // Exception did not contain a valid code
                $response = $response->withStatus(500);
            }

            if ($e instanceof HttpException) {
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

    /**
     * Recursively convert all filenames in the stack to be relative
     *
     * @param array $stack
     *
     * @return array
     */
    private function getRelativeFiles(array $stack)
    {
        foreach ($stack as $key => $value) {
            if ($key === 'file' && is_string($value)) {
                $stack[$key] = str_replace($this->root, '', $value);
            } elseif (is_array($value)) {
                $stack[$key] = $this->getRelativeFiles($value);
            }
        }
        return $stack;
    }
}
