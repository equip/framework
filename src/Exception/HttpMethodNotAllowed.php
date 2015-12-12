<?php
namespace Spark\Exception;

use Psr\Http\Message\ResponseInterface;
use RuntimeException;

class HttpMethodNotAllowed extends RuntimeException
{
    /**
     * @param string $method
     * @param string $path
     * @param array $allowed
     *
     * @return static
     */
    public static function invalidAccessMethod($method, $path, array $allowed)
    {
        $error = new static(sprintf(
            'Cannot access resource `%s` using method `%s`',
            $path,
            $method,
            implode(', ', $allowed)
        ));

        $error->setAllowedMethods($allowed);

        return $error;
    }

    /**
     * @var array
     */
    private $allowed = [];

    /**
     * @param array $allowed
     *
     * @return static
     */
    public function setAllowedMethods(array $allowed)
    {
        $this->allowed = $allowed;
        return $this;
    }

    /**
     * @return array
     */
    public function getAllowedMethods()
    {
        return $this->allowed;
    }

    /**
     * @return integer
     */
    public function getStatusCode()
    {
        return 405;
    }

    /**
     * @param ResponseInterface $response
     *
     * @return ResponseInterface
     */
    public function withResponse(ResponseInterface $response)
    {
        $methods = implode(',', $this->getAllowedMethods());
        return $response->withHeader('Allow', $methods);
    }
}
