# WebpConverter

*Bildoptimierungskomponente für MODX 3.*

[![Lizenz: MIT](https://img.shields.io/badge/Lizenz-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![MODX 3](https://img.shields.io/badge/MODX-3.x-blue.svg)](https://modx.com/)
[![PHP >= 7.4](https://img.shields.io/badge/PHP-%3E%3D%207.4-8892BF.svg)](https://php.net/)
[![Plattform: Windows / Linux / macOS](https://img.shields.io/badge/Plattform-Windows%20%7C%20Linux%20%7C%20macOS-lightgrey.svg)]()
[![Teilen](https://img.shields.io/twitter/url?style=social&url=https%3A%2F%2Fgithub.com%2FAlmanex%2FWebpconverter)](https://twitter.com/intent/tweet?text=Check%20out%20this%20awesome%20MODX%203%20image%20optimization%20component&url=https%3A%2F%2Fgithub.com%2FAlmanex%2FWebpconverter)

WebpConverter ist eine gebrauchsfertige Komponente für MODX 3, die die Ladezeit von Websites durch die automatische Konvertierung von JPEG- und PNG-Bildern in das moderne, leichtgewichtige WebP-Format optimiert. Sie ersetzt Bildpfade dynamisch im HTML-Code und bietet ein Dashboard-Widget sowie eine Verwaltungsseite zur Stapelverarbeitung und Bereinigung.

---

## Hauptmerkmale

- **Dynamische Pfadersetzung**: Ersetzt Links in `<img>`-Tags, `srcset`-Listen und Inline-Styles im generierten HTML-Code der Seite automatisch.
- **Website-Scan**: Rekursive Suche nach Bilddateien in Serververzeichnissen mit konfigurierbarer Ausschlussliste.
- **Stapelkonvertierung**: Generiert WebP-Dateien in kleinen Gruppen, um Server-Timeouts und CPU-Überlastungen zu verhindern.
- **Bereinigung verwaister Dateien**: Löscht automatisch `.webp`-Dateien, deren Originalbilder aus der Medienbibliothek gelöscht wurden.
- **Dashboard-Widget**: Zeigt Echtzeit-Statistiken (Gesamtzahl der Bilder, optimierter Fortschritt, gesparter Speicherplatz) direkt im Admin-Bereich an.
- **Verwaltungsseite**: Eigene Seite (CMP) zum Starten von Scans, Konvertierungen und Bereinigungen.

---

## Technologiestack

| Ebene / Komponente | Technologie | Details / Zweck |
| --- | --- | --- |
| Server-Logik | PHP (>= 7.4) | Core-Plugin, Prozessoren und Registrierung im DI-Container |
| CMS-Plattform | MODX 3.x | Content-Management-System |
| Benutzeroberfläche (UI) | ExtJS / ModExt | Custom Manager Page-Komponenten und Dashboard-Widgets |
| Bildkomprimierung | cwebp CLI | Google WebP-Encoder-Befehlszeilentool |

---

## Erste Schritte

### Voraussetzungen

- Installierte Version von MODX 3.x.
- Installierte `cwebp`-Befehlszeilenanwendung auf dem Server, die von PHP ausgeführt werden kann.

### Installation

1. Laden Sie das Transportpaket herunter: [webpconverter-1.0.0-pl.transport.zip](../webpconverter-1.0.0-pl.transport.zip).
2. Laden Sie die ZIP-Datei über die **Paketverwaltung** (Extras -> Paketverwaltung) in MODX hoch.
3. Klicken Sie auf **Installieren** und folgen Sie den Anweisungen.

---

## Tests ausführen

Dieses Paket enthält keine automatisierten Tests. Anweisungen zum manuellen Testen finden Sie im [Benutzerhandbuch](GUIDE_DE.md).

---

## Bereitstellung (Deployment)

Um Änderungen in ein neues Installationspaket zu packen, führen Sie das Build-Skript aus:

```bash
php core/components/webpconverter/_build/build.transport.php
```

Das Skript packt Plugins, Lexika, Systemeinstellungen, Widgets und Menüs in ein neues Transportpaket unter `core/packages/`. Kopieren Sie das ZIP-Archiv zurück in das Root-Verzeichnis, um das Installationspaket zu aktualisieren.

---

## Mitwirken

Beiträge zum Projekt sind willkommen! Sie können Fehler melden oder Verbesserungsvorschläge per Issue oder Pull Request im Repository einreichen.

---

## Lizenz

Dieses Projekt ist unter der MIT-Lizenz lizenziert. Weitere Details finden Sie in der Datei `LICENSE`.

---

Detaillierte Anweisungen zur Konfiguration finden Sie im [Benutzerhandbuch](GUIDE_DE.md). Informationen zur Architektur und API finden Sie im [Entwicklerhandbuch (Russisch)](DEVELOPER_GUIDE_RU.md).
