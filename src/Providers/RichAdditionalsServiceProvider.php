<?php

namespace Grizzlyware\Aero\RichAdditionals\Providers;

use Aero\Admin\AdminSlot;
use Aero\Admin\Facades\Admin;
use Aero\Admin\Http\Responses\Catalog\AdminCategoryUpdate;
use Aero\Admin\Http\Responses\Catalog\AdminProductUpdate;
use Aero\Admin\Http\Responses\Configuration\AdminShippingMethodUpdate;
use Aero\Admin\Http\Responses\Content\AdminPageUpdate;
use Aero\Cart\Models\ShippingMethod;
use Aero\Catalog\Events\ProductUpdated;
use Aero\Catalog\Models\Category;
use Aero\Catalog\Models\Product;
use Aero\Common\Models\Model;
use Aero\Common\Traits\CanHaveAdditionalAttributes;
use Aero\Content\Models\Page;
use Aero\Responses\ResponseBuilder;
use Grizzlyware\Aero\RichAdditionals\Helpers\ClassHelper;
use Grizzlyware\Aero\RichAdditionals\Services\RichAdditionalsService;
use Grizzlyware\Aero\RichAdditionals\AttributeType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RichAdditionalsServiceProvider extends \Illuminate\Support\ServiceProvider
{
    private const NULL_IDENTIFIER = '6c6d6b2c-e2a6-45cc-aa97-3e4a334d0f77';

    public function register(): void
    {
        $this->loadViewsFrom(
            __DIR__ . '/../../resources/views',
            'rich-additionals'
        );

        $this->app->singleton(RichAdditionalsService::class);
    }

    public function boot(): void
    {
        Admin::booted(
            $this->setupModelsWithAttributes(...)
        );
    }

    private function setupModelsWithAttributes(): void
    {
        /**
         * Products
         */
        $this->addAttributesToSlot(
            'catalog.product.edit.cards',
            Product::class
        );

        $this->extendUpdateResponseBuilder(
            AdminProductUpdate::class,
            Product::class
        );

        /**
         * Categories
         */
        $this->addAttributesToSlot(
            'catalog.category.edit.cards',
            Category::class
        );

        $this->extendUpdateResponseBuilder(
            AdminCategoryUpdate::class,
            Category::class
        );

        /**
         * Pages
         */
        $this->addAttributesToSlot(
            'content.page.edit.cards',
            Page::class
        );

        $this->extendUpdateResponseBuilder(
            AdminPageUpdate::class,
            Page::class
        );

        /**
         * Shipping Methods
         */
        $this->addAttributesToSlot(
            'configuration.shipping-methods.edit.cards',
            ShippingMethod::class,
            'method'
        );

        $this->extendUpdateResponseBuilder(
            AdminShippingMethodUpdate::class,
            ShippingMethod::class,
            'method'
        );
    }

    private function addAttributesToSlot(string $slot, string $model, string $variableInView = null): void
    {
        if (null === $variableInView) {
            $variableInView = $model::RELATION_KEY;
        }

        if (!ClassHelper::classUsesTrait($model, CanHaveAdditionalAttributes::class)) {
            throw new \InvalidArgumentException("Expected \$model to have trait CanHaveAdditionalAttributes");
        }

        /** @var CanHaveAdditionalAttributes $model */

        AdminSlot::inject($slot, function(array $vars) use($model, $variableInView) : ? View
        {
            /** @var Model|CanHaveAdditionalAttributes $modelInstance */
            $modelInstance = $vars[$variableInView];

            /** @var RichAdditionalsService $ras */
            $ras = $this->app->get(RichAdditionalsService::class);

            $attributes = $ras->getAttributesForRelation($modelInstance::RELATION_KEY);

            if (count($attributes) < 1) {
                return null;
            }

            $modelInstance->load('additionals');

            foreach ($attributes as $attribute) {
                $attribute->injectAttributable($modelInstance);
            }

            return view('rich-additionals::admin/show-attributes', [
                'rich_attributes' => $attributes
            ]);
        });
    }

    private function extendUpdateResponseBuilder(string $builder, string $model, string $property = null): void
    {
        if (!is_subclass_of($builder, ResponseBuilder::class)) {
            throw new \InvalidArgumentException("Expected \$builder to be an instance of ResponseBuilder");
        }

        // Got something else...
        if (!ClassHelper::classUsesTrait($model, CanHaveAdditionalAttributes::class)) {
            throw new \InvalidArgumentException("Expected \$model to have trait CanHaveAdditionalAttributes");
        }

        if (null === $property) {
            $property = $model::RELATION_KEY;
        }

        /** @var CanHaveAdditionalAttributes|Model $model */

        /** @var ResponseBuilder|string $builder */

        $builder::extend(function(ResponseBuilder $builder, \Closure $next) use($property) {

            // Uhm, nope...
            if (!isset($builder->{$property})) {
                return $next($builder);
            }

            $modelInstance = $builder->{$property};

            // Got something else...
            if (!ClassHelper::classUsesTrait($modelInstance, CanHaveAdditionalAttributes::class)) {
                return $next($builder);
            }

            /** @var CanHaveAdditionalAttributes|Model $modelInstance */

            /** @var Request $request */
            $request = $builder->request;

            /** @var RichAdditionalsService $ras */
            $ras = $this->app->get(RichAdditionalsService::class);

            $attributes = $ras->getAttributesForRelation($modelInstance::RELATION_KEY);

            $validationRules = [];

            foreach ($attributes as $attribute) {
                $rules = $attribute->getValidationRules();

                if ($attribute->isRequired()) {
                    $rules[] = 'required';
                } else {
                    $rules[] = 'nullable';
                }

                if ($attribute->getType() === AttributeType::DROPDOWN) {
                    $options = collect($attribute->getOptions())->map(function($option, $key): string {
                        return $key;
                    })->values()->toArray();

                    if (!$attribute->isRequired()) {
                        $options[] = self::NULL_IDENTIFIER;
                    }

                    $rules[] = Rule::in($options);
                }

                $validationRules['rich-additionals.' . $attribute->getAttributeKey()] = $rules;
            }

            $validated = $request->validate($validationRules)['rich-additionals'] ?? [];

            foreach ($attributes as $attribute) {
                $value = $validated[$attribute->getAttributeKey()];

                if ($value === '') {
                    $value = null;
                }

                if ($value === self::NULL_IDENTIFIER) {
                    $value = null;
                }

                $existingValue = $modelInstance->additional(
                    $attribute->getAttributeKey()
                );

                if (!$existingValue && !$value) {
                    continue;
                }

                $modelInstance->additional(
                    $attribute->getAttributeKey(),
                    $value
                );
            }

            if ($modelInstance instanceof Product) {
                event(new ProductUpdated($modelInstance));
            }

            return $next($builder);
        });
    }
}

