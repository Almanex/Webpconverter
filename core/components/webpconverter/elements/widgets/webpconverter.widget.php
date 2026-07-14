<?php
use MODX\Revolution\modDashboardWidgetInterface;

class WebpConverterDashboardWidget extends modDashboardWidgetInterface
{
    public function render()
    {
        $webpconverter = $this->modx->services->get('webpconverter');
        if (!$webpconverter) {
            return '<p style="color:red; padding: 15px;">WebpConverter service not loaded.</p>';
        }

        // Get cached stats
        $stats = $webpconverter->getStats(false);
        
        $originalCount = isset($stats['original_count']) ? $stats['original_count'] : 0;
        $webpCount = isset($stats['webp_count']) ? $stats['webp_count'] : 0;
        $originalSize = isset($stats['original_size']) ? $stats['original_size'] : 0;
        $webpSize = isset($stats['webp_size']) ? $stats['webp_size'] : 0;
        $savedBytes = isset($stats['saved_bytes']) ? $stats['saved_bytes'] : 0;
        $percentSaved = isset($stats['percent_saved']) ? $stats['percent_saved'] : 0;
        
        $origFormatted = $this->formatBytes($originalSize);
        $webpFormatted = $this->formatBytes($webpSize);
        $savedFormatted = $this->formatBytes($savedBytes);
        
        $timestamp = isset($stats['timestamp']) ? $stats['timestamp'] : time();
        $updatedTime = date('d.m.Y H:i:s', $timestamp);
        
        $managerUrl = $this->modx->getOption('manager_url', null, MODX_MANAGER_URL);
        $cmpUrl = $managerUrl . '?a=home&namespace=webpconverter';
        $connectorUrl = $webpconverter->options['assetsUrl'] . 'connector.php';

        $html = '
        <div class="webpconverter-widget-wrapper" style="padding: 15px; background: #fff; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, sans-serif;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; border-bottom: 1px solid #f0f0f0; padding-bottom: 15px;">
                <div style="display: flex; align-items: center;">
                    <div style="background: #e8f5e9; color: #2e7d32; padding: 10px; border-radius: 50%; font-size: 20px; font-weight: bold; margin-right: 15px; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; box-shadow: inset 0 2px 4px rgba(0,0,0,0.05);">
                        W
                    </div>
                    <div>
                        <h4 style="margin: 0; font-size: 16px; color: #2c3e50; font-weight: 600;">WEBP Оптимизация</h4>
                        <span style="font-size: 12px; color: #7f8c8d;">Сжатие изображений на лету</span>
                    </div>
                </div>
                <div style="text-align: right;">
                    <span style="font-size: 24px; font-weight: 700; color: #2e7d32; display: block;">-' . $percentSaved . '%</span>
                    <span style="font-size: 11px; color: #7f8c8d; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px;">Сэкономлено</span>
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
                <div style="background: #fafafa; padding: 12px; border-radius: 6px; border: 1px solid #eaeaea;">
                    <span style="font-size: 11px; color: #95a5a6; text-transform: uppercase; font-weight: 600;">Файлы (WebP / Всего)</span>
                    <div style="font-size: 18px; font-weight: 700; color: #34495e; margin-top: 5px;">' . $webpCount . ' / ' . $originalCount . '</div>
                </div>
                <div style="background: #fafafa; padding: 12px; border-radius: 6px; border: 1px solid #eaeaea;">
                    <span style="font-size: 11px; color: #95a5a6; text-transform: uppercase; font-weight: 600;">Сжатый объем</span>
                    <div style="font-size: 18px; font-weight: 700; color: #34495e; margin-top: 5px;">' . $webpFormatted . ' <span style="font-size: 12px; font-weight: normal; color: #7f8c8d;">из ' . $origFormatted . '</span></div>
                </div>
            </div>

            <div style="display: flex; gap: 10px;">
                <a href="' . $cmpUrl . '" class="x-btn x-btn-small x-btn-icon-horizontal x-btn-noicon primary-button" style="flex: 1; text-align: center; text-decoration: none; padding: 8px 14px; font-size: 13px; font-weight: 600; line-height: 1.5; border-radius: 4px; display: block; border: 1px solid #2e7d32; background: #2e7d32; color: #fff;">
                    Перейти в панель WebP
                </a>
                <button onclick="refreshWebpWidgetStats(this)" class="x-btn x-btn-small x-btn-icon-horizontal x-btn-noicon" style="flex: 1; padding: 8px 14px; font-size: 13px; border-radius: 4px; cursor: pointer; border: 1px solid #ccc; background: #fff; color: #333; font-weight: 600; transition: all 0.2s;">
                    Обновить данные
                </button>
            </div>
            
            <div style="font-size: 11px; color: #95a5a6; text-align: center; margin-top: 12px;">
                Последнее обновление: ' . $updatedTime . '
            </div>

            <script type="text/javascript">
                function refreshWebpWidgetStats(btn) {
                    var oldText = btn.innerHTML;
                    btn.disabled = true;
                    btn.innerHTML = "Обновление...";
                    
                    MODx.Ajax.request({
                        url: "' . $connectorUrl . '",
                        params: {
                            action: "stats",
                            refresh: 1
                        },
                        listeners: {
                            success: {
                                fn: function(r) {
                                    btn.disabled = false;
                                    btn.innerHTML = oldText;
                                    location.reload();
                                },
                                scope: this
                            },
                            failure: {
                                fn: function(r) {
                                    btn.disabled = false;
                                    btn.innerHTML = "Ошибка!";
                                    setTimeout(function() { btn.innerHTML = oldText; }, 2000);
                                },
                                scope: this
                            }
                        }
                    });
                }
            </script>
        </div>';

        return $html;
    }

    protected function formatBytes($bytes, $precision = 2)
    {
        if ($bytes <= 0) return '0 B';
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
return 'WebpConverterDashboardWidget';
