<?php
/** @var MODX\Revolution\modX $modx */

// 1. Try to register with Composer ClassLoader for modern PSR-4 autoloading
if (class_exists(\Composer\Autoload\ClassLoader::class)) {
    $loader = null;
    if (isset($modx) && method_exists($modx->services, 'has') && $modx->services->has('composer_loader')) {
        $loader = $modx->services->get('composer_loader');
    }
    if (!$loader) {
        // Attempt to find loader from composer vendor autoload
        foreach (get_declared_classes() as $class) {
            if (strpos($class, 'ComposerAutoloaderInit') === 0 && method_exists($class, 'getLoader')) {
                $loader = $class::getLoader();
                break;
            }
        }
    }
    if ($loader instanceof \Composer\Autoload\ClassLoader) {
        $loader->addPsr4('WebpConverter\\', __DIR__ . '/src/');
    }
}

// 2. Register custom PSR-4 fallback autoloader just in case Composer ClassLoader is not bound
spl_autoload_register(function ($class) {
    $prefix = 'WebpConverter\\';
    $base_dir = __DIR__ . '/src/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

// 3. Register webpconverter service in MODX 3 DI container
if (isset($modx) && method_exists($modx->services, 'add')) {
    $modx->services->add('webpconverter', function($c) use ($modx) {
        return new \WebpConverter\WebpConverter($modx);
    });
}
