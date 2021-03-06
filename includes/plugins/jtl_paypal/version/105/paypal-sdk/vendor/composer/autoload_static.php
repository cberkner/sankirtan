<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit84a2fe4e6ba58fb1cf91c92b97d465a0
{
    public static $prefixesPsr0 = array (
        'P' => 
        array (
            'PayPal\\Types' => 
            array (
                0 => __DIR__ . '/..' . '/paypal/permissions-sdk-php/lib',
            ),
            'PayPal\\Service' => 
            array (
                0 => __DIR__ . '/..' . '/paypal/merchant-sdk-php/lib',
                1 => __DIR__ . '/..' . '/paypal/permissions-sdk-php/lib',
            ),
            'PayPal\\PayPalAPI' => 
            array (
                0 => __DIR__ . '/..' . '/paypal/merchant-sdk-php/lib',
            ),
            'PayPal\\EnhancedDataTypes' => 
            array (
                0 => __DIR__ . '/..' . '/paypal/merchant-sdk-php/lib',
            ),
            'PayPal\\EBLBaseComponents' => 
            array (
                0 => __DIR__ . '/..' . '/paypal/merchant-sdk-php/lib',
            ),
            'PayPal\\CoreComponentTypes' => 
            array (
                0 => __DIR__ . '/..' . '/paypal/merchant-sdk-php/lib',
            ),
            'PayPal' => 
            array (
                0 => __DIR__ . '/..' . '/paypal/rest-api-sdk-php/lib',
                1 => __DIR__ . '/..' . '/paypal/sdk-core-php/lib',
            ),
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixesPsr0 = ComposerStaticInit84a2fe4e6ba58fb1cf91c92b97d465a0::$prefixesPsr0;

        }, null, ClassLoader::class);
    }
}
