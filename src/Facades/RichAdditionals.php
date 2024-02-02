<?php

namespace Grizzlyware\Aero\RichAdditionals\Facades;

use Grizzlyware\Aero\RichAdditionals\AttributeType;
use Grizzlyware\Aero\RichAdditionals\Contracts\RichAttributeInterface;
use Grizzlyware\Aero\RichAdditionals\Services\RichAdditionalsService;

/**
 * @method static void addAttribute(RichAttributeInterface $attribute)
 * @method static RichAttributeInterface enum(string $key, string $model, string $enumClass)
 * @method static RichAttributeInterface add(string $key, string $model, AttributeType $type = AttributeType::STRING)
 */
class RichAdditionals extends \Illuminate\Support\Facades\Facade
{
    protected static function getFacadeAccessor(): string
    {
        return RichAdditionalsService::class;
    }
}

