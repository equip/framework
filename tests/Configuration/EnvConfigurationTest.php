<?php

namespace SparkTests\Configuration;

use Spark\Configuration\EnvConfiguration;
use Spark\Env;

class EnvConfigurationTest extends ConfigurationTestCase
{
    /**
     * @var string
     */
    private $envfile;

    public function setUp()
    {
        if (!class_exists('josegonzalez\Dotenv\Loader')) {
            $this->markTestSkipped('Dotenv is not installed');
        }

        $this->envfile = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . '.env';
    }

    protected function getConfigurations()
    {
        return [
            new EnvConfiguration,
        ];
    }

    public function testApply()
    {
        $this->createEnv();
        $this->applyConfigurations();

        $env = $this->injector->make(Env::class);

        $this->assertInstanceOf(Env::class, $env);
        $this->assertTrue($env['test']);

        $this->destroyEnv();
    }

    /**
     * @expectedException \Spark\Exception\EnvException
     * @expectedExceptionMessageRegExp /unable to automatically detect/i
     */
    public function testUnableToDetect()
    {
        $config = new EnvConfiguration;
    }

    /**
     * @expectedException \Spark\Exception\EnvException
     * @expectedExceptionMessageRegExp /environment file .* does not exist/i
     */
    public function testInvalidRoot()
    {
        $config = new EnvConfiguration('/tmp/bad/path/.env');
    }

    /**
     * @return void
     */
    private function createEnv()
    {
        file_put_contents($this->envfile, 'test=true');
    }

    /**
     * @return void
     */
    private function destroyEnv()
    {
        unlink($this->envfile);
    }
}
