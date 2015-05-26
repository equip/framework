<?php
namespace Spark;

use Spark\Adr\DomainInterface;
use Spark\Adr\RouteInterface;

class Route implements RouteInterface
{
    /**
     * @var string
     */
    protected $method;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var string
     */
    protected $domain;

    /**
     * @var string
     */
    protected $input = 'Spark\Adr\Input';

    /**
     * @var string
     */
    protected $responder = 'Spark\Responder\Responder';

    public function __construct($method, $path, $domain)
    {
        $this->method = $method;
        $this->path   = $path;
        $this->domain = $domain;
    }

    /**
     * Set the domain handler for the route
     *
     * @param $domain string|DomainInterface
     * @return $this
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;
        return $this;
    }

    public function getDomain()
    {
        return $this->domain;
    }

    public function setInput($input)
    {
        $this->input = $input;
        return $this;
    }

    public function getInput()
    {
        return $this->input;
    }

    public function setResponder($responder)
    {
        $this->responder = $responder;
        return $this;
    }

    public function getResponder()
    {
        return $this->responder;
    }
}
