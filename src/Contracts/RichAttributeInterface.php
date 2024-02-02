<?php

namespace Grizzlyware\Aero\RichAdditionals\Contracts;

use Grizzlyware\Aero\RichAdditionals\AttributeType;

interface RichAttributeInterface
{
    public function getAttributeKey(): string;
    public function getRelationKey(): string;
    public function injectAttributable(mixed $attributable): void;
    public function getType(): AttributeType;
    public function getOptions(): array;
    public function setOptions(array|\Closure|string $options): self;
    public function setRequired(bool $required = true): self;
    public function isRequired(): bool;
    public function getHelp(): ? string;
    public function setHelp(? string $help): self;
    public function getValidationRules(): array;
    public function setValidationRules(array $rules): self;
    public function getAttributeLabel(): string;
    public function setAttributeLabel(? string $label): self;
}
