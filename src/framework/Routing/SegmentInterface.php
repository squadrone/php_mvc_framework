<?php

namespace App\Framework\Routing;

interface SegmentInterface
{
    public static function is(string $segment): false|array;
    public function initialize(string $segment): void;
    public function parse(string $value): void;
    public function getType(): string;
    public function toString(): string;
    public function toArray(): array;
}