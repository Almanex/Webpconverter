<?php
/**
 * Clean orphaned webp files processor
 *
 * @package webpconverter
 * @subpackage processors
 */

class WebpConverterCleanProcessor extends modProcessor
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
        // Increase time limit for cleanup operations
        set_time_limit(300);
        ini_set('max_execution_time', 300);

        $this->webpconverter->cleanOrphans();
        return $this->success('Orphaned WebP files cleaned.');
    }
}
return 'WebpConverterCleanProcessor';
