<?php

namespace App\Framework\Routing;

class DynamicSegment implements SegmentInterface
{
    private string $variableName;
    private ?string $requirement = null;
    private string $defaultValue = '';
    private string $value;
    private array $matches;

    const REGEXP = '/^{([a-zA-Z0-9_]+)(<.*>)?(\?.*?)?}$/m';

    public function __construct(string $segment)
    {
        $this->initialize($segment);
    }

    public function getType(): string
    {
        return 'dynamic';
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function parse(string $value): void
    {
        $this->value = $value;
    }

    public function initialize(string $segment): void
    {
        $matches = self::is($segment);
        if ($matches !== false) {
            if (array_key_exists(1, $matches)) {
                $this->variableName = $matches[1][0];
            }
            if (array_key_exists(2, $matches)) {
                $this->checkForRequirement($matches[2][0]);
                $this->checkForDefaultValue($matches[2][0]);
            }
            if (array_key_exists(3, $matches)) {
                $this->checkForDefaultValue($matches[3][0]);
            }
        }
    }

    private function checkForRequirement(string $segmentPart): void {
        preg_match('/<(.*?)>/m', $segmentPart, $requirementMatches, PREG_OFFSET_CAPTURE, 0);
        if (!empty($requirementMatches)) {
            $this->requirement = $requirementMatches[1][0];
        }
    }

    private function checkForDefaultValue(string $segmentPart): void {
        preg_match('/\?(.*)/m', $segmentPart, $defaultValueMatches, PREG_OFFSET_CAPTURE, 0);
        if (!empty($defaultValueMatches)) {
            $this->defaultValue = $defaultValueMatches[1][0];
        }
    }

    public function getVariableName(): string
    {
        return $this->variableName;
    }

    public function getRequirement(): ?string
    {
        return $this->requirement;
    }

    public function getDefaultValue(): string
    {
        return $this->defaultValue;
    }

    public static function is(string $segment): false|array
    {
        preg_match(self::REGEXP, $segment, $matches, PREG_OFFSET_CAPTURE, 0);
        if (empty($matches)) {
            return false;
        }
        if (substr($segment, 1, 1) == "_") {
            return false;
        }
        return $matches;
    }

    public function toArray(): array
    {
        return [
            'variableName' => $this->variableName,
            'value' => $this->value,
            'requirement' => $this->requirement,
            'defaultValue' => $this->defaultValue,
            'type' => $this->getType()
        ];
    }
}