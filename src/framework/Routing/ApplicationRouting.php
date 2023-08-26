<?php

namespace App\Framework\Routing;

use App\Helpers\ExportHelper;
use Dotenv\Dotenv;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

trait ApplicationRouting
{
    private array $simplifiedRoutes = [];
    private array $routingTree = [];
    private array $routingTreeByMethodsAndSegmentCount = [];
    const ROUTE_NAME_REGEXP = '/^([a-zA-Z][a-zA-Z0-9_]+)$/';

    public function handleRequest(): Request
    {
        $this->readRoutingConfiguration();
        return new Request();
    }

    private function readRoutingConfiguration(): void
    {
        if ($this->getEnvironment() == 'dev') {
            $routes = $this->readRoutingConfigurationFromYaml();
            $this->simplifyRoutes($routes);
        } else {
            $this->simplifiedRoutes = $this->readRoutingConfigurationFromCache();
        }
        $this->buildRoutingTree($this->simplifiedRoutes);
    }

    private function readRoutingConfigurationFromYaml(): mixed
    {
        $this->resetTree();
        $routesYamlFilePath = $this->configPath.'routes.yaml';
        if (file_exists($routesYamlFilePath)) {
            try {
                return Yaml::parseFile($routesYamlFilePath);
            } catch (ParseException) {
            }
        }
    }

    private function readRoutingConfigurationFromCache(): mixed
    {
        $routingCacheFile = $this->cachePath.'routes.php';
        if (!file_exists($routingCacheFile)) {
            $routes = $this->readRoutingConfigurationFromYaml();
            $this->simplifyRoutes($routes);
            $routes = $this->simplifiedRoutes;
            file_put_contents($routingCacheFile, "<?php\n\nreturn ".ExportHelper::var_export($routes, true).";\n");
        }
        return include $routingCacheFile;
    }

    private function resetTree(): void {
        $this->routingTree = [];
        $this->routingTreeByMethodsAndSegmentCount = [];
    }

    private function simplifyRoutes(array $routes, string $prefix = '/'): array {
        $prefix = ltrim($prefix, "/");
        $pathPrefix = $prefix;
        $namePrefix = '';
        if (strlen($prefix) > 0) {
            $namePrefix = $prefix.'_';
            $pathPrefix = "/".$prefix;
        }
        foreach ($routes as $name => $routeDetails) {
            if ($name == 'group') {
                if (array_key_exists('prefix', $routeDetails) && array_key_exists('routes', $routeDetails)) {
                    $this->simplifyRoutes($routeDetails['routes'], $prefix.$routeDetails['prefix']);
                }
            } else {
                $name = $namePrefix.$name;
                $matches = [];
                preg_match(self::ROUTE_NAME_REGEXP, $name, $matches, PREG_OFFSET_CAPTURE, 0);
                if (!empty($matches)) {
                    if (array_key_exists('path', $routeDetails)) {
                        $newPath = $pathPrefix.$routeDetails['path'];
                        $routeDetails['path'] = $newPath;
                        if (!array_key_exists($name, $this->simplifiedRoutes)) {
                            $this->simplifiedRoutes[$name] = $routeDetails;
                        }
                    }
                }
            }
        }
        return $routes;
    }

    private function buildRoutingTree(array $routes): array {
        foreach ($routes as $name => $routeDetails) {
            $matches = [];
            preg_match(self::ROUTE_NAME_REGEXP, $name, $matches, PREG_OFFSET_CAPTURE, 0);
            if (!empty($matches)) {
                if (array_key_exists('path', $routeDetails)) {
                    if (!array_key_exists($routeDetails['path'], $this->routingTree)) {
                        $route = new Route($name);
                        $route->setSegments($routeDetails['path']);
                        if (array_key_exists('controller', $routeDetails)) {
                            $route->setController($routeDetails['controller']);
                            if (array_key_exists('methods', $routeDetails)) {
                                $route->setMethods(explode('|', $routeDetails['methods']));
                            } else {
                                $route->setMethods(['GET']);
                            }
                            $segmentCount = $route->getSegmentCount();
                            foreach ($route->getMethods() as $method) {
                                if (!array_key_exists($method, $this->routingTreeByMethodsAndSegmentCount)) {
                                    $this->routingTreeByMethodsAndSegmentCount[$method] = [];
                                }
                                if (!array_key_exists($segmentCount, $this->routingTreeByMethodsAndSegmentCount[$method])) {
                                    $this->routingTreeByMethodsAndSegmentCount[$method][$segmentCount] = [];
                                }
                                $this->routingTreeByMethodsAndSegmentCount[$method][$segmentCount][$routeDetails['path']] = $route;
                            }
                        }
                        $this->routingTree[$routeDetails['path']] = $route;
                    }
                }
            }
        }
        return $routes;
    }
}