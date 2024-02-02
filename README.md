# Rich Additionals for Aero Commerce

This package allows recording structured additional attributes against models in Aero Commerce.

## Installation

You can install the package via Composer:

```bash
composer require grizzlyware/aero-rich-additionals
```

## Usage

```php
<?php

namespace App\Providers;

use Aero\Admin\Facades\Admin;
use Aero\Catalog\Models\Product;
use App\Enums\PaperSize;
use Grizzlyware\Aero\RichAdditionals\AttributeType;
use Grizzlyware\Aero\RichAdditionals\Facades\RichAdditionals;
use Illuminate\Support\ServiceProvider;

class RichAdditionalsProvider extends ServiceProvider
{
    public function boot(): void
    {
        // For performance, only register the additional attributes when the admin panel is booted
        Admin::booted(
            $this->registerAdditionalAttributes(...)
        );
    }

    private function registerAdditionalAttributes(): void
    {
        // Options generated from an enum class
        RichAdditionals::enum(
            'paper_size',
            Product::class,
            PaperSize::class,
        );

        // Free text
        RichAdditionals::add('nickname', Product::class)
            ->setAttributeLabel('Nickname')
            ->setRequired()
            ->setHelp('A nickname for the product')
        ;

        // Manually defined options
        RichAdditionals::add('color', Product::class, AttributeType::DROPDOWN)
            ->setHelp('The color of the product')
            ->setOptions([
                'red' => 'Red',
                'green' => 'Green',
                'blue' => 'Blue',
            ])
        ;

        // Custom validation rules
        RichAdditionals::add('sales_email', Product::class)
            ->setHelp('Email address of the sales manager for this product')
            ->setValidationRules(['email'])
        ;

        // Generate options with a callback
        RichAdditionals::add('isle_number', Product::class, AttributeType::DROPDOWN)
            ->setHelp('Where is this product located in the store?')
            ->setOptions(fn() => array_combine(range(50, 100), range(50, 100)))
        ;
    }
}
```

