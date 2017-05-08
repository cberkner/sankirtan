<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit545ddf3deca188997ca395f507581968
{
    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'Psr\\Log\\' => 8,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Psr\\Log\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/log/Psr/Log',
        ),
    );

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
                0 => __DIR__ . '/..' . '/paypal/sdk-core-php/lib',
                1 => __DIR__ . '/..' . '/paypal/rest-api-sdk-php/lib',
            ),
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit545ddf3deca188997ca395f507581968::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit545ddf3deca188997ca395f507581968::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInit545ddf3deca188997ca395f507581968::$prefixesPsr0;

        }, null, ClassLoader::class);
    }
}
