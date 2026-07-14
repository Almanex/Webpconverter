# Руководство разработчика: Компонент WebpConverter для MODX 3

Компонент **WebpConverter** представляет собой готовое решение для автоматической и ручной конвертации изображений (JPEG, PNG) в современный формат WebP на платформе **MODX 3**.

---

## Архитектура компонента

Компонент построен по стандартам MODX 3 с использованием пространства имен (Namespace), автозагрузки PSR-4 и объектно-ориентированных процессоров (Class-based Processors).

### Структура файлов и папок

```text
webpconverter/
├── _build/                          # Скрипты сборки транспортного пакета
│   ├── build.transport.php          # Основной скрипт сборки
│   └── resolvers/
│       └── resolve.events.php       # Резолвер для регистрации плагина на системные события
├── controllers/
│   └── home.class.php               # Контроллер для Custom Manager Page (CMP)
├── elements/
│   └── widgets/
│       └── webpconverter.widget.php # Класс виджета для панели управления (Dashboard)
├── lexicon/
│   ├── en/
│   │   ├── default.inc.php          # Английские лексиконы компонента
│   │   └── setting.inc.php          # Английские лексиконы настроек
│   └── ru/
│       ├── default.inc.php          # Русские лексиконы компонента
│       └── setting.inc.php          # Русские лексиконы настроек
├── processors/
│   ├── clean.class.php              # Процессор для очистки лишних/удаленных webp-файлов
│   ├── convert.class.php            # Процессор для конвертации найденных изображений
│   ├── scan.class.php               # Процессор для сканирования и поиска изображений
│   └── stats.class.php              # Процессор для получения текущей статистики
├── src/
│   └── WebpConverter.php            # Основной сервис-класс (содержит логику конвертации и поиска)
├── templates/
│   └── home.tpl                     # Шаблон разметки для CMP
├── bootstrap.php                    # Файл инициализации и регистрации автозагрузчика PSR-4 в MODX 3
└── composer.json                    # Описание автозагрузки для Composer
```

---

## Регистрация сервиса и автозагрузка (bootstrap.php)

MODX 3 использует файл `bootstrap.php` в корне компонента для автоматической регистрации пространства имен и инициализации сервисов в контейнере зависимостей (Dependency Injection Container):

```php
// Регистрация автозагрузчика классов
$namespaces = [
    'WebpConverter' => dirname(__FILE__) . '/src/',
];
foreach ($namespaces as $namespace => $path) {
    $mxNamespace = new \MODX\Revolution\modNamespace();
    $mxNamespace->set('name', strtolower($namespace));
    $mxNamespace->set('path', dirname(__FILE__) . '/');
    $mxNamespace->set('assets_path', MODX_ASSETS_PATH . 'components/' . strtolower($namespace) . '/');
    
    // Добавление в автозагрузчик MODX
    $modx->getAutoload()->addPsr4($namespace . '\\', $path);
}

// Регистрация сервиса в DI контейнере
$modx->services->add('webpconverter', function($c) use ($modx) {
    return new \WebpConverter\WebpConverter($modx);
});
```

---

## API Основного класса (`\WebpConverter\WebpConverter`)

Класс-сервис находится в файле `core/components/webpconverter/src/WebpConverter.php`. Он решает следующие задачи:
* **Инициализация параметров**: считывает настройки сжатия и исключения директорий.
* **Сканирование файловой системы**: рекурсивно ищет файлы `.jpg`, `.jpeg`, `.png`, пропуская исключенные папки и системные папки (например, папки с паттерном `modx-`).
* **Конвертация**: вызывает утилиту `cwebp` через системную функцию `exec` с соответствующими параметрами командной строки.
* **Обновление путей**: заменяет пути к картинкам в HTML-коде страницы на лету.

### Ключевые методы:

1. **`getStats()`**: Возвращает массив со статистикой (общее кол-во картинок, кол-во сконвертированных webp, размер оригиналов, размер webp, процент оптимизации).
2. **`scanImages()`**: Сканирует корневую директорию сайта (исключая папки, заданные в `webpconverter.exclude_dirs`) и формирует кэш-файл со списком всех найденных изображений.
3. **`convertNext($limit = 10)`**: Берет следующие N изображений из списка не сконвертированных и сжимает их с помощью утилиты `cwebp`.
4. **`cleanOrphanedWebp()`**: Находит и удаляет файлы `.webp`, для которых оригинальные JPEG/PNG изображения больше не существуют.

---

## Системные события и работа плагина

Плагин зарегистрирован на события:
1. **`OnWebPagePrerender`** — перехватывает готовый HTML-вывод страницы и производит замену путей оригинальных изображений в тегах `<img>`, `srcset`, стилях на соответствующие `.webp` версии (если файл `.webp` существует на сервере).
2. **`OnFileManagerUpload`** / **`OnFileManagerFileRename`** — опционально для автоматического сжатия при загрузке новых файлов через медиа-менеджер.

---

## Разработка интерфейса (CMP и Dashboard Widget)

* **CMP (Custom Manager Page)**: Использует ExtJS панель. Контроллер находится в `controllers/home.class.php`. Шаблон рендерится через Smarty (`templates/home.tpl`). JS-логика реализована в `assets/components/webpconverter/js/mgr/widgets/home.panel.js`.
* **Виджет консоли**: Находится в `elements/widgets/webpconverter.widget.php`. Он асинхронно запрашивает процессор `stats` и выводит прогресс оптимизации прямо на главной странице панели MODX.

---

## Сборка и деплой транспортного пакета

Для компиляции изменений в транспортный пакет `.transport.zip` выполните команду в консоли:

```bash
docker-compose exec -T php php /var/www/html/core/components/webpconverter/_build/build.transport.php
```

После выполнения скрипт создаст архив в папке `core/packages/webpconverter-1.0.0-pl.transport.zip`, готовый к установке на любой другой сайт MODX 3.
