<?php
namespace WebpConverter;

use modX;
use xPDO;

class WebpConverter
{
    /** @var modX */
    public $modx;
    
    /** @var array */
    public $options = [];

    /** @var array */
    protected $uniqImgs = [];

    public function __construct(modX &$modx, array $options = [])
    {
        $this->modx =& $modx;
        
        $corePath = $modx->getOption('core_path') . 'components/webpconverter/';
        $assetsUrl = $modx->getOption('assets_url') . 'components/webpconverter/';
        $assetsPath = $modx->getOption('assets_path') . 'components/webpconverter/';

        $this->options = array_merge([
            'corePath' => $corePath,
            'assetsPath' => $assetsPath,
            'assetsUrl' => $assetsUrl,
            'connectorUrl' => $assetsUrl . 'connector.php',
            'webpDir' => MODX_BASE_PATH . 'webp/',
            
            // Compression parameters
            'cwebpParamsJpeg' => $modx->getOption('webpconverter.cwebp_params_jpeg', null, "-metadata none -quiet -pass 10 -m 6 -mt -q 65 -low_memory"),
            'cwebpParamsPng' => $modx->getOption('webpconverter.cwebp_params_png', null, "-metadata none -quiet -pass 10 -m 6 -alpha_q 85 -mt -alpha_filter best -alpha_method 1 -q 70 -low_memory"),
            
            // Exclude directories
            'excludeDirs' => explode(',', $modx->getOption('webpconverter.exclude_dirs', null, 'core/packages,tmp,manager,webp')),
            
            // Configurations
            'disableForLoggedUser' => (bool)$modx->getOption('webpconverter.disable_for_logged_user', null, false),
        ], $options);
    }

    /**
     * Get local option or namespaced system setting
     */
    public function getOption($key, $default = null)
    {
        if (array_key_exists($key, $this->options)) {
            return $this->options[$key];
        }
        return $this->modx->getOption('webpconverter.' . $key, null, $default);
    }

    /**
     * Dispatch events from the wrapper plugin
     */
    public function handleEvent($event)
    {
        switch ($event->name) {
            case 'OnManagerPageBeforeRender':
                // Injected in the manager to add Javascript control button or CMP load hooks
                // Note: If using CMP, this can be used to inject ExtJS assets or dashboard components.
                $this->modx->controller->addJavascript($this->options['assetsUrl'] . 'js/mgr/webpconverter.js');
                break;
                
            case 'OnSiteRefresh':
            case 'OnTemplateSave':
            case 'OnChunkSave':
            case 'OnPluginSave':
            case 'OnTemplateVarSave':
            case 'OnDocFormSave':
            case 'OnSnippetSave':
                $this->clearCache();
                break;
                
            case 'OnWebPagePrerender':
                if (stripos($_SERVER['HTTP_ACCEPT'], 'image/webp') !== false) {
                    if ($this->options['disableForLoggedUser'] && $this->modx->user->hasSessionContext('mgr')) {
                        break;
                    }
                    $this->replaceImagesInHtml();
                }
                break;
        }
    }

    /**
     * Clear Cache partition
     */
    public function clearCache()
    {
        $options = [xPDO::OPT_CACHE_KEY => 'webp_on_page'];
        $this->modx->cacheManager->clean($options);
    }

    /**
     * Convert relative path to absolute
     */
    public function rel2abs($rel, $base)
    {
        $parsed = parse_url($base);
        $scheme = isset($parsed['scheme']) ? $parsed['scheme'] : 'http';
        $host = isset($parsed['host']) ? $parsed['host'] : '';
        $path = isset($parsed['path']) ? $parsed['path'] : '';

        if (strpos($rel, "//") === 0) {
            return $scheme . ':' . $rel;
        }

        if (parse_url($rel, PHP_URL_SCHEME) != '') {
            return $rel;
        }

        if (isset($rel[0]) && ($rel[0] == '#' || $rel[0] == '?')) {
            return $base . $rel;
        }

        $path = preg_replace('#/[^/]*$#', '', $path);

        if (isset($rel[0]) && $rel[0] == '/') {
            $path = '';
        }

        $abs = $path . "/" . $rel;
        $abs = preg_replace("/(\/\.?\/)/", "/", $abs);
        $abs = preg_replace("/\/(?!\.\.)[^\/]+\/\.\.\//", "/", $abs);

        return $abs;
    }

    /**
     * Check if a WebP version exists and add to replacement array
     */
    public function checkImageFile($imgReal, &$webpOnPage)
    {
        $imgReal = trim($imgReal);
        if (in_array($imgReal, $this->uniqImgs)) {
            return;
        }
        $this->uniqImgs[] = $imgReal;

        // Strip query string for extension and path checks
        $parsedUrl = parse_url($imgReal);
        $cleanPath = isset($parsedUrl['path']) ? $parsedUrl['path'] : $imgReal;
        $query = isset($parsedUrl['query']) ? $parsedUrl['query'] : '';

        $ext = strtolower(pathinfo($cleanPath, PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
            $requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $abs = $this->rel2abs($cleanPath, MODX_SITE_URL . $requestPath);
            $absBase = str_replace('//', '/', MODX_BASE_PATH . $abs);

            $webp = '/webp' . $abs . '.webp' . ($query ? '?' . $query : '');
            $webpBase = str_replace('//', '/', MODX_BASE_PATH . '/webp' . $abs . '.webp');

            if (file_exists($absBase) && file_exists($webpBase)) {
                $webpOnPage[$imgReal] = $webp;
            }
        }
    }

    /**
     * Parse and replace JPG/PNG assets with WebP versions in the output HTML
     */
    public function replaceImagesInHtml()
    {
        if (empty($this->modx->resource) || empty($this->modx->resource->_output)) {
            return;
        }
        
        $options = [xPDO::OPT_CACHE_KEY => 'webp_on_page'];
        $cacheKey = md5(MODX_SITE_URL . $_SERVER['REQUEST_URI']);

        $cachedWebp = $this->modx->cacheManager->get($cacheKey, $options);
        $output =& $this->modx->resource->_output;

        if (empty($cachedWebp)) {
            $webpOnPage = [];
            $this->uniqImgs = [];

            preg_match_all('/<img[^>]+>/i', $output, $result);
            if (count($result) && isset($result[0])) {
                foreach ($result[0] as $imgTag) {
                    $imgTag = str_replace("'", '"', $imgTag);
                    
                    // src attribute
                    preg_match('/(src)=("[^"]*")/i', $imgTag, $matchSrc);
                    if (isset($matchSrc[2])) {
                        $imgReal = str_replace('"', '', $matchSrc[2]);
                        $this->checkImageFile($imgReal, $webpOnPage);
                    }

                    // data-src attribute
                    preg_match('/(data-src)=("[^"]*")/i', $imgTag, $matchDataSrc);
                    if (isset($matchDataSrc[2])) {
                        $imgReal = str_replace('"', '', $matchDataSrc[2]);
                        $this->checkImageFile($imgReal, $webpOnPage);
                    }

                    // srcset attribute
                    preg_match('/(srcset)=("[^"]*")/i', $imgTag, $matchSrcset);
                    if (isset($matchSrcset[2])) {
                        $srcset = explode(',', str_replace('"', '', $matchSrcset[2]));
                        foreach ($srcset as $srcItem) {
                            $srcArray = explode(' ', trim($srcItem));
                            if (isset($srcArray[0]) && !empty($srcArray[0])) {
                                $this->checkImageFile($srcArray[0], $webpOnPage);
                            }
                        }
                    }
                }
            }

            // CSS inline background images url(...)
            preg_match_all('/url\(([^)]*)"?\)/iu', $output, $resultCss);
            if (count($resultCss) && isset($resultCss[1])) {
                foreach ($resultCss[1] as $urlTag) {
                    if (stripos($urlTag, 'data:') === 0) {
                        continue;
                    }
                    $imgReal = str_replace(['"', "'"], '', $urlTag);
                    $this->checkImageFile($imgReal, $webpOnPage);
                }
            }

            $webpOnPage['/webp/webp/'] = '/webp/';
            $webpOnPage['//webp/'] = '/webp/';
            $webpOnPage['.webp.webp'] = '.webp';

            if (count($webpOnPage)) {
                $output = str_replace(array_keys($webpOnPage), array_values($webpOnPage), $output);
            }
            
            $this->modx->cacheManager->set($cacheKey, serialize($webpOnPage), 0, $options);
        } else {
            $webpOnPage = unserialize($cachedWebp);
            if (count($webpOnPage)) {
                $output = str_replace(array_keys($webpOnPage), array_values($webpOnPage), $output);
            }
        }
    }

    /**
     * Detect system environment and select cwebp binary
     */
    public function getBinary()
    {
        $suppliedBinaries = [
            'winnt' => 'cwebp-110-windows-x64.exe',
            'darwin' => 'cwebp-110-mac-10_15',
            'sunos' => 'cwebp-060-solaris',
            'freebsd' => 'cwebp-060-fbsd',
            'linux' => [
                'cwebp-110-linux-x86-64',
                'cwebp-103-linux-x86-64-static',
                'cwebp-061-linux-x86-64'
            ]
        ];

        $gdSupport = $this->checkGd();
        $gd = ($gdSupport !== false && 
               $gdSupport['WebP Support'] == 1 && 
               $gdSupport['WebP Alpha Channel Support'] == 1 && 
               $gdSupport['PNG Support'] == 1);

        // Check for exec support
        $disabledFunctions = explode(",", str_replace(" ", "", @ini_get("disable_functions")));
        if (!is_callable("exec") || in_array("exec", $disabledFunctions)) {
            if ($gd) return "gd";
            return ['status' => 'Exec function disabled!'];
        }

        $cwebpPath = $this->options['corePath'] . 'Binaries/';
        $os = strtolower(PHP_OS);
        
        if (!isset($suppliedBinaries[$os])) {
            if ($gd) return "gd";
            return ['status' => 'Bin file for: ' . PHP_OS . ' not found in /core/components/webpconverter/Binaries/'];
        }

        $bin = $suppliedBinaries[$os];
        $cwebp = null;
        $return_var = 255;
        $output = [];

        if (is_array($bin)) {
            foreach ($bin as $b) {
                if (is_file($cwebpPath . $b)) {
                    if (!is_executable($cwebpPath . $b)) {
                        chmod($cwebpPath . $b, 0755);
                    }
                    $output[] = $cwebpPath . $b;
                    exec($cwebpPath . $b . ' 2>&1', $output, $return_var);
                    if ($return_var == 0) {
                        $cwebp = $b;
                        break;
                    }
                }
            }
        } else {
            if (is_file($cwebpPath . $bin)) {
                if ($os != 'winnt' && !is_executable($cwebpPath . $bin)) {
                    chmod($cwebpPath . $bin, 0755);
                }
                $output[] = $cwebpPath . $bin;
                exec($cwebpPath . $bin . ' 2>&1', $output, $return_var);
                if ($return_var == 0) {
                    $cwebp = $bin;
                }
            }
        }

        if (!$cwebp) {
            if ($os == 'linux' && is_file('/usr/bin/cwebp')) {
                $output[] = '/usr/bin/cwebp';
                exec('/usr/bin/cwebp 2>&1', $output, $return_var);
                if ($return_var == 0) {
                    return 'system';
                }
            }

            if ($gd) return "gd";
            return [
                'status' => 'Bin file not work! return code: ' . $return_var,
                'output' => $output,
                'return_var' => $return_var
            ];
        }

        return $cwebp;
    }

    /**
     * Check GD installation for WebP support
     */
    public function checkGd()
    {
        if (extension_loaded('gd') && function_exists('gd_info')) {
            $gd = gd_info();
            if (!in_array('GD Version', $gd)) {
                $gd['GD Version'] = '0.0.0';
            }
            preg_match('/\\d+\\.\\d+(?:\\.\\d+)?/', $gd['GD Version'], $matches);
            $gd['Ver'] = isset($matches[0]) ? $matches[0] : '0.0.0';
            
            $gd['WebP Alpha Channel Support'] = (version_compare($gd['Ver'], '2.2.5') >= 0) ? 1 : 0;
            
            if (!in_array('WebP Support', $gd)) $gd['WebP Support'] = 0;
            if (!in_array('JPEG Support', $gd)) $gd['JPEG Support'] = 0;
            if (!in_array('PNG Support', $gd)) $gd['PNG Support'] = 0;
            
            return $gd;
        }
        return false;
    }

    /**
     * GD image conversion fallback
     */
    public function gdConvert($source, $dest)
    {
        $imagetype = $this->getImageType($source);
        switch ($imagetype) {
            case IMAGETYPE_JPEG:
                $img = imagecreatefromjpeg($source);
                break;
            case IMAGETYPE_PNG:
                $img = imagecreatefrompng($source);
                break;
            default:
                return false;
        }

        if (!$img) {
            return false;
        }

        $success = imageWebp($img, $dest, 80);
        imagedestroy($img);
                    
        if ($success && filesize($dest) % 2 == 1) {
            file_put_contents($dest, "\0", FILE_APPEND);
        }
                    
        return $success ? 0 : false;
    }

    /**
     * Convert single image to WebP
     */
    public function convertImage($file, $cwebpBin)
    {
        $file = '/' . ltrim($file, '/');

        $validBinaries = [
            'cwebp-110-windows-x64.exe',
            'cwebp-110-mac-10_15',
            'cwebp-060-solaris',
            'cwebp-060-fbsd',
            'cwebp-110-linux-x86-64',
            'cwebp-103-linux-x86-64-static',
            'cwebp-061-linux-x86-64',
            'system',
            'gd'
        ];

        if (empty($cwebpBin) || !in_array($cwebpBin, $validBinaries)) {
            $detected = $this->getBinary();
            if (is_array($detected)) {
                return [
                    'status' => 'error',
                    'message' => 'No working conversion method found: ' . ($detected['status'] ?? 'unknown')
                ];
            }
            $cwebpBin = $detected;
        }

        if ($cwebpBin === 'system') {
            $cwebp = '/usr/bin/cwebp';
        } else {
            $cwebp = $this->options['corePath'] . 'Binaries/' . $cwebpBin;
        }

        $dest = rtrim(MODX_BASE_PATH, '/') . '/webp' . $file . '.webp';
        $source = rtrim(MODX_BASE_PATH, '/') . $file;
        
        if (!is_file($source)) {
            return ['status' => 'error', 'message' => 'Source file not found: ' . $source];
        }

        if (!is_dir(dirname($dest))) {
            mkdir(dirname($dest), 0755, true);
        }

        $imagetype = $this->getImageType($source);
        if ($imagetype != IMAGETYPE_JPEG && $imagetype != IMAGETYPE_PNG) {
            return ['status' => 'error', 'message' => 'Not supported format: ' . $file];
        }

        if (is_file($dest)) {
            if (filemtime($dest) > filemtime($source)) {
                return ['status' => 'success', 'message' => 'File already converted: ' . $file];
            }
            unlink($dest);
        }

        ignore_user_abort(true);
        $return_var = 255;
        $output = [];
        $gdSupport = $this->checkGd();

        if ($imagetype == IMAGETYPE_JPEG) {
            if ($cwebpBin !== 'gd') {
                exec($cwebp . ' ' . $this->options['cwebpParamsJpeg'] . ' "' . $source . '" -o "' . $dest . '" 2>&1', $output, $return_var);
            } else {
                $return_var = 1;
            }
            
            if ($return_var != 0 && $gdSupport && $gdSupport['WebP Support'] && $gdSupport['JPEG Support']) {
                $converted = $this->gdConvert($source, $dest);
                $return_var = ($converted === 0) ? 0 : 1;
                if ($return_var == 0) $output[] = "Fallback to GD conversion successful.";
            }
        } else if ($imagetype == IMAGETYPE_PNG) {
            if ($cwebpBin !== 'gd') {
                exec($cwebp . ' ' . $this->options['cwebpParamsPng'] . ' "' . $source . '" -o "' . $dest . '" 2>&1', $output, $return_var);
            } else {
                $return_var = 1;
            }
            
            if ($return_var != 0 && $gdSupport && $gdSupport['WebP Support'] && $gdSupport['WebP Alpha Channel Support'] && $gdSupport['PNG Support']) {
                $converted = $this->gdConvert($source, $dest);
                $return_var = ($converted === 0) ? 0 : 1;
                if ($return_var == 0) $output[] = "Fallback to GD conversion successful.";
            }
        }

        if ($return_var == 0 && is_file($dest)) {
            return [
                'status' => 'success',
                'source' => $file,
                'dest' => '/webp' . $file . '.webp',
                'output' => $output
            ];
        }

        return [
            'status' => 'error',
            'message' => 'Conversion failed for: ' . $file,
            'output' => $output,
            'return_var' => $return_var
        ];
    }

    /**
     * Recursively scan directory for images
     */
    /**
     * Recursively scan directory for images
     */
    public function scanImages()
    {
        $images = [];
        $basePath = rtrim(str_replace(['/', '\\'], '/', MODX_BASE_PATH), '/');
        $this->recursiveScan($basePath, $images);
        return $images;
    }

    protected function recursiveScan($dir, &$images)
    {
        $dir = rtrim(str_replace(['/', '\\'], '/', $dir), '/');
        $basePath = rtrim(str_replace(['/', '\\'], '/', MODX_BASE_PATH), '/');
        $relativeDir = trim(str_replace($basePath, '', $dir), '/');
        
        if (!empty($relativeDir)) {
            $segments = explode('/', $relativeDir);
            foreach ($segments as $segment) {
                $segmentLower = strtolower($segment);
                if (in_array($segment, $this->options['excludeDirs']) || strpos($segmentLower, 'modx-') !== false) {
                    return;
                }
            }
        }

        $odir = opendir($dir);
        if (!$odir) return;

        while (($file = readdir($odir)) !== false) {
            if ($file == '.' || $file == '..') {
                continue;
            }

            $fullPath = $dir . '/' . $file;
            if (is_dir($fullPath)) {
                $fileLower = strtolower($file);
                if (in_array($file, $this->options['excludeDirs']) || strpos($fileLower, 'modx-') !== false) {
                    continue;
                }
                $this->recursiveScan($fullPath, $images);
            } else {
                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
                    $img = $relativeDir ? $relativeDir . '/' . $file : $file;
                    $dest = $basePath . '/webp/' . $img . '.webp';

                    if (is_file($dest) && filemtime($fullPath) < filemtime($dest)) {
                        continue;
                    }
                    $images[] = $img;
                }
            }
        }
        closedir($odir);
    }

    /**
     * Recursively clean orphaned WebP images
     */
    public function cleanOrphans()
    {
        $webpDir = MODX_BASE_PATH . 'webp';
        if (!is_dir($webpDir)) {
            return;
        }

        $this->recursiveClean($webpDir);
        $this->recursiveRemoveEmptyDirs($webpDir);
        $this->clearCache();
        $this->getStats(true);
    }

    protected function recursiveClean($dir)
    {
        $odir = opendir($dir);
        if (!$odir) return;

        while (($file = readdir($odir)) !== false) {
            if ($file == '.' || $file == '..') continue;
            
            $fullPath = $dir . DIRECTORY_SEPARATOR . $file;
            if (is_dir($fullPath)) {
                $this->recursiveClean($fullPath);
            } else {
                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                if ($ext === 'webp') {
                    // Reconstruct original file path
                    // WebP path: MODX_BASE_PATH/webp/path/to/img.png.webp
                    // Original path: MODX_BASE_PATH/path/to/img.png
                    $relative = str_replace(MODX_BASE_PATH . 'webp' . DIRECTORY_SEPARATOR, '', $fullPath);
                    $original = MODX_BASE_PATH . substr($relative, 0, -5); // strip '.webp'

                    if (!is_file($original)) {
                        unlink($fullPath);
                    }
                }
            }
        }
        closedir($odir);
    }

    protected function recursiveRemoveEmptyDirs($dir)
    {
        $odir = opendir($dir);
        if (!$odir) return 0;
        
        $countFiles = 0;
        while (($file = readdir($odir)) !== false) {
            if ($file == '.' || $file == '..') continue;
            $countFiles++;
            
            $fullPath = $dir . DIRECTORY_SEPARATOR . $file;
            if (is_dir($fullPath)) {
                $countFiles += $this->recursiveRemoveEmptyDirs($fullPath);
            }
        }
        closedir($odir);

        if ($countFiles == 0) {
            rmdir($dir);
            return -1;
        }
        return $countFiles;
    }

    /**
     * Helper to get image type without exif extension dependency
     */
    public function getImageType($source)
    {
        if (function_exists('exif_imagetype')) {
            return @exif_imagetype($source);
        }
        $info = @getimagesize($source);
        return $info ? $info[2] : false;
    }

    /**
     * Get WebP compression stats
     */
    public function getStats($force = false)
    {
        $options = [\xPDO\xPDO::OPT_CACHE_KEY => 'webpconverter'];
        $cacheKey = 'stats';
        if (!$force) {
            $stats = $this->modx->cacheManager->get($cacheKey, $options);
            if (!empty($stats)) {
                return $stats;
            }
        }

        $originalCount = 0;
        $webpCount = 0;
        $originalSize = 0;
        $webpSize = 0;

        $this->recursiveScanStats(MODX_BASE_PATH, $originalCount, $webpCount, $originalSize, $webpSize);

        $savedBytes = max(0, $originalSize - $webpSize);
        $percentSaved = $originalSize > 0 ? round(($savedBytes / $originalSize) * 100) : 0;

        $stats = [
            'original_count' => $originalCount,
            'webp_count' => $webpCount,
            'original_size' => $originalSize,
            'webp_size' => $webpSize,
            'saved_bytes' => $savedBytes,
            'percent_saved' => $percentSaved,
            'timestamp' => time()
        ];

        $this->modx->cacheManager->set($cacheKey, $stats, 86400, $options);
        return $stats;
    }

    protected function recursiveScanStats($dir, &$originalCount, &$webpCount, &$originalSize, &$webpSize)
    {
        $dir = rtrim(str_replace(['/', '\\'], '/', $dir), '/');
        $basePath = rtrim(str_replace(['/', '\\'], '/', MODX_BASE_PATH), '/');
        $relativeDir = trim(str_replace($basePath, '', $dir), '/');
        
        if (!empty($relativeDir)) {
            $segments = explode('/', $relativeDir);
            foreach ($segments as $segment) {
                $segmentLower = strtolower($segment);
                if (in_array($segment, $this->options['excludeDirs']) || strpos($segmentLower, 'modx-') !== false) {
                    return;
                }
            }
        }

        $odir = opendir($dir);
        if (!$odir) return;

        while (($file = readdir($odir)) !== false) {
            if ($file == '.' || $file == '..') {
                continue;
            }

            $fullPath = $dir . '/' . $file;
            if (is_dir($fullPath)) {
                $fileLower = strtolower($file);
                if (in_array($file, $this->options['excludeDirs']) || strpos($fileLower, 'modx-') !== false) {
                    continue;
                }
                $this->recursiveScanStats($fullPath, $originalCount, $webpCount, $originalSize, $webpSize);
            } else {
                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
                    $originalCount++;
                    $originalSize += @filesize($fullPath);

                    $img = $relativeDir ? $relativeDir . '/' . $file : $file;
                    $dest = $basePath . '/webp/' . $img . '.webp';
                    if (is_file($dest)) {
                        $webpCount++;
                        $webpSize += @filesize($dest);
                    }
                }
            }
        }
        closedir($odir);
    }
}
