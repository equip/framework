<?php
namespace Spark\Action;

use Psr\Http\Message\ServerRequestInterface;

abstract class Base
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