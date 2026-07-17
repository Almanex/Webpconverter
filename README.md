[README_RU](docs/README_RU.md) | [README_DE](docs/README_DE.md) | [README_EN](README.md) | [GUIDE_RU](docs/GUIDE_RU.md) | [GUIDE_DE](docs/GUIDE_DE.md) | [GUIDE_EN](docs/GUIDE_EN.md)
# WebpConverter

*Image optimization component for MODX 3.*

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![MODX 3](https://img.shields.io/badge/MODX-3.x-blue.svg)](https://modx.com/)
[![PHP >= 7.4](https://img.shields.io/badge/PHP-%3E%3D%207.4-8892BF.svg)](https://php.net/)
[![Platform: Windows / Linux / macOS](https://img.shields.io/badge/Platform-Windows%20%7C%20Linux%20%7C%20macOS-lightgrey.svg)]()
[![Share](https://img.shields.io/twitter/url?style=social&url=https%3A%2F%2Fgithub.com%2FAlmanex%2FWebpconverter)](https://twitter.com/intent/tweet?text=Check%20out%20this%20awesome%20MODX%203%20image%20optimization%20component&url=https%3A%2F%2Fgithub.com%2FAlmanex%2FWebpconverter)

WebpConverter is a ready-to-use component for MODX 3 that automatically accelerates website loading times by converting JPEG and PNG images to the modern, lightweight WebP format. It dynamically replaces image paths in the HTML output on the fly and provides a custom dashboard widget and manager page for bulk scanning, conversion, and cleanup.

---

## Key Features

- **On-the-fly path replacement**: Dynamically modifies image source URLs within `<img>` tags, `srcset` lists, and inline styles in the prerendered HTML output.
- **Bulk scanning**: Recursively scans the site's directory (ignoring system folders and customizable exclusions) to build an image database.
- **Queue-based conversion**: Converts images in configurable batch sizes to prevent server timeouts and resource exhaustion.
- **Orphan cleanup**: Scans and deletes generated `.webp` files whose original JPEGs/PNGs have been deleted.
- **Dashboard widget**: Displays real-time metrics including total images, optimized images, percentage progress, and total disk space saved.
- **Manager panel**: Dedicated Custom Manager Page (CMP) for running scans, executing conversions, and viewing statistics.

---

## Tech Stack

| Layer / Component | Technology | Details / Purpose |
| --- | --- | --- |
| Server-side Language | PHP (>= 7.4) | Core logic, processors, and dependency injection container registration |
| CMS Platform | MODX 3.x | Target content management system |
| Frontend UI | ExtJS / ModExt | Custom Manager Page interface components |
| Image Compression | cwebp CLI | Google WebP encoder command-line utility |

---

## Getting Started

### Prerequisites

- MODX 3.x installation.
- The `cwebp` command line utility installed and accessible by the PHP runtime.

### Installation

1. Download the precompiled transport package: [webpconverter-1.0.0-pl.transport.zip](webpconverter-1.0.0-pl.transport.zip).
2. Open your MODX manager panel and navigate to **Apps** -> **Package Management**.
3. Upload the transport package file.
4. Click **Install** and follow the step-by-step installation instructions.

---

## Running the Tests

No automated testing suite is included with this package. To perform manual verification, refer to the testing guidelines in the [User Guide](docs/GUIDE.md).

---

## Deployment

To compile your code modifications into a new installable transport package, execute the build script:

```bash
php core/components/webpconverter/_build/build.transport.php
```

The build script automatically bundles plugins, lexicons, system settings, widgets, and menu items into a transport package archive inside the `core/packages/` directory of your MODX site. Copy the compiled ZIP archive back to the root of this folder to update your package distribution.

---

## Contributing

Contributions are welcome! If you would like to help improve this component, please open an issue or submit a pull request on the repository.

---

## License

This project is licensed under the MIT License. Refer to the `LICENSE` file for details.

---

For detailed instructions on configuring settings, see the [User Guide](docs/GUIDE.md). If you are looking to extend the component or understand its architecture, check out the [Developer Guide (Russian)](docs/DEVELOPER_GUIDE_RU.md).
