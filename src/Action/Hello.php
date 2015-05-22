<?php
namespace Spark\Action;

use Spark\Action;

class Hello extends Action
{

    public function __invoke($name = 'World')
    {
        return ['hello' => $name, 'user_id' => $this->request->getAttribute('user_id')];
    }
}