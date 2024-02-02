![Grizzlyware](https://github.com/grizzlyware/aero-rich-additionals/assets/1097093/7b976a18-8a69-469c-a4a2-9b878b936127)

# Rich Additionals for Aero Commerce

This package allows recording structured additional attributes against models in Aero Commerce.

![image](https://github.com/grizzlyware/aero-rich-additionals/assets/1097093/91085cfa-f563-40df-b423-cd2b08d809d8)

## Installation

You can install the package via Composer:

```bash
composer require grizzlyware/aero-rich-additionals
```

## Usage

Rich attributes can be added to the following models (so far!):

- Products
- Categories
- Pages
- Shipping Methods

Pass the model class to the `add` or helper methods, and then chain methods to configure the attribute.

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
        RichAdditionals::add('aisle_number', Product::class, AttributeType::DROPDOWN)
            ->setHelp('Where is this product located in the store?')
            ->setOptions(fn() => array_combine(range(50, 100), range(50, 100)))
        ;
    }
}
```



## Support

Please raise an issue on GitHub if you have any problems with this package.

Development of this package is sponsored by [Grizzlyware](https://www.grizzlyware.com).

Commercial support is available, please [contact us](https://www.grizzlyware.com/) for more information.

## Security

If you discover any security related issues, please email a maintainer of this project, and **do not raise an issue**.

