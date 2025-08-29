Changelog
=========

Version 2.0.0 – 29.08.2025
--------------------------
🎉 **Major Release - Modernisiertes Dashboard**

**Neue Features:**
- ✨ Standard-Widgets für REDAXO Core-Funktionen
- 📝 Artikel-Widgets mit Benutzerrechte-Integration  
- 📊 System-Status und AddOn-Verwaltung (nur Admins)
- 📁 Medien-Speicherverbrauch nach Dateitypen
- 📡 RSS-Feed Widget mit Paginierung (2 Items/Seite)
- 🛡️ Structure-Permissions für alle Artikel-Widgets
- 🎨 Auto-Refresh beim Dashboard-Load (500ms Verzögerung)
- ⚙️ Zentrale Konfigurationsseite für Administratoren
- 📱 Verbessertes Responsive Design
- 🌐 Multi-Language Support mit dynamischen Spalten

**Änderungen:**
- 🔄 Demo-Plugin aufgelöst und ins Core integriert
- 🏗️ Widget-IDs standardisiert (`dashboard-default-*` Präfix)
- 🔧 RSS-Feed zentral konfigurierbar statt per Widget
- 📈 Media Storage von Chart auf Tabelle umgestellt
- 🗑️ User Activity Widget deaktiviert (Performance)

**Sicherheit:**
- 🔒 Strenge Berechtigungsprüfung für Admin-Widgets
- 🛡️ XSS-Schutz durch `rex_escape()` für alle Ausgaben
- 🚫 SQL-Injection-Schutz durch Parameter-Binding
- 👤 User-spezifische Widget-Layouts

**Entfernt:**
- Demo Plugin komplett entfernt
- Veraltete API-Endpoints bereinigt
- Legacy Chart-Code entfernt

**Migration:**
- Automatische Übernahme bestehender Widget-Positionen
- Neue Widgets initial deaktiviert
- Konfiguration über Dashboard > Konfiguration erforderlich

Version 1.2 – 28.08.2025
--------------------------
- Jetzt FOR-AddOn
- Neu: Auto-Refresh
- Button-Leiste verschoben und dezenter

Version 1.1 – 27.05.2021
--------------------------

- Bugfix: JS Aufrufe waren fehlerhaft
- Bugfix: Cachingaufrufe wurden angepasst
- Etwas Logikänderunge bei den Widgetklassenaufrufen
- Quicknavigation nun parallel nutzbar

Version 1.0 – 18.05.2021
--------------------------

- Initiales Setup
- Pie, Balken Charts Klassen
- Listenklasse
- Auswahl von Widgets
- Grid und Grundgerüst
- Refresh der (aller) Widgetdaten
