<?php

namespace Equip\Contract;

use Psr\Http\Message\RequestInterface;

interface InputInterface
{
    /**
     * Create a copy of the input from the request.
     *
     * @param RequestInterface $request
     *
     * @return static
     */
    public function fromRequest(RequestInterface $request);

    /**
     * Convert input into an array.
     *
     * Private details should be removed or masked formatting for public output.
     *
     * @param boolean $public
     *
     * @return array
     */
    public function toArray($public = false);
}
