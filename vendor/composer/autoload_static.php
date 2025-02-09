<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit6bf4a85ec06e2bef2312416526230475
{
    public static $prefixLengthsPsr4 = array (
        'C' => 
        array (
            'Core\\' => 5,
        ),
        'A' => 
        array (
            'App\\' => 4,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Core\\' => 
        array (
            0 => __DIR__ . '/../..' . '/Core',
        ),
        'App\\' => 
        array (
            0 => __DIR__ . '/../..' . '/app',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit6bf4a85ec06e2bef2312416526230475::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit6bf4a85ec06e2bef2312416526230475::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit6bf4a85ec06e2bef2312416526230475::$classMap;

        }, null, ClassLoader::class);
    }
}
