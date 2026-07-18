# WebpConverter User Guide — How to Optimize Images in MODX 3

> [!NOTE]
> **💡 Summary:** WebpConverter is a ready-to-use component for MODX 3 that automatically compresses and converts JPEG/PNG images to the modern, lightweight WebP format on the fly using the `cwebp` utility. This helps you boost your Google PageSpeed scores, provides a clear dashboard widget showing disk space savings, and offers a manager page to run bulk scan, convert, and orphaned file cleanup operations.

---

## Application Overview

Web technologies are evolving rapidly, and website loading speed remains a critical factor for SEO search rankings. The **WebpConverter** component is specifically designed for MODX 3 to accelerate page load times by optimizing graphic assets. It automatically handles the conversion of JPG, JPEG, and PNG images into the next-generation **WebP** format. Integrated via a native system plugin, the component swaps image source paths dynamically in the HTML markup, remaining completely invisible to the site administrator and visitors.

---

## Component Features

### On-the-Fly Image Path Replacement
Once installed and enabled, the plugin hooks into the `OnWebPagePrerender` event. It parses the prerendered HTML output and dynamically updates links inside `<img>` tags, `srcset` attributes, and inline CSS styles to target the newly generated `.webp` files.

### Site Scanning and Tracking
The scanner utility recursively searches the site's directories for original images, skipping system folders and custom exclusions. It populates a database table to keep track of conversion statuses across the entire site.

### Queue-Based Batch Conversion
Compression is executed in controlled batches. You can configure the queue size to stay within PHP script execution limits (timeouts) and avoid overloading the server's CPU when processing hundreds of files.

### Orphaned File Cleanup
When you delete an original image via the MODX media manager, its `.webp` counterpart is no longer needed. The cleanup tool scans the server for these orphaned `.webp` files and safely removes them to reclaim disk space.

---

## Step-by-Step Installation and Setup

Follow these steps to set up and run the component on your website:

1. **Step 1: Download the Package** — Download the precompiled transport archive `webpconverter-1.0.0-pl.transport.zip` from the latest release on the GitHub repository.
2. **Step 2: Upload to MODX** — Log in to your MODX 3 manager dashboard, navigate to **Apps** -> **Package Management**, click the dropdown and choose to upload your ZIP package.
3. **Step 3: Run the Installer** — Click the "Install" button next to the uploaded package and follow the instructions to set up database tables, menus, and the system plugin.
4. **Step 4: Verify Settings** — Navigate to "System Settings" (gear icon in the top-right corner), select the `webpconverter` namespace, and verify that the exclusions and `cwebp` command-line parameters match your hosting environment.
5. **Step 5: Scan for Images** — Go to **Apps** -> **WEBP Converter** and click the **Scan Site** button to build the image database.
6. **Step 6: Execute Conversion** — Click the **Convert Files** button to start the optimization process. Wait for the progress bar to reach 100%.

---

## Optimization Tips and Settings

The table below lists the primary configuration parameters for tuning the `cwebp` compression engine:

| Setting Key | Default Value | Recommendations |
| --- | --- | --- |
| `webpconverter.cwebp_params_jpeg` | `-metadata none -quiet -pass 10 -m 6 -mt -q 65 -low_memory` | A quality setting of `-q 65` gives the best file size-to-quality ratio for JPEGs. Increase to `80` if you need high-resolution photographer assets. |
| `webpconverter.cwebp_params_png` | `-metadata none -quiet -pass 10 -m 6 -alpha_q 85 -mt -alpha_filter best -alpha_method 1 -q 70 -low_memory` | The `-alpha_q 85` setting optimizes PNG transparency gradients without introducing visible artifacts. |
| `webpconverter.exclude_dirs` | `core,connectors,manager,webp,tmp,.git,vendor,node_modules` | Ensure system and build folders are excluded so the scanner does not waste CPU cycles index-searching third-party libraries. |
| `webpconverter.disable_for_logged_user` | `No` (false) | Set to `Yes` if you are designing or debugging layouts and need to verify original image quality as an administrator. |

---

## FAQ and Troubleshooting

### What should I do if the conversion process fails to start?
Ensure that the `cwebp` utility is installed on your server and that the PHP runtime has permission to run external commands (via `exec`). You can verify this by checking with your hosting provider.

### Why are my images not changing to `.webp` in the browser?
1. Check that the WebpConverter system plugin is enabled and registered to the `OnWebPagePrerender` event.
2. Verify if the `webpconverter.disable_for_logged_user` setting is active while you are logged into the MODX manager.
3. Clear the MODX site cache by selecting `Manage` -> `Clear Cache` from the top menu.

### Where are the optimized files stored?
The `.webp` files are created in the exact same directories as the original images (e.g. `assets/images/photo.webp` will be created right next to `assets/images/photo.jpg`).
