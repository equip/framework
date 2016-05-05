<?php

namespace Equip\Configuration;

use Auryn\Injector;
use Equip\Env;
use Equip\Exception\EnvException;
use josegonzalez\Dotenv\Loader;

class EnvConfiguration implements ConfigurationInterface
{
    /**
     * @var string
     */
    private $envfile;

    /**
     * @param string $envfile
     *
     * @throws EnvException If a missing or unreadable file is specified
     */
    public function __construct($envfile = null)
    {
        if (empty($envfile)) {
            $envfile = $this->detectEnvFile();
        }

        if (!is_file($envfile) || !is_readable($envfile)) {
            throw EnvException::invalidFile($envfile);
        }

        $this->envfile = $envfile;
    }

    /**
     * @inheritDoc
     */
    public function apply(Injector $injector)
    {
        $injector->define(Loader::class, [
            ':filepaths' => $this->envfile,
        ]);

        $injector->share(Env::class);

        $injector->prepare(Env::class, function (Env $env, Injector $injector) {
            $loader = $injector->make(Loader::class);
            $values = $loader->parse()->toArray();
            return $env->withValues($values);
        });
    }

    /**
     * Find a .env file by traversing up the filesystem
     *
     * @return string
     *
     * @throws EnvException If no file is found
     */
    private function detectEnvFile()
    {
        $env = DIRECTORY_SEPARATOR . '.env';
        $dir = dirname(dirname(__DIR__));

        do {
            if (is_file($dir . $env)) {
                return $dir . $env;
            }
            $dir = dirname($dir);
        } while (is_readable($dir) && dirname($dir) !== $dir);

        throw EnvException::detectionFailed();
    }
}
