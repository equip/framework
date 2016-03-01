<?php

namespace Equip\Configuration;

use Equip\Env;

trait EnvTrait
{
    /**
     * @var Env
     */
    private $env;

    /**
     * @param Env $env
     */
    public function __construct(Env $env)
    {
        $this->env = $env;
    }
}
