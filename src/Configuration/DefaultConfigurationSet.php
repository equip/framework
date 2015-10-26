<?php

namespace Spark\Configuration;

class DefaultConfigurationSet extends ConfigurationSet
{
    public function __construct()
    {
        parent::__construct([
            AurynConfiguration::class,
            DiactorosConfiguration::class,
            NegotiationConfiguration::class,
            RelayConfiguration::class,
        ]);
    }
}
