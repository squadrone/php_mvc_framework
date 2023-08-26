<?php

namespace App\Framework\Environment;

use Dotenv\Dotenv;

trait ApplicationEnvironment
{
    private string $environment = 'prod';

    public function getEnvironment(): string
    {
        return $this->environment;
    }

    public function setEnvironment(string $environment): void
    {
        $this->environment = $environment;
    }

    private function readEnvironmentConfiguration(): void
    {
        $dotenv = Dotenv::createImmutable($this->rootPath, '.env');
        $dotenv->load();
    }

}