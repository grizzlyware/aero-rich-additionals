<?php

namespace Grizzlyware\Aero\RichAdditionals\Services;

use Grizzlyware\RichAdditionalAttributes\Contracts\RichAttributeInterface;

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

    /**
     * @param string $relationKey
     * @return RichAttributeInterface[]
     */
    public function getAttributesForRelation(string $relationKey): array
    {
        return $this->attributes[$relationKey] ?? [];
    }
}

