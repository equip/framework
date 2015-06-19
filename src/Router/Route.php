<?php

namespace Spark\Router;

use Spark\Adr\RouteInterface;

class Route implements RouteInterface
{
    /**
     * @var string
     */
    private $domain;

    /**
     * @var string
     */
    private $input;

    /**
     * @var string
     */
    private $responder;

    /**
     * @param  string $domain
     * @param  string $input
     * @param  string $responder
     */
    public function __construct(
        $domain,
        $input,
        $responder
    ) {
        $this->domain    = $domain;
        $this->input     = $input;
        $this->responder = $responder;
    }

    /**
     * Set the domain handler spec.
     *
     * @param  string $spec
     * @return $this
     */
    public function setDomain($spec)
    {
        $this->domain = $spec;
        return $this;
    }

    /**
     * Get the domain spec.
     *
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * Set the input handler spec.
     *
     * @param  string $spec
     * @return $this
     */
    public function setInput($spec)
    {
        $this->input = $spec;
        return $this;
    }

    /**
     * Get the input spec.
     *
     * @return string
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * Set the responder handler spec.
     *
     * @param  string $spec
     * @return $this
     */
    public function setResponder($spec)
    {
        $this->responder = $spec;
        return $this;
    }

    /**
     * Get the responder spec.
     *
     * @return string
     */
    public function getResponder()
    {
        return $this->responder;
    }
}
