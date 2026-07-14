<?php
/**
 * WebpConverter connector
 *
 * @var modX $modx
 */

require_once dirname(__FILE__, 4) . '/config.core.php';
require_once MODX_CORE_PATH . 'config/' . MODX_CONFIG_KEY . '.inc.php';
require_once MODX_CONNECTORS_PATH . 'index.php';

// Get service from container
$webpconverter = null;
if (isset($modx->services) && $modx->services->has('webpconverter')) {
    $webpconverter = $modx->services->get('webpconverter');
}

if (!$webpconverter) {
    // Fallback if not loaded
    $corePath = $modx->getOption('core_path') . 'components/webpconverter/';
    if (file_exists($corePath . 'bootstrap.php')) {
        require_once $corePath . 'bootstrap.php';
        $webpconverter = $modx->services->get('webpconverter');
    }
}

if (!$webpconverter) {
    header("HTTP/1.1 500 Internal Server Error");
    die('WebpConverter core service not found.');
}

// Handle request
$modx->request->handleRequest(array(
    'processors_path' => $webpconverter->options['corePath'] . 'processors/',
    'location' => ''
));
