<?php

namespace App\Framework\Routing;

class StaticSegment implements SegmentInterface
{
    private string $name;
    const REGEXP = '/^[a-zA-Z_]+$/m';

    public function __construct(string $segment)
    {
        $this->initialize($segment);
    }

    public function getType(): string
    {
        return 'static';
    }

    public function toString(): string
    {
        return $this->name;
    }

    public function parse(string $value): void
    {
        $this->name = $value;
    }

    public function initialize(string $segment): void
    {
        $this->parse($segment);
    }

    public static function is(string $segment): false|array
    {
        preg_match(self::REGEXP, $segment, $matches, PREG_OFFSET_CAPTURE, 0);
        if (empty($matches)) {
            return false;
        }
        return $matches;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'type' => $this->getType(),
        ];
    }
}