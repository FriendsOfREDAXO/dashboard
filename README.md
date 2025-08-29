# Dashboard AddOn für REDAXO 5.x

## Version 2.0.0 - Modernisiertes Dashboard mit Standard-Widgets

Das Dashboard AddOn ermöglicht es, wichtige Informationen aus REDAXO und anderen AddOns übersichtlich auf der Startseite des Backends anzuzeigen. Mit Version 2.0 wurde das Dashboard grundlegend modernisiert und um Standard-Widgets erweitert.

## ✨ Neue Features in Version 2.0

### 🎯 Standard-Widgets für REDAXO Core-Funktionen

- **📝 Artikel-Widgets**: Zuletzt bearbeitete und neue Artikel mit Benutzerrechte-Integration
- **📊 System-Status**: Speicherverbrauch, PHP-Version, Datenbankgröße (nur Admins)
- **📁 Medien-Speicher**: Kategorisierte Übersicht des Medienpools nach Dateitypen
- **🔧 AddOn-Verwaltung**: Verfügbare Updates und AddOn-Statistiken (nur Admins)
- **📡 RSS-Feed**: Konfigurierbare RSS-Feeds mit Paginierung
- **📈 Artikel-Status**: Übersicht über Online/Offline-Artikel

### 🛡️ Sicherheit und Berechtigungen

- **Benutzerrechte**: Artikel-Widgets berücksichtigen REDAXO-Benutzerberechtigungen
- **Admin-Widgets**: Sensitive Informationen nur für Administratoren sichtbar
- **Structure-Permissions**: Integration mit `rex_complex_perm('structure')`

### 🎨 Verbessertes UI/UX

- **Auto-Refresh**: Dashboard lädt automatisch aktuelle Daten beim Aufrufen
- **Responsive Design**: Optimiert für verschiedene Bildschirmgrößen
- **Drag & Drop**: Widgets frei positionierbar mit GridStack.js
- **Konfigurationsseite**: Zentrale Verwaltung aller Widget-Einstellungen

## 🚀 Installation und Aktivierung

1. AddOn installieren und aktivieren
2. Als Administrator zu **Dashboard > Konfiguration** gehen
3. "Default Widgets aktivieren" ankreuzen
4. Gewünschte Widgets auswählen und Größen konfigurieren
5. Für RSS-Widget: Feed-URL und Namen eingeben

## 📋 Verfügbare Standard-Widgets

| Widget | Beschreibung | Berechtigung | Größe |
|--------|-------------|--------------|-------|
| **Zuletzt aktualisierte Artikel** | Die 10 neuesten bearbeiteten Artikel | Structure-Rechte | 2 Spalten |
| **Neue Artikel (30 Tage)** | Artikel der letzten 30 Tage | Structure-Rechte | 2 Spalten |
| **Medien-Speicherverbrauch** | Dateityp-kategorisierte Übersicht | Alle User | 1 Spalte |
| **Artikel-Status** | Online/Offline Artikel-Statistik | Alle User | 1 Spalte |
| **System-Status** | PHP, MySQL, Speicher-Infos | Nur Admins | 2 Spalten |
| **AddOn Updates** | Verfügbare Updates anzeigen | Nur Admins | 2 Spalten |
| **AddOn Statistiken** | Installierte AddOns-Übersicht | Nur Admins | 1 Spalte |
| **RSS Feed** | Konfigurierbare RSS-Feeds | Alle User | 1-2 Spalten |

## ⚙️ Konfiguration

### Dashboard-Einstellungen

Über **Dashboard > Konfiguration** können Administratoren:

- Default Widgets aktivieren/deaktivieren
- Widget-Größen konfigurieren (1 oder 2 Spalten)
- RSS-Feed URL und Namen festlegen
- Demo-Widgets aktivieren (zu Testzwecken)

### RSS-Widget Konfiguration

```
RSS Feed URL: https://example.com/feed.xml
RSS Feed Name: Mein RSS Feed
Größe: 1 Spalte (klein) oder 2 Spalten (breit)
```

Das RSS-Widget zeigt 2 Items pro Seite mit Paginierung an.

## 🔧 Entwickler-API

### Eigene Widgets erstellen

```php
class MeinCustomWidget extends rex_dashboard_item
{
    public function getTitle(): string
    {
        return 'Mein Custom Widget';
    }

    public function getData()
    {
        return '<p>Hier steht der Inhalt</p>';
    }
}
```

### Widget registrieren

```php
// In der boot.php des eigenen AddOns
if (rex::isBackend() && rex_addon::exists('dashboard')) {
    rex_dashboard::addItem(
        MeinCustomWidget::factory('mein-widget-id', 'Mein Widget')
            ->setColumns(2) // 1 oder 2 Spalten
    );
}
```

### Verfügbare Basis-Klassen

- `rex_dashboard_item` - Basis-Widget
- `rex_dashboard_item_chart_bar` - Balkendiagramm
- `rex_dashboard_item_chart_line` - Liniendiagramm  
- `rex_dashboard_item_chart_pie` - Kreisdiagramm
- `rex_dashboard_item_table` - Tabellen-Widget

## 📊 Widget-Typen im Detail

### Chart-Widgets

```php
class MeinChartWidget extends rex_dashboard_item_chart_bar
{
    public function getChartData()
    {
        return [
            'Label 1' => 42,
            'Label 2' => 37,
            'Label 3' => 28
        ];
    }
}
```

### Tabellen-Widgets

```php
class MeinTabellenWidget extends rex_dashboard_item_table
{
    public function getTableData()
    {
        return [
            'headers' => ['Name', 'Wert', 'Status'],
            'rows' => [
                ['Eintrag 1', '100', 'Aktiv'],
                ['Eintrag 2', '200', 'Inaktiv']
            ]
        ];
    }
}
```

## 🔐 Berechtigungen und Sicherheit

### Structure-Berechtigungen

Artikel-Widgets prüfen automatisch:

```php
$user = rex::requireUser();
$structurePerm = $user->getComplexPerm('structure');
if ($structurePerm->hasCategoryPerm($categoryId)) {
    // User hat Zugriff auf diese Kategorie
}
```

### Admin-Only Widgets

```php
if (rex::getUser() && rex::getUser()->isAdmin()) {
    // Widget nur für Admins registrieren
    rex_dashboard::addItem(AdminWidget::factory('admin-widget', 'Admin Widget'));
}
```

## 🎛️ Erweiterte Features

### Auto-Refresh

- Dashboard refresht automatisch beim Laden (500ms Verzögerung)
- Automatisches Refresh alle 5 Minuten
- Manueller Refresh über Refresh-Button

### GridStack Integration

- Drag & Drop Positionierung
- Automatische Größenanpassung
- Benutzer-spezifische Layouts (je User individuell gespeichert)
- Responsive Breakpoints

### Multi-Language Support

- Widgets passen sich automatisch an verfügbare Sprachen an  
- Sprachenspalten werden nur angezeigt wenn > 1 Sprache vorhanden

## 📱 Responsive Design

Das Dashboard passt sich automatisch an verschiedene Bildschirmgrößen an:

- **Desktop**: Vollständiges Grid mit Drag & Drop
- **Tablet**: Optimierte Spaltenbreiten  
- **Mobile**: Single-Column Layout mit vertikalem Scrolling

## 🔄 Migration von Version 1.x

### Automatische Migration

- Demo-Plugin wird automatisch aufgelöst
- Bestehende Widget-Positionen bleiben erhalten
- Neue Standard-Widgets werden deaktiviert hinzugefügt

### Breaking Changes

- Demo-Plugin entfernt (Funktionalität ins Core integriert)
- Neue Konfigurationsstruktur
- Widget-IDs geändert (`dashboard-default-*` Präfix)

## 🐛 Debugging

### Debug-Modus

Im REDAXO Debug-Modus werden zusätzliche Informationen angezeigt:

- Widget-Ladezeiten
- Berechtigungsprüfungen  
- Cache-Status
- JavaScript-Fehler

### Log-Dateien

Fehler werden in REDAXO's System-Log geschrieben:
```
redaxo/data/log/system.log
```

## 🤝 Kompatibilität

- **REDAXO**: >= 5.11.0
- **PHP**: >= 7.4
- **Browser**: Moderne Browser mit ES6-Support
- **Mobile**: iOS Safari, Chrome Mobile, Firefox Mobile

## 📄 Lizenz

MIT License - siehe LICENSE-Datei

## 👥 Credits

- **Entwicklung**: Friends of REDAXO
- **GridStack**: https://gridstackjs.com/
- **Chart.js**: https://www.chartjs.org/
- **Bootstrap**: https://getbootstrap.com/

## 📞 Support

- **GitHub**: https://github.com/FriendsOfREDAXO/dashboard
- **REDAXO Slack**: #addons Channel
- **Forum**: https://redaxo.org/forum/

---

**Dashboard AddOn 2.0** - Modernes Dashboard für REDAXO 5.x mit Standard-Widgets, Sicherheitsfeatures und verbessertem UX.

### rex_dashboard_item

```php
class rex_dashboard_item_demo extends rex_dashboard_item
{
    public function getData()
    {
        return 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.';
    }
}
```

### rex_dashboard_item_chart_bar

```php

class rex_dashboard_item_my_chart_bar_horizontal extends rex_dashboard_item_chart_bar
{
    protected function __construct($id, $name)
    {
        parent::__construct($id, $name);
        $this->setHorizontal(); // optional, sonst vertikal
    }

    public function getChartData()
    {
        return [
            'Rot' => rand(1,122),
            'Blau' => rand(1,122),
            'Gelb' => rand(1,122),
            'Grün' => rand(1,122),
            'Lila' => rand(1,122),
            'Orange' => rand(1,122),
        ];
    }
}

```

### rex_dashboard_item_chart_line

```php

class rex_dashboard_item_chart_line_demo extends rex_dashboard_item_chart_line
{
    public function getChartData()
    {
        return [
            'Linie 1' => [
                'Rot' => 12,
                'Blau' => 19,
                'Gelb' => 3,
                'Grün' => 5,
                'Lila' => 2,
                'Orange' => 3,
            ],
            'Linie 2' => [
                'Rot' => 3,
                'Blau' => 5,
                'Gelb' => 8,
                'Grün' => 10,
                'Lila' => 11,
                'Orange' => 11.5,
            ],
            'Linie 3' => [
                'Rot' => 5,
                'Blau' => 13,
                'Gelb' => 16,
                'Grün' => 12,
                'Lila' => 7,
                'Orange' => 2,
            ]
        ];
    }
}

```

### rex_dashboard_item_chart_pie

```php

class rex_dashboard_item_chart_pie_demo extends rex_dashboard_item_chart_pie
{
    public function getChartData()
    {
        return [
            'Rot' => 12,
            'Blau' => 19,
            'Gelb' => 3,
            'Grün' => 5,
            'Lila' => 2,
            'Orange' => 3,
        ];
    }
}

```
### rex_dashboard_item_table

```php

class rex_dashboard_item_table_demo extends rex_dashboard_item_table
{
    protected $header = [];
    protected $data = [];

    protected function getTableData()
    {
        $tableData = rex_sql::factory()->setQuery('
            SELECT  id ID
                    , label Label
                    , dbtype `DB-Type`
            FROM rex_metainfo_type
            ORDER BY id ASC
        ')->getArray();

        if (!empty($tableData)) {
            $this->data = $tableData;
            $this->header = array_keys($tableData[0]);
        }

        return [
            'data' => $this->data,
            'header' => $this->header,
        ];
    }
}

```

## Anmeldung der eigenen Widgets

In der boot.php des eigenen Project-AddOns oder in der jeweiligen boot.php des entsprechenden AddOns (Für AddOn-Entwickler) müssen die entsprechenden Widgets angemeldet werden.

Hier ein Beispiel für die Anmeldung der Widgets aus dem DemoPlugin, siehe oben:

```php

if (rex::isBackend() && rex_addon::exists('dashboard')) {

    rex_dashboard::addItem(
        rex_dashboard_item_demo::factory('dashboard-demo-1', 'Demo 1')
    );

    rex_dashboard::addItem(
        rex_dashboard_item_demo::factory('dashboard-demo-2', 'Demo 2')
            ->setColumns(2)
    );

    rex_dashboard::addItem(
        rex_dashboard_item_demo::factory('dashboard-demo-3', 'Demo 3')
            ->setColumns(3)
    );

    rex_dashboard::addItem(
        rex_dashboard_item_chart_bar_horizontal::factory('dashboard-demo-chart-bar-horizontal', 'Chartdemo Balken horizontal')
    );

    rex_dashboard::addItem(
        rex_dashboard_item_chart_bar_vertical::factory('dashboard-demo-chart-bar-vertical', 'Chartdemo Balken vertikal')
    );

    rex_dashboard::addItem(
        rex_dashboard_item_chart_pie_demo::factory('dashboard-demo-chart-pie', 'Chartdemo Kreisdiagramm')
    );

    rex_dashboard::addItem(
        rex_dashboard_item_chart_pie_demo::factory('dashboard-demo-chart-donut', 'Chartdemo Donutdiagramm')
            ->setDonut()
    );

    rex_dashboard::addItem(
        rex_dashboard_item_table_demo::factory('dashboard-demo-table-sql', 'Tabelle (SQL)')
            ->setTableAttribute('data-locale', 'de-DE')
    );

    rex_dashboard::addItem(
        rex_dashboard_item_chart_line_demo::factory('dashboard-demo-chart-line', 'Liniendiagramm')
    );

);

```

## Auswählen des Widgets im Dashboard

Sobald die Widgets angemeldet sind, können sie im Dashboard ausgewählt und angeordnet werden. Dazu klickt man im Widget auf `Widget auswählen`.
