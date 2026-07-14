<?php
/** @var MODX\Revolution\modX $modx */
$webpconverter = $modx->services->get('webpconverter');
if (!$webpconverter) {
    $corePath = $modx->getOption('core_path', null, MODX_CORE_PATH) . 'components/webpconverter/';
    if (file_exists($corePath . 'bootstrap.php')) {
        require_once $corePath . 'bootstrap.php';
        $webpconverter = $modx->services->get('webpconverter');
    }
}
if ($webpconverter && isset($modx->event)) {
    $webpconverter->handleEvent($modx->event);
}
