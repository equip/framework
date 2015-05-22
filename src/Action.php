<?php
namespace Spark;

use Psr\Http\Message\ServerRequestInterface;

abstract class Action
{
    /**
     * @var ServerRequestInterface
     */
    protected $request;

    public function __construct(ServerRequestInterface $request)
    {
        $this->request = $request;
    }

}