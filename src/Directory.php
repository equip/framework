<?php

namespace Equip;

use Equip\Contract\ActionInterface;
use Equip\Exception\DirectoryException;
use Equip\Structure\Dictionary;

class Directory extends Dictionary
{
    const ANY = '*';
    const GET = 'GET';
    const POST = 'POST';
    const PUT = 'PUT';
    const PATCH = 'PATCH';
    const HEAD = 'HEAD';
    const DELETE = 'DELETE';
    const OPTIONS = 'OPTIONS';

    /**
     * @var string
     */
    private $prefix = '';

    /**
     * Set the directory path prefix.
     *
     * @param string $prefix
     *
     * @return static
     */
    public function withPrefix($prefix)
    {
        $copy = clone $this;
        $copy->prefix = '/' . trim($prefix, '/');

        return $copy;
    }

    /**
     * Remove the directory path prefix.
     *
     * @return static
     */
    public function withoutPrefix()
    {
        $copy = clone $this;
        $copy->prefix = '';

        return $copy;
    }

    /**
     * Add the prefix to a path.
     *
     * @param string $path
     *
     * @return string
     */
    public function prefix($path)
    {
        return $this->prefix . $path;
    }

    /**
     * @param string $path
     * @param string $action
     *
     * @return static
     */
    public function any($path, $action)
    {
        return $this->action(self::ANY, $path, $action);
    }

    /**
     * @param string $path
     * @param string $action
     *
     * @return static
     */
    public function get($path, $action)
    {
        return $this->action(self::GET, $path, $action);
    }

    /**
     * @param string $path
     * @param string $action
     *
     * @return static
     */
    public function post($path, $action)
    {
        return $this->action(self::POST, $path, $action);
    }

    /**
     * @param string $path
     * @param string $action
     *
     * @return static
     */
    public function put($path, $action)
    {
        return $this->action(self::PUT, $path, $action);
    }

    /**
     * @param string $path
     * @param string $action
     *
     * @return static
     */
    public function patch($path, $action)
    {
        return $this->action(self::PATCH, $path, $action);
    }

    /**
     * @param string $path
     * @param string $action
     *
     * @return static
     */
    public function head($path, $action)
    {
        return $this->action(self::HEAD, $path, $action);
    }

    /**
     * @param string $path
     * @param string $action
     *
     * @return static
     */
    public function delete($path, $action)
    {
        return $this->action(self::DELETE, $path, $action);
    }

    /**
     * @param string $path
     * @param string $action
     *
     * @return static
     */
    public function options($path, $action)
    {
        return $this->action(self::OPTIONS, $path, $action);
    }

    /**
     * @param string $method
     * @param string $path
     * @param string $action
     *
     * @return static
     */
    public function action($method, $path, $action)
    {
        return $this->withValue(sprintf('%s %s', $method, $path), $action);
    }

    /**
     * @inheritDoc
     *
     * @throws DirectoryException If a value is not an Action instance
     */
    protected function assertValid(array $data)
    {
        parent::assertValid($data);

        foreach ($data as $value) {
            if (!is_subclass_of($value, ActionInterface::class)) {
                throw DirectoryException::invalidEntry($value);
            }
        }
    }
}
