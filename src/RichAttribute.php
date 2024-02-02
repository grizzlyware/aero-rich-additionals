<?php

namespace Grizzlyware\Aero\RichAdditionals;

use Aero\Common\Traits\CanHaveAdditionalAttributes;
use Grizzlyware\Aero\RichAdditionals\Helpers\ClassHelper;
use Grizzlyware\Aero\RichAdditionals\Providers\RichAdditionalsServiceProvider;
use Illuminate\Support\Str;

class RichAttribute implements Contracts\RichAttributeInterface
{
    /**
     * @var CanHaveAdditionalAttributes
     */
    private mixed $attributable;

    private array|\Closure $options;
    private bool $builtOptions = false;

    private bool $required = false;
    private bool $addEmptyOption = false;

    public function __construct(
        private string $key,
        private string $relationKey,
        private AttributeType $type,
        private ? string $help = null,
        private array $validationRules = [],
        private ? string $label = null,
    ) {
        //
    }

	public function getAttributeKey(): string
	{
        return $this->key;
	}

	public function getRelationKey(): string
	{
        return $this->relationKey;
	}

    public function getValue(): string
    {
        return old('rich-additionals.' . $this->getAttributeKey(), $this->attributable->additional($this->getAttributeKey())) ?? '';
    }

    public function injectAttributable(mixed $attributable): void
    {
        if (!ClassHelper::classUsesTrait($attributable, CanHaveAdditionalAttributes::class)) {
            throw new \InvalidArgumentException(get_class($attributable) . " does not implement CanHaveAdditionalAttributes");
        }

        $this->attributable = $attributable;
    }

    public function getType(): AttributeType
    {
        return $this->type;
    }

    public function getOptions(): array
    {
        $this->buildOptions();

        return $this->options;
    }

    private function buildOptions(): void
    {
        if ($this->builtOptions) {
            return;
        }

        if (is_array($this->options)) {
            $options = $this->options;
        } else {
            $options = call_user_func($this->options);
        }

        if (count($options) > 0) {
            if (reset($options) instanceof \UnitEnum) {
                $options = collect($options)
                    ->mapWithKeys(fn(\UnitEnum $enum) => [$enum->value => Str::of($enum->value)->replace('_', ' ')->title()->toString()])
                    ->toArray();
            }
        }

        if ($this->addEmptyOption) {
            $options = ['' => 'Please select...', ...$options];
        }

        $this->builtOptions = true;

        $this->options = $options;
    }

    /**
     * Pass an array of options, a closure that returns an array of options, or a string that is the name of an enum class
     */
    public function setOptions(array|\Closure|string $options): self
    {
        if (is_string($options)) {
            if (enum_exists($options)) {
                $options = $options::cases();
            } else {
                throw new \InvalidArgumentException("Invalid enum class: $options");
            }
        }

        $this->options = $options;

        return $this;
    }

    public function addEmptyOption(bool $addEmptyOption = true): self
    {
        $this->addEmptyOption = $addEmptyOption;

        return $this;
    }

    public function setRequired(bool $required = true): self
    {
        $this->required = $required;

        return $this;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function getHelp(): ? string
    {
        return $this->help;
    }

    public function setHelp(?string $help): self
    {
        $this->help = $help;

        return $this;
    }

    public function getValidationRules(): array
    {
        return $this->validationRules;
    }

    public function setValidationRules(array $rules): self
    {
        $this->validationRules = $rules;

        return $this;
    }

    public function getAttributeLabel(): string
    {
        if (empty($this->label)) {
            return Str::title(str_replace('_', ' ', $this->key));
        }

        return $this->label;
    }

    public function setAttributeLabel(? string $label): self
    {
        $this->label = $label;

        return $this;
    }
}
