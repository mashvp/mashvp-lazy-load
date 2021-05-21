<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit509249dadddbbe02938b53c89671b84d
{
    public static $prefixLengthsPsr4 = array (
        'k' => 
        array (
            'kornrunner\\Blurhash\\' => 20,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'kornrunner\\Blurhash\\' => 
        array (
            0 => __DIR__ . '/..' . '/kornrunner/blurhash/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit509249dadddbbe02938b53c89671b84d::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit509249dadddbbe02938b53c89671b84d::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit509249dadddbbe02938b53c89671b84d::$classMap;

        }, null, ClassLoader::class);
    }
}