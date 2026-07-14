# Benutzerhandbuch: WebpConverter-Komponente für MODX 3

Mit der **WebpConverter**-Komponente können Sie die Ladezeit Ihrer MODX 3-Website automatisch beschleunigen, indem Sie Bilder (JPEG, PNG) in das moderne, leichtgewichtige **WebP**-Format konvertieren. Das Plugin ersetzt Bildpfade dynamisch im HTML-Code der Seite auf Code-Ebene, um den Benutzern optimierte Versionen bereitzustellen.

---

## 1. Installation

1. Melden Sie sich im MODX-Verwaltungsbereich (Admin-Bereich) an.
2. Navigieren Sie zu **Apps** (Extras) -> **Paketverwaltung** (Installer).
3. Laden Sie das Transportpaket hoch: `webpconverter-1.0.0-pl.transport.zip`.
4. Klicken Sie neben dem hochgeladenen Paket auf **Installieren** und folgen Sie den Anweisungen auf dem Bildschirm.

Nach der Installation werden folgende Elemente im System erstellt:
- Ein System-Plugin, das Bildlinks zur Laufzeit durch `.webp` ersetzt.
- Ein **WEBP Converter**-Menüeintrag im Bereich **Apps** (Extras).
- Ein **WEBP Optimierung**-Widget auf der Startseite des Dashboards.

---

## 2. Systemeinstellungen

Die Einstellungen der Komponente befinden sich unter **Systemeinstellungen** (Zahnrad-Symbol oben rechts -> Systemeinstellungen) im Namensraum `webpconverter`.

### Verfügbare Parameter:

1. **`webpconverter.cwebp_params_jpeg`** (JPEG-Komprimierungseinstellungen)
   - **Standardwert**: `-metadata none -quiet -pass 10 -m 6 -mt -q 65 -low_memory`
   - **Beschreibung**: Befehlszeilenparameter für das Programm `cwebp`, das zur JPEG-Komprimierung verwendet wird.
     - `-metadata none` — entfernt alle EXIF/IPTC-Metadaten, um die Dateigröße zu reduzieren.
     - `-quiet` — deaktiviert detaillierte Protokollausgaben.
     - `-pass 10` — Anzahl der Durchgänge zur Optimierung der Entropie (max. 10).
     - `-m 6` — Komprimierungsmethode (0-6, wobei 6 die beste Komprimierung bietet, aber mehr CPU-Zeit erfordert).
     - `-mt` — aktiviert Multi-Threading, um die Verarbeitung zu beschleunigen.
     - `-q 65` — Komprimierungsqualität (Skala 0-100, wobei 65 die optimale Balance zwischen Qualität und Gewicht für JPEG ist).
     - `-low_memory` — optimiert die Speichernutzung auf dem Server.

2. **`webpconverter.cwebp_params_png`** (PNG-Komprimierungseinstellungen)
   - **Standardwert**: `-metadata none -quiet -pass 10 -m 6 -alpha_q 85 -mt -alpha_filter best -alpha_method 1 -q 70 -low_memory`
   - **Beschreibung**: Befehlszeilenparameter für die Komprimierung von PNG-Dateien mit Transparenzunterstützung.
     - `-alpha_q 85` — Qualität der Alpha-Kanal-Komprimierung (Transparenz).
     - `-alpha_filter best` — Alpha-Filterungsalgorithmus.
     - `-alpha_method 1` — Alpha-Komprimierungsmethode.
     - `-q 70` — Komprimierungsqualität für die Haupt-PNG-Farben.

3. **`webpconverter.exclude_dirs`** (Ausgeschlossene Verzeichnisse)
   - **Standardwert**: `core,connectors,manager,webp,tmp,.git,vendor,node_modules`
   - **Beschreibung**: Kommagetrennte Liste von Ordnern (relativ zum Root-Verzeichnis der Website), die vom Bildscanner vollständig ignoriert werden sollen. Es wird dringend empfohlen, Systemordner und Build-Verzeichnisse auszuschließen, um eine unnötige Serverlast zu vermeiden.

4. **`webpconverter.disable_for_logged_user`** (Für angemeldete Benutzer deaktivieren)
   - **Standardwert**: `Nein` (false)
   - **Beschreibung**: Wenn auf `Ja` (true) eingestellt, ersetzt das Plugin keine Bildpfade durch `.webp` für Administratoren und Manager, die im MODX-Verwaltungsbereich angemeldet sind. Dies ist nützlich, um Original-Layouts zu testen oder CSS-Designs zu debuggen.

---

## 3. Verwendung

### Custom Manager Page (CMP-Schnittstelle)

Navigieren Sie zu **Apps** -> **WEBP Converter**. Im Bedienfeld stehen folgende Aktionen zur Verfügung:

1. **Website scannen**:
   - Klicken Sie auf die Schaltfläche **Website scannen**, um nach allen Bildern auf dem Server zu suchen. Der Scanner findet alle JPG-, JPEG- und PNG-Dateien, außer jenen in den ausgeschlossenen Ordnern.
   - Ein Fortschrittsbalken zeigt den aktuellen Status an.
2. **Dateien konvertieren**:
   - Klicken Sie auf die Schaltfläche **Dateien konvertieren**, um die Generierung von `.webp`-Versionen der gefundenen Bilder zu starten.
   - Die Konvertierung erfolgt in Stapeln (standardmäßig 10 Dateien pro Anfrage), um Skript-Timeouts des Servers zu verhindern. Der Fortschrittsbalken zeigt den Prozentsatz an.
3. **Verwaiste Dateien löschen**:
   - Klicken Sie auf die Schaltfläche **Verwaiste Dateien löschen**, um `.webp`-Dateien zu entfernen, deren Originalbilder gelöscht wurden (z. B. nach dem Löschen von Bildern über den Medienmanager oder die Galerie). Dies spart Speicherplatz auf dem Server.

### Dashboard-Widget

Auf der Dashboard-Hauptseite zeigt das Widget **WEBP Optimierung** Live-Statistiken an:
- Gesamtzahl der gefundenen Bilder auf der Website.
- Anzahl der bereits optimierten Bilder.
- Aktueller Fortschritt in Prozent.
- Gesamter eingesparter Speicherplatz (in MB oder KB).
- **Aktualisieren**-Schaltfläche zur sofortigen Neuberechnung.
- **Zum Converter wechseln**-Schaltfläche zur Schnellnavigation zur CMP.
