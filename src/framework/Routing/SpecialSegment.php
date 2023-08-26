<?php

namespace App\Framework\Routing;

class SpecialSegment implements SegmentInterface
{
    const REGEXP = '/^(.*?){_(.*?)}(.*?)$/m';
    const ALLOWED_PARAMETERS = ['locale', 'format'];
    private string $parameter;
    private string $prefix;
    private string $suffix;
    private string $value;

    public function __construct(string $segment)
    {
        $this->initialize($segment);
    }

    public function initialize(string $segment): void
    {
        $matches = self::is($segment);
        if ($matches !== false) {
            $this->prefix = $matches[1][0];
            $this->parameter = $matches[2][0];
            $this->suffix = $matches[3][0];
        }
    }

    public function parse(string $value): void
    {
        $this->value = $value;
    }

    public function getType(): string
    {
        return 'special';
    }

    public function toString(): string
    {
        return $this->value;
    }

    public static function is(string $segment): false|array
    {
        preg_match(self::REGEXP, $segment, $matches, PREG_OFFSET_CAPTURE, 0);
        if (empty($matches)) {
            return false;
        }
        if (!in_array($matches[2][0], self::ALLOWED_PARAMETERS)) {
            return false;
        }
        return $matches;
    }

    public function toArray(): array
    {
        return [
            'prefix' => $this->prefix,
            'parameter' => $this->parameter,
            'suffix' => $this->suffix,
            'value' => $this->value,
            'type' => $this->getType()
        ];
    }
}