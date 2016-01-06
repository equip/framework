<?php

namespace Equip\Configuration;

use Auryn\Injector;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\SyslogHandler;
use Equip\Env;
use Equip\Handler\ExceptionHandler;
use Equip\Logger\ExceptionLogger;

class LoggerConfiguration implements ConfigurationInterface
{
    /**
     * @var Env
     */
    protected $env;

    /**
     * @param Env $env
     */
    public function __construct(Env $env)
    {
        $this->env = $env;
    }

    /**
     * @inheritDoc
     */
    public function apply(Injector $injector)
    {
        $injector->prepare(ExceptionLogger::class, [$this, 'prepareExceptionLogger']);

        $injector->define(ExceptionHandler::class, ['logger' => ExceptionLogger::class]);
    }

    /**
     * @param ExceptionLogger $logger
     *
     * @return void
     */
    public function prepareExceptionLogger(ExceptionLogger $logger)
    {
        $formatter = new LineFormatter;
        $formatter->includeStacktraces();

        $handler = new SyslogHandler($this->env->getValue('log.exception.syslog', 'spark'));
        $handler->setFormatter($formatter);

        $logger->pushHandler($handler);
    }
}
