# Project Context: WebpConverter for MODX 3

This document provides system-level context and architectural details of the `WebpConverter` MODX 3 component, designed for AI agents working on this codebase.

---

## Project Overview

`WebpConverter` is a package for MODX 3 that converts uploaded website images (JPEG, PNG) into the highly compressed WebP format and replaces image paths in the prerendered HTML output dynamically. It includes:
1. A system plugin registered to `OnWebPagePrerender` that does HTML parsing and replacement on the fly.
2. A Custom Manager Page (CMP) built using ExtJS for administrative scanning and queue-based batch conversion.
3. A dashboard widget showing optimization statistics (space saved, counts, progress).

---

## Directory Structure

```text
webpconverter-1.0.0-pl/
├── assets/
│   └── components/
│       └── webpconverter/      # CSS, JS, and connectors for the CMP
│           ├── css/
│           ├── js/             # ExtJS panel, widgets, and grid components
│           └── connector.php   # Endpoint for AJAX requests from the CMP
├── core/
│   └── components/
│       └── webpconverter/      # Server-side business logic
│           ├── Binaries/       # Precompiled cwebp executables for platforms
│           ├── _build/         # MODX transport package build scripts and resolvers
│           ├── controllers/    # Smarty templates controllers (e.g. home.class.php)
│           ├── docs/           # Documentation and guides
│           ├── elements/       # Widgets (webpconverter.widget.php) and plugins
│           ├── lexicon/        # English and Russian translations for settings and menus
│           ├── processors/     # MODX processors (scan, convert, clean, stats)
│           ├── src/            # Core PHP namespace (PSR-4 WebpConverter\WebpConverter)
│           ├── templates/      # Smarty templates (.tpl files)
│           ├── bootstrap.php   # Dependency injection container registration
│           └── composer.json   # Composer autoloader metadata
├── docs/                       # Project documentation folder
└── webpconverter-1.0.0-pl.transport.zip # Built transport package
```

---

## Core Components & Architecture

### 1. Autoloading and Dependency Injection (`core/components/webpconverter/bootstrap.php`)
MODX 3 loads this file to register the `WebpConverter` namespace and add it to the service container:
```php
$modx->services->add('webpconverter', function($c) use ($modx) {
    return new \WebpConverter\WebpConverter($modx);
});
```

### 2. Main Service Class (`core/components/webpconverter/src/WebpConverter.php`)
This is the core service containing:
- `scanImages()`: Scans folders specified in system settings while ignoring exclusions (`webpconverter.exclude_dirs`). Writes a list of found files to a temporary cache.
- `convertNext($limit)`: Reads the cache file, takes `$limit` number of files, and calls the external `cwebp` command to convert them.
- `cleanOrphanedWebp()`: Cleans up WebP files that no longer have a corresponding source file.
- `getStats()`: Computes total files, processed files, saved disk space.

### 3. Dynamic HTML Path Swapping
The plugin hook on `OnWebPagePrerender` intercepts the final HTML document before it is sent to the client and replaces image paths with their WebP equivalents if the WebP file exists.

### 4. Custom Manager Page (CMP)
The CMP is accessed via the MODX manager menu. It uses ExtJS widgets (`assets/components/webpconverter/js/mgr/widgets/home.panel.js`) to interact with processors via the `connector.php` endpoint.

---

## Developer Guidelines

- **Namespaces**: Use PSR-4 `WebpConverter\...` namespace.
- **Processors**: All processors must inherit from `MODX\Revolution\Processors\Processor` or specific subclasses.
- **Settings**: System settings are prefixed with `webpconverter.`.
- **System Events**: The plugin is registered to `OnWebPagePrerender`.
