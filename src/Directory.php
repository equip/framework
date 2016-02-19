<?php

namespace Equip;

use Equip\Action;
use Equip\Compatibility\StructureWithDataAlias;
use Equip\Exception\DirectoryException;
use Equip\Structure\Dictionary;

class Directory extends Dictionary
{
    use StructureWithDataAlias;

    const GET = 'GET';
    const POST = 'POST';
    const PUT = 'PUT';
    const PATCH = 'PATCH';
    const HEAD = 'HEAD';
    const DELETE = 'DELETE';
    const OPTIONS = 'OPTIONS';

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
