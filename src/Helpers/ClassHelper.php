<?php

namespace Grizzlyware\Aero\RichAdditionals\Helpers;

class ClassHelper
{
    public static function classUsesTrait(string|object $class, string $trait): bool
    {
        return in_array($trait, class_uses_recursive($class));
    }
}

