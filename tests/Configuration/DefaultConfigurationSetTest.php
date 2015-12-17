<?php

namespace SparkTests\Configuration;

use PHPUnit_Framework_TestCase as TestCase;
use Spark\Configuration\DefaultConfigurationSet;

class DefaultConfigurationTest extends TestCase
{
    public function testDefault()
    {
        $expected = [
            'Spark\Configuration\AurynConfiguration',
            'Spark\Configuration\DiactorosConfiguration',
            'Spark\Configuration\NegotiationConfiguration',
            'Spark\Configuration\PayloadConfiguration',
            'Spark\Configuration\RelayConfiguration',
        ];

        $set = new DefaultConfigurationSet;
        $this->assertSame($expected, $set->toArray());
    }
}
