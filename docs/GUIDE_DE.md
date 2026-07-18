# WebpConverter Benutzerhandbuch — Bilder in MODX 3 optimieren

> [!NOTE]
> **💡 Zusammenfassung:** WebpConverter ist eine gebrauchsfertige Komponente für MODX 3, die JPEG- und PNG-Bilder mithilfe des Programms `cwebp` automatisch während des Seitenaufrufs in das moderne, leichtgewichtige WebP-Format konvertiert. Dies verbessert die Google PageSpeed-Werte Ihrer Website erheblich. Die Erweiterung bietet ein Dashboard-Widget zur Anzeige des eingesparten Speicherplatzes sowie eine Verwaltungsseite zur Durchführung von Scans, Massenkonvertierungen und Bereinigungen.

---

## Systemübersicht

Webtechnologien entwickeln sich rasant weiter, und die Ladegeschwindigkeit einer Website ist ein entscheidender Faktor für das SEO-Suchmaschinenranking. Die Komponente **WebpConverter** wurde speziell für MODX 3 entwickelt, um die Ladezeiten durch die Optimierung von Bilddateien zu beschleunigen. Sie konvertiert JPEG-, JPG- und PNG-Bilder vollautomatisch in das moderne Nachfolgeformat **WebP**. Durch die Integration eines nativen System-Plugins erfolgt der Pfadaustausch im HTML-Quellcode völlig transparent für Administratoren und Besucher.

---

## Funktionen der Komponente

### Dynamischer Pfadaustausch
Nach der Installation und Aktivierung des Plugins fängt es das Seiten-Rendering-Ereignis `OnWebPagePrerender` ab. Alle Bildverweise in `<img>`-Tags, `srcset`-Attributen und Inline-CSS-Stilen werden dynamisch durch die Pfade der neu generierten `.webp`-Dateien ersetzt.

### Website-Scan und Überwachung
Das Scan-Dienstprogramm durchsucht die Verzeichnisse der Website rekursiv nach Originalbildern. Es überspringt Systemordner sowie benutzerdefinierte Ausschlüsse und speichert die Konvertierungsdaten in einer Datenbanktabelle.

### Stapelverarbeitung (Warteschlangenbasiert)
Die Komprimierung wird in konfigurierten Paketen abgearbeitet. Sie können die Stapelgröße anpassen, um Server-Timeouts zu vermeiden und die CPU-Last bei der Verarbeitung von Hunderten von Dateien zu minimieren.

### Bereinigung verwaister Dateien
Wenn Sie ein Originalbild über den MODX-Medienmanager löschen, wird die entsprechende `.webp`-Datei nicht mehr benötigt. Das Bereinigungswerkzeug findet diese verwaisten Dateien und löscht sie sicher, um Speicherplatz freizugeben.

---

## Schritt-für-Schritt-Anleitung zur Einrichtung

Befolgen Sie diese Schritte, um die Komponente auf Ihrer Website einzurichten:

1. **Schritt 1: Paket herunterladen** — Laden Sie das fertige Transportpaket `webpconverter-1.0.0-pl.transport.zip` aus den Releases des GitHub-Repositorys herunter.
2. **Schritt 2: In MODX hochladen** — Melden Sie sich im MODX 3-Verwaltungsbereich an, navigieren Sie zu **Apps** -> **Paketverwaltung**, klicken Sie auf das Dropdown-Menü zum Hochladen und wählen Sie das ZIP-Archiv aus.
3. **Schritt 3: Installation durchführen** — Klicken Sie neben dem hochgeladenen Paket auf „Installieren“ und folgen Sie den Anweisungen auf dem Bildschirm, um die Datenbanktabellen, Menüs und das Plugin zu erstellen.
4. **Schritt 4: Systemeinstellungen prüfen** — Öffnen Sie die Systemeinstellungen (Zahnrad-Symbol oben rechts), wählen Sie den Namensraum `webpconverter` und verifizieren Sie die Ausschlüsse sowie die Parameter für `cwebp`.
5. **Schritt 5: Website scannen** — Gehen Sie zu **Apps** -> **WEBP Converter** und klicken Sie auf **Website scannen**, um die Bilddatenbank zu befüllen.
6. **Schritt 6: Konvertierung starten** — Klicken Sie auf **Dateien konvertieren**, um die Optimierung der Bilder zu starten, und warten Sie, bis der Fortschrittsbalken 100 % erreicht.

---

## Optimierungstipps und Einstellungen

Die folgende Tabelle zeigt die wichtigsten Parameter zur Konfiguration der `cwebp`-Komprimierung:

| Einstellungsschlüssel | Standardwert | Empfehlungen |
| --- | --- | --- |
| `webpconverter.cwebp_params_jpeg` | `-metadata none -quiet -pass 10 -m 6 -mt -q 65 -low_memory` | Der Qualitätswert `-q 65` bietet das beste Verhältnis von Qualität zu Dateigröße für JPEGs. Erhöhen Sie auf `80` für hochauflösende Fotografien. |
| `webpconverter.cwebp_params_png` | `-metadata none -quiet -pass 10 -m 6 -alpha_q 85 -mt -alpha_filter best -alpha_method 1 -q 70 -low_memory` | Der Parameter `-alpha_q 85` optimiert die PNG-Transparenz ohne sichtbare Bildfehler. |
| `webpconverter.exclude_dirs` | `core,connectors,manager,webp,tmp,.git,vendor,node_modules` | Schließen Sie Systemordner unbedingt aus, damit der Scanner keine CPU-Ressourcen für die Indizierung von Systembibliotheken verschwendet. |
| `webpconverter.disable_for_logged_user` | `Nein` (false) | Aktivieren Sie (`Ja`), falls Sie Layouts bearbeiten oder debuggen und die Originalbilder als Administrator prüfen müssen. |

---

## FAQ und Fehlerbehebung

### Was tun, wenn die Konvertierung nicht startet?
Stellen Sie sicher, dass das Tool `cwebp` auf Ihrem Server installiert ist und PHP die Berechtigung zur Ausführung externer Programme besitzt (Funktion `exec`). Wenden Sie sich im Zweifel an Ihren Hosting-Anbieter.

### Warum werden die Bilder im Browser nicht durch `.webp` ersetzt?
1. Überprüfen Sie, ob das WebpConverter-Plugin aktiviert und mit dem Ereignis `OnWebPagePrerender` verknüpft ist.
2. Prüfen Sie, ob die Option `webpconverter.disable_for_logged_user` aktiv ist, während Sie im Manager angemeldet sind.
3. Leeren Sie den MODX-Website-Cache über `Verwalten` -> `Cache leeren`.

### Wo werden die optimierten Dateien gespeichert?
Die `.webp`-Dateien werden direkt im selben Verzeichnis wie die Originalbilder abgelegt (z. B. wird `photo.webp` direkt neben `photo.jpg` erzeugt).
