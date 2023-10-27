<?php

// autoload_real.php @generated by Composer

class ComposerAutoloaderInite66901cd75168947f520a55f7f5cd143
{
    private static $loader;

    public static function loadClassLoader($class)
    {
        if ('Composer\Autoload\ClassLoader' === $class) {
            require __DIR__ . '/ClassLoader.php';
        }
    }

    /**
     * @return \Composer\Autoload\ClassLoader
     */
    public static function getLoader()
    {
        if (null !== self::$loader) {
            return self::$loader;
        }

        spl_autoload_register(array('ComposerAutoloaderInite66901cd75168947f520a55f7f5cd143', 'loadClassLoader'), true, true);
        self::$loader = $loader = new \Composer\Autoload\ClassLoader(\dirname(__DIR__));
        spl_autoload_unregister(array('ComposerAutoloaderInite66901cd75168947f520a55f7f5cd143', 'loadClassLoader'));

        require __DIR__ . '/autoload_static.php';
        call_user_func(\Composer\Autoload\ComposerStaticInite66901cd75168947f520a55f7f5cd143::getInitializer($loader));

        $loader->register(true);

        return $loader;
    }
}
