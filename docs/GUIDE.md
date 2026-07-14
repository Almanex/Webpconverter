# User Guide: WebpConverter Component for MODX 3

The **WebpConverter** component allows you to automatically speed up the loading time of your MODX 3 website by converting images (JPEG, PNG) into the modern, lightweight **WebP** format. The plugin automatically replaces image links in the HTML output on the fly, serving optimized versions to users.

---

## 1. Installation

1. Log in to your MODX manager panel (administration area).
2. Navigate to **Apps** -> **Package Management**.
3. Click the **Download Extras** or upload dropdown and select your precompiled transport package: `webpconverter-1.0.0-pl.transport.zip`.
4. Click **Install** next to the loaded package and follow the on-screen instructions.

After installation, the following components will be created in your system:
- A system plugin that replaces image links with `.webp` on the fly.
- A **WEBP Converter** menu item in the **Apps** (Extras) section.
- A **WEBP Optimization** widget on the main dashboard page.

---

## 2. System Settings

The component settings are located under **System Settings** (gear icon in the top right corner -> System Settings) inside the `webpconverter` namespace.

### Available Parameters:

1. **`webpconverter.cwebp_params_jpeg`** (JPEG compression settings)
   - **Default Value**: `-metadata none -quiet -pass 10 -m 6 -mt -q 65 -low_memory`
   - **Description**: Command-line arguments for the `cwebp` utility used during JPEG compression.
     - `-metadata none` — strips all EXIF/IPTC metadata to reduce file size.
     - `-quiet` — disables verbose output logging.
     - `-pass 10` — number of passes for optimizing entropy (max 10).
     - `-m 6` — compression method (0-6, where 6 provides best compression but takes more CPU time).
     - `-mt` — enables multi-threading to speed up processing.
     - `-q 65` — compression quality (scale 0-100, where 65 is the optimal balance of quality and weight for JPEG).
     - `-low_memory` — optimizes memory usage on the server.

2. **`webpconverter.cwebp_params_png`** (PNG compression settings)
   - **Default Value**: `-metadata none -quiet -pass 10 -m 6 -alpha_q 85 -mt -alpha_filter best -alpha_method 1 -q 70 -low_memory`
   - **Description**: Command-line arguments for compressing PNG files with transparency support.
     - `-alpha_q 85` — quality of alpha channel compression.
     - `-alpha_filter best` — alpha filtering algorithm.
     - `-alpha_method 1` — alpha compression method.
     - `-q 70` — compression quality for main PNG colors.

3. **`webpconverter.exclude_dirs`** (Excluded directories)
   - **Default Value**: `core,connectors,manager,webp,tmp,.git,vendor,node_modules`
   - **Description**: Comma-separated list of folders (relative to the site root) that the image scanner should completely ignore. It is highly recommended to exclude system directories and build folders to avoid unnecessary server load.

4. **`webpconverter.disable_for_logged_user`** (Disable for authorized users)
   - **Default Value**: `No` (false)
   - **Description**: If set to `Yes` (true), the plugin will not rewrite image paths to `.webp` for administrators and managers logged into the MODX manager. This is useful for testing original layouts or debugging stylesheet integration.

---

## 3. Usage

### Custom Manager Page (CMP Interface)

Go to **Apps** -> **WEBP Converter**. The control panel provides the following actions:

1. **Scan Site**:
   - Click the **Scan Site** button to search for all images on the server. The scanner will find all JPG, JPEG, and PNG files, except those located in the excluded folders.
   - A live progress indicator will show current scanning status.
2. **Convert Files**:
   - Click the **Convert Files** button to start generating `.webp` versions of the discovered images.
   - Conversion runs in batches (defaulting to 10 files per request) to prevent server script timeouts. The progress bar shows the completion percentage.
3. **Clean Orphaned Files**:
   - Click the **Clean Orphaned Files** button to delete `.webp` files whose original JPEGs or PNGs have been deleted (e.g. after removing images via the media manager or gallery). This helps reclaim server disk space.

### Dashboard Widget

On the main dashboard page, the **WEBP Optimization** widget displays live statistics:
- Total images found on the site.
- Count of already optimized images.
- Current optimization progress as a percentage.
- Total disk space saved (in MB or KB).
- **Refresh** button to recalculate statistics instantly.
- **Go to Converter** button to quickly navigate to the CMP.
