<?php
$_lang['setting_webpconverter.cwebp_params_jpeg'] = 'JPEG compression parameters (cwebp)';
$_lang['setting_webpconverter.cwebp_params_jpeg_desc'] = 'Command line options for the cwebp tool used when converting JPEG images. Example: -metadata none -quiet -pass 10 -m 6 -mt -q 65 -low_memory';

$_lang['setting_webpconverter.cwebp_params_png'] = 'PNG compression parameters (cwebp)';
$_lang['setting_webpconverter.cwebp_params_png_desc'] = 'Command line options for the cwebp tool used when converting PNG images. Example: -metadata none -quiet -pass 10 -m 6 -alpha_q 85 -mt -alpha_filter best -alpha_method 1 -q 70 -low_memory';

$_lang['setting_webpconverter.exclude_dirs'] = 'Excluded directories';
$_lang['setting_webpconverter.exclude_dirs_desc'] = 'Comma-separated list of directories (relative to site root) that the image scanner should ignore.';

$_lang['setting_webpconverter.disable_for_logged_user'] = 'Disable for logged-in users';
$_lang['setting_webpconverter.disable_for_logged_user_desc'] = 'If enabled, the plugin will not replace image paths with WebP for users logged into the manager panel.';
