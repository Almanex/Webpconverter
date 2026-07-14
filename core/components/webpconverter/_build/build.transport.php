<?php
/**
 * WebpConverter build script
 *
 * @package webpconverter
 * @subpackage build
 */
$mtime = microtime();
$mtime = explode(' ', $mtime);
$mtime = $mtime[1] + $mtime[0];
$tstart = $mtime;
set_time_limit(0);

// Define package names
define('PKG_NAME', 'WebpConverter');
define('PKG_NAME_LOWER', 'webpconverter');
define('PKG_VERSION', '1.0.0');
define('PKG_RELEASE', 'pl');

// Paths
$root = dirname(__FILE__, 5) . '/'; // Points to MODX root
require_once $root . 'config.core.php';
require_once MODX_CORE_PATH . 'model/modx/modx.class.php';

$modx = new modX();
$modx->initialize('mgr');

$modx->loadClass('transport.modPackageBuilder', '', false, true);
$builder = new \MODX\Revolution\Transport\modPackageBuilder($modx);
$builder->createPackage(PKG_NAME_LOWER, PKG_VERSION, PKG_RELEASE);
$builder->registerNamespace(PKG_NAME_LOWER, false, true, '{core_path}components/' . PKG_NAME_LOWER . '/', '{assets_path}components/' . PKG_NAME_LOWER . '/');

// 1. Create Category
$category = $modx->newObject('MODX\Revolution\modCategory');
$category->set('id', 1);
$category->set('category', PKG_NAME);

// 2. Add Plugin
$plugin = $modx->newObject('MODX\Revolution\modPlugin');
$plugin->set('id', 1);
$plugin->set('name', PKG_NAME);
$plugin->set('description', 'Modern WebP image converter for MODX 3');
$plugin->set('static', 0);

$pluginFile = dirname(__FILE__, 2) . '/elements/plugins/WebpConverter.php';
if (!file_exists($pluginFile)) {
    $pluginFile = dirname(__FILE__, 4) . '/elements/plugins/WebpConverter.php';
}
if (file_exists($pluginFile)) {
    $pluginCode = file_get_contents($pluginFile);
    // Strip opening php tag if present
    if (strpos($pluginCode, '<?php') === 0) {
        $pluginCode = ltrim(substr($pluginCode, 5));
    }
    $plugin->set('plugincode', $pluginCode);
} else {
    $modx->log(xPDO::LOG_LEVEL_ERROR, 'Plugin source file not found at: ' . $pluginFile);
}

$category->addMany($plugin);

// Pack Category (adds plugin automatically)
$attr = [
    \xPDO\Transport\xPDOTransport::UNIQUE_KEY => 'category',
    \xPDO\Transport\xPDOTransport::PRESERVE_KEYS => false,
    \xPDO\Transport\xPDOTransport::UPDATE_OBJECT => true,
    \xPDO\Transport\xPDOTransport::RELATED_OBJECTS => true,
    \xPDO\Transport\xPDOTransport::RELATED_OBJECT_ATTRIBUTES => [
        'Plugins' => [
            \xPDO\Transport\xPDOTransport::PRESERVE_KEYS => false,
            \xPDO\Transport\xPDOTransport::UPDATE_OBJECT => true,
            \xPDO\Transport\xPDOTransport::UNIQUE_KEY => 'name',
        ]
    ]
];
$vehicle = $builder->createVehicle($category, $attr);

// Add file resolvers for core/ and assets/ folders!
$vehicle->resolve('file', [
    'source' => MODX_CORE_PATH . 'components/' . PKG_NAME_LOWER,
    'target' => "return MODX_CORE_PATH . 'components/';",
]);
$vehicle->resolve('file', [
    'source' => MODX_ASSETS_PATH . 'components/' . PKG_NAME_LOWER,
    'target' => "return MODX_ASSETS_PATH . 'components/';",
]);

// Add plugin events resolver
$vehicle->resolve('php', [
    'source' => dirname(__FILE__) . '/resolvers/resolve.events.php',
]);

$builder->putVehicle($vehicle);

// 3. System Settings
$settings = [
    'webpconverter.cwebp_params_jpeg' => '-metadata none -quiet -pass 10 -m 6 -mt -q 65 -low_memory',
    'webpconverter.cwebp_params_png' => '-metadata none -quiet -pass 10 -m 6 -alpha_q 85 -mt -alpha_filter best -alpha_method 1 -q 70 -low_memory',
    'webpconverter.exclude_dirs' => 'core,connectors,manager,webp,tmp,.git,vendor,node_modules',
    'webpconverter.disable_for_logged_user' => false
];

foreach ($settings as $key => $val) {
    $setting = $modx->newObject('MODX\Revolution\modSystemSetting');
    $setting->set('key', $key);
    $setting->set('value', $val);
    $setting->set('xtype', is_bool($val) ? 'combo-boolean' : 'textfield');
    $setting->set('namespace', PKG_NAME_LOWER);
    $setting->set('area', 'general');
    
    $vehicle = $builder->createVehicle($setting, [
        \xPDO\Transport\xPDOTransport::UNIQUE_KEY => 'key',
        \xPDO\Transport\xPDOTransport::PRESERVE_KEYS => true,
        \xPDO\Transport\xPDOTransport::UPDATE_OBJECT => false,
    ]);
    $builder->putVehicle($vehicle);
}

// 4. Register CMP Menu
$menu = $modx->newObject('MODX\Revolution\modMenu');
$menu->set('text', PKG_NAME_LOWER);
$menu->set('description', 'webpconverter.menu_desc');
$menu->set('parent', 'components');
$menu->set('action', 'home');
$menu->set('namespace', PKG_NAME_LOWER);

$vehicle = $builder->createVehicle($menu, [
    \xPDO\Transport\xPDOTransport::UNIQUE_KEY => 'text',
    \xPDO\Transport\xPDOTransport::PRESERVE_KEYS => true,
    \xPDO\Transport\xPDOTransport::UPDATE_OBJECT => true,
]);
$builder->putVehicle($vehicle);

// Build Package
$builder->pack();

$mtime = microtime();
$mtime = explode(' ', $mtime);
$mtime = $mtime[1] + $mtime[0];
$tend = $mtime;
$totalTime = ($tend - $tstart);
$totalTime = sprintf("%2.4f", $totalTime);
echo "\nPackage built successfully in {$totalTime} seconds.\n";
