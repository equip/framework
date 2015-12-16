<?php

namespace Spark\Exception;

use RuntimeException;
use Psr\Http\Message\ResponseInterface;

class HttpException extends RuntimeException
{
    /**
     * @param string $path
     *
     * @return static
     */
    public static function notFound($path)
    {
        return new static(sprintf(
            'Cannot find any resource at `%s`',
            $path
        ), 404);
    }

    /**
     * @param string $path
     * @param string $method
     * @param array $allowed
     *
     * @return static
     */
    public static function methodNotAllowed($path, $method, array $allowed)
    {
        $error = new static(sprintf(
            'Cannot access resource `%s` using method `%s`',
            $path,
            $method
        ), 405);

        $error->allowed = $allowed;

        return $error;
    }

    /**
     * @param string $message
     *
     * @return static
     */
    public static function badRequest($message)
    {
        return new static(sprintf(
            'Cannot parse the request: %s',
            $message
        ), 400);
    }

    /**
     * @var array
     */
    private $allowed = [];

    /**
     * @param ResponseInterface $response
     *
     * @return ResponseInterface
     */
    public function withResponse(ResponseInterface $response)
    {
        if ($this->allowed) {
            $response = $response->withHeader('Allow', implode(',', $this->allowed));
        }

        return $response;
    }
}
