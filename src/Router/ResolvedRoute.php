<?php

namespace Spark\Router;

use Spark\Adr\DomainInterface;
use Spark\Adr\InputInterface;
use Spark\Adr\ResponderInterface;
use Spark\Adr\RouteInterface;

class ResolvedRoute implements RouteInterface
{
    /**
     * @var DomainInterface
     */
    private $domain;

    /**
     * @var InputInterface
     */
    private $input;

    /**
     * @var ResponderInterface
     */
    private $responder;

    public function __construct(
        DomainInterface    $domain,
        InputInterface     $input,
        ResponderInterface $responder
    ) {
        $this->domain    = $domain;
        $this->input     = $input;
        $this->responder = $responder;
    }

    public function getDomain()
    {
        return $this->domain;
    }

    public function getInput()
    {
        return $this->input;
    }

    public function getResponder()
    {
        return $this->responder;
    }
}
