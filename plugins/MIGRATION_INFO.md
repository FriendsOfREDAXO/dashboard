# Dashboard Demo Plugin wurde aufgelöst

Das Dashboard Demo Plugin wurde aufgelöst und die Demo-Funktionalität wurde in das Hauptaddon integriert.

## Was ist passiert?
- Alle Demo-Klassen wurden nach `lib/demo_items/` verschoben
- Eine Config-Seite für Admins wurde hinzugefügt (nur für Admins sichtbar)
- Das Zahnradsymbol im Dashboard-Header führt zur Konfiguration
- Die Demo-Elemente können über die Config-Seite aktiviert/deaktiviert werden

## Migration
Nach einem Cache-Clear werden die Demo-Elemente standardmäßig deaktiviert sein. 
Admins können sie über Dashboard > Konfiguration wieder aktivieren.

Das Plugin-Verzeichnis kann nach erfolgreichem Test gelöscht werden.
