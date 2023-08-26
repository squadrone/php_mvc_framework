<?php

namespace App\Framework;

use App\Framework\Environment\ApplicationEnvironment;
use App\Framework\Routing\ApplicationRouting;

class Application
{
    private static object $instance;
    private string $cachePath;
    private string $logPath;
    private string $controllersPath;
    private string $servicesPath;
    private string $viewsPath;
    private string $configPath;
    private string $rootPath;
    private string $publicPath;
    private string $locale = 'en';

    const FRAMEWORK_DIR = __DIR__;

    use ApplicationEnvironment, ApplicationRouting;

    private function __construct()
    {
        $this->cachePath = self::FRAMEWORK_DIR . '/../../var/cache/';
        $this->logPath = self::FRAMEWORK_DIR . '/../../var/log/';
        $this->configPath = self::FRAMEWORK_DIR . '/../../config/';
        $this->controllersPath = self::FRAMEWORK_DIR . '/../controllers/';
        $this->servicesPath = self::FRAMEWORK_DIR . '/../services/';
        $this->viewsPath = self::FRAMEWORK_DIR . '/../views/';
        $this->rootPath = self::FRAMEWORK_DIR . '/../../';
        $this->publicPath = self::FRAMEWORK_DIR . '/../../public/';
    }

    public static function getInstance(): Application
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function start(): void
    {
        $this->readEnvironmentConfiguration();
        $this->applyEnvironmentConfigurations();
        $this->handleRequest();
    }

    private function applyEnvironmentConfigurations(): void {
        if (isset($_ENV) && array_key_exists('APP_ENV', $_ENV)) {
            $this->setEnvironment($_ENV['APP_ENV']);
        } else {
            $this->setEnvironment('dev');
        }
        include $this->configPath.$this->environment.DIRECTORY_SEPARATOR."init.php";
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
    }
}