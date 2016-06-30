<?php

namespace Equip;

use Equip\Action;
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
     * @param string|Action $domainOrAction
     *
     * @return static
     */
    public function any($path, $domainOrAction)
    {
        return $this->action(self::ANY, $path, $domainOrAction);
    }

    /**
     * @param string $path
     * @param string|Action $domainOrAction
     *
     * @return static
     */
    public function get($path, $domainOrAction)
    {
        return $this->action(self::GET, $path, $domainOrAction);
    }

    /**
     * @param string $path
     * @param string|Action $domainOrAction
     *
     * @return static
     */
    public function post($path, $domainOrAction)
    {
        return $this->action(self::POST, $path, $domainOrAction);
    }

    /**
     * @param string $path
     * @param string|Action $domainOrAction
     *
     * @return static
     */
    public function put($path, $domainOrAction)
    {
        return $this->action(self::PUT, $path, $domainOrAction);
    }

    /**
     * @param string $path
     * @param string|Action $domainOrAction
     *
     * @return static
     */
    public function patch($path, $domainOrAction)
    {
        return $this->action(self::PATCH, $path, $domainOrAction);
    }

    /**
     * @param string $path
     * @param string|Action $domainOrAction
     *
     * @return static
     */
    public function head($path, $domainOrAction)
    {
        return $this->action(self::HEAD, $path, $domainOrAction);
    }

    /**
     * @param string $path
     * @param string|Action $domainOrAction
     *
     * @return static
     */
    public function delete($path, $domainOrAction)
    {
        return $this->action(self::DELETE, $path, $domainOrAction);
    }

    /**
     * @param string $path
     * @param string|Action $domainOrAction
     *
     * @return static
     */
    public function options($path, $domainOrAction)
    {
        return $this->action(self::OPTIONS, $path, $domainOrAction);
    }

    /**
     * @param string $method
     * @param string $path
     * @param string|Action $domainOrAction
     *
     * @return static
     */
    public function action($method, $path, $domainOrAction)
    {
        if ($domainOrAction instanceof Action) {
            $action = $domainOrAction;
        } else {
            $action = new Action($domainOrAction);
        }

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
            if (!is_object($value) || !$value instanceof Action) {
                throw DirectoryException::invalidEntry($value);
            }
        }
    }
}
