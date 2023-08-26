<?php

namespace App\Framework\Routing;

class Route
{
    private string $name;
    private array $methods;
    private array $segments;
    private string $controller;

    public function __construct(string $name)
    {
        $this->name = $name;
        $this->segments = [];
    }

    public function getMethods(): array
    {
        return $this->methods;
    }

    public function getSegments(): array
    {
        return $this->segments;
    }

    public function getSegmentCount(): int {
        return count($this->segments);
    }

    public function setMethods(array $methods): void
    {
        $this->methods = $methods;
    }

    public function setSegments(string $path): void {
        $this->segments = [];
        $segments = explode('/', $path);
        foreach ($segments as $segment) {
            if (!empty($segment)) {
                if (SpecialSegment::is($segment)) {
                    $this->segments[] = new SpecialSegment($segment);
                } elseif (DynamicSegment::is($segment)) {
                    $this->segments[] = new DynamicSegment($segment);
                } elseif (StaticSegment::is($segment)) {
                    $this->segments[] = new StaticSegment($segment);
                }
            }
        }
    }

    public function getController(): string
    {
        return $this->controller;
    }

    public function setController(string $controller): void
    {
        $this->controller = $controller;
    }

    public function toArray(): array {
        $result = [
            'name' => $this->name,
            'controller' => $this->controller,
            'methods' => $this->getMethods(),
            'segments' => []
        ];
        foreach ($this->segments as $segment) {
            $result[] = $segment->toArray();
        }
        return $result;
    }
}