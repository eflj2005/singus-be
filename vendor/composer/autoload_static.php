<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit423558ac0387b9de71886af8f135a15d
{
    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'PHPMailer\\PHPMailer\\' => 20,
        ),
        'F' => 
        array (
            'Firebase\\JWT\\' => 13,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'PHPMailer\\PHPMailer\\' => 
        array (
            0 => __DIR__ . '/..' . '/phpmailer/phpmailer/src',
        ),
        'Firebase\\JWT\\' => 
        array (
            0 => __DIR__ . '/..' . '/firebase/php-jwt/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit423558ac0387b9de71886af8f135a15d::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit423558ac0387b9de71886af8f135a15d::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
