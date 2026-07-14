<?php
/**
 * Get WebP optimization stats processor
 *
 * @package webpconverter
 * @subpackage processors
 */

class WebpConverterStatsProcessor extends modProcessor
{
    /** @var \WebpConverter\WebpConverter */
    public $webpconverter;

    public function initialize()
    {
        $this->webpconverter = $this->modx->services->get('webpconverter');
        if (!$this->webpconverter) {
            return 'WebpConverter service not loaded.';
        }
        return parent::initialize();
    }

    public function process()
    {
        $refresh = (bool)$this->getProperty('refresh', false);
        $stats = $this->webpconverter->getStats($refresh);
        
        return $this->outputArray($stats);
    }
}
return 'WebpConverterStatsProcessor';
