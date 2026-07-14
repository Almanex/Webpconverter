<?php
/**
 * Scan images processor
 *
 * @package webpconverter
 * @subpackage processors
 */

class WebpConverterScanProcessor extends modProcessor
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
        // Increase execution times for scan operations
        set_time_limit(300);
        ini_set('max_execution_time', 300);

        $images = $this->webpconverter->scanImages();
        $cwebp = $this->webpconverter->getBinary();
        
        $cwebpName = is_array($cwebp) ? (isset($cwebp['status']) ? $cwebp['status'] : 'error') : $cwebp;

        return $this->outputArray([
            'images' => $images,
            'count' => count($images),
            'cwebp' => $cwebpName
        ]);
    }
}
return 'WebpConverterScanProcessor';
