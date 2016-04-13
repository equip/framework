<?php

namespace Equip\Configuration;

use Auryn\Injector;
use League\Plates\Engine;

class PlatesConfiguration implements ConfigurationInterface
{
    use EnvTrait;

    public function apply(Injector $injector)
    {
        $injector->define(Engine::class, [
            ':directory' => $this->env['PLATES_DIRECTORY'],
        ]);
    }
}
