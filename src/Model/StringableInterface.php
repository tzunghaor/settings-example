<?php

namespace App\Model;

interface StringableInterface
{
    public static function fromString(string $string): self;

    public function toString(): string;
}