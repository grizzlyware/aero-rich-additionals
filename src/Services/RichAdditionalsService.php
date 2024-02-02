<?php

namespace Grizzlyware\Aero\RichAdditionals\Services;

use Grizzlyware\Aero\RichAdditionals\Contracts\RichAttributeInterface;
use Grizzlyware\Aero\RichAdditionals\Helpers\ClassHelper;
use Illuminate\Database\Eloquent\Model;

class RichAdditionalsService
{
    /**
     * @var array<string, array<string, RichAttributeInterface>>
     */
    private array $attributes = [];

    public function addAttribute(RichAttributeInterface $attribute): void
    {
        if (!isset($this->attributes[$attribute->getParentMorphClass()])) {
            $this->attributes[$attribute->getParentMorphClass()] = [];
        }

        $this->attributes[$attribute->getParentMorphClass()][$attribute->getAttributeKey()] = $attribute;
    }

    /**
     * @param class-string<Model>|Model $model
     * @return RichAttributeInterface[]
     */
    public function getAttributesForModel(string|Model $model): array
    {
        if (is_string($model)) {
            if (!ClassHelper::classExtends($model, Model::class)) {
                throw new \InvalidArgumentException('$model must be an instance of ' . Model::class);
            }

            $model = new $model();
        }

        $morphClass = (new $model())->getMorphClass();

        return $this->attributes[$morphClass] ?? [];
    }
}

