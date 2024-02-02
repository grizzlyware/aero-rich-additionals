<?php

namespace Grizzlyware\Aero\RichAdditionals\Services;

use Grizzlyware\Aero\RichAdditionals\AttributeType;
use Grizzlyware\Aero\RichAdditionals\Contracts\RichAttributeInterface;
use Grizzlyware\Aero\RichAdditionals\RichAttribute;

class RichAdditionalsService
{
    /**
     * @var array<string, array<string, RichAttributeInterface>>
     */
    private array $attributes = [];

    public function addAttribute(RichAttributeInterface $attribute): void
    {
        if (!isset($this->attributes[$attribute->getRelationKey()])) {
            $this->attributes[$attribute->getRelationKey()] = [];
        }

        $this->attributes[$attribute->getRelationKey()][$attribute->getAttributeKey()] = $attribute;
    }

    public function add(string $key, string $model, AttributeType $type = AttributeType::STRING): RichAttributeInterface
    {
        $attribute = new RichAttribute($key, $model, $type);

        $this->addAttribute($attribute);

        return $attribute;
    }

    public function enum(string $key, string $model, string $enumClass): RichAttributeInterface
    {
        $attribute = RichAttribute::enum($key, $model, $enumClass);

        $this->addAttribute($attribute);

        return $attribute;
    }

    /**
     * @param string $relationKey
     * @return RichAttributeInterface[]
     */
    public function getAttributesForRelation(string $relationKey): array
    {
        return $this->attributes[$relationKey] ?? [];
    }
}

