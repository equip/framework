<?php

namespace Equip\Logger;

use Monolog\Logger;

class ExceptionLogger extends Logger
{
    public function __construct($name = 'exception', array $handlers = [], array $processors = [])
    {
        parent::__construct($name, $handlers, $processors);
    }
}
