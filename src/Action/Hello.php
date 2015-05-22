<?php
namespace Spark\Action;

use Spark\Action\Base as BaseAction;

class Hello extends BaseAction
{

    public function __invoke($name = 'World')
    {
        return ['hello' => $name, 'user_id' => $this->request->getAttribute('user_id')];
    }
}