<?php

/**
 * Install-Script für Dashboard AddOn
 * Erstellt die Tabelle für Schnellnotizen
 */

$sql = rex_sql::factory();

// Tabelle für Schnellnotizen erstellen
$sql->setQuery('
    CREATE TABLE IF NOT EXISTS `' . rex::getTable('dashboard_notes') . '` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11) NOT NULL,
        `notes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
        `created_at` datetime NOT NULL,
        `updated_at` datetime NOT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `user_id` (`user_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
');

// Migration: Bestehende Quick Notes aus rex_config in neue Tabelle verschieben
try {
    $configSql = rex_sql::factory();
    $configSql->setQuery("SELECT `value` FROM " . rex::getTable('config') . " WHERE `namespace` = 'dashboard' AND `key` LIKE 'dashboard_quick_notes_%'");
    
    while ($configSql->hasNext()) {
        $configValue = $configSql->getValue('value');
        $configKey = $configSql->getValue('key');
        
        // User-ID aus Key extrahieren (dashboard_quick_notes_[USER_ID])
        if (preg_match('/dashboard_quick_notes_(\d+)/', $configKey, $matches)) {
            $userId = (int)$matches[1];
            $notes = $configValue;
            
            // In neue Tabelle einfügen
            $migrationSql = rex_sql::factory();
            $migrationSql->setTable(rex::getTablePrefix() . 'dashboard_notes');
            $migrationSql->setValue('user_id', $userId);
            $migrationSql->setValue('notes', $notes);
            $migrationSql->setDateTimeValue('created_at', time());
            $migrationSql->setDateTimeValue('updated_at', time());
            
            try {
                $migrationSql->insert();
                
                // Alten Config-Eintrag löschen
                $deleteSql = rex_sql::factory();
                $deleteSql->setQuery("DELETE FROM " . rex::getTable('config') . " WHERE `namespace` = 'dashboard' AND `key` = ?", [$configKey]);
                
            } catch (Exception $e) {
                // Falls Benutzer bereits existiert, ignorieren
            }
        }
        
        $configSql->next();
    }
} catch (Exception $e) {
    // Migration-Fehler ignorieren
}


