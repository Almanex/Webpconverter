<?php
/**
 * Convert image processor
 *
 * @package webpconverter
 * @subpackage processors
 */

class WebpConverterConvertProcessor extends modProcessor
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
        $file = $this->getProperty('file');
        $cwebp = $this->getProperty('cwebp');

        if (empty($file)) {
            return $this->failure('No file specified.');
        }

        $result = $this->webpconverter->convertImage($file, $cwebp);
        if ($result['status'] === 'success') {
            return $this->success($result['message'] ?? 'Image converted successfully.', $result);
        } else {
            return $this->failure($result['message'] ?? 'Conversion failed.', $result);
        }
    }
}
return 'WebpConverterConvertProcessor';
