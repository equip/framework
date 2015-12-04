<?php

namespace Spark\Configuration;

class DefaultConfigurationSet extends ConfigurationSet
{
    public function __construct(array $classes = [])
    {
        $defaults = [
            AurynConfiguration::class,
            DiactorosConfiguration::class,
            NegotiationConfiguration::class,
            PayloadConfiguration::class,
            RelayConfiguration::class,
        ];

        parent::__construct(array_merge($defaults, $classes));
    }
}
