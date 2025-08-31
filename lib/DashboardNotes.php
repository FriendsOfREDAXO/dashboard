<?php

namespace FriendsOfREDAXO\Dashboard;

use rex_sql;
use rex;

/**
 * Verwaltung der Benutzer-Schnellnotizen
 */
class DashboardNotes
{
    /**
     * Notizen für einen Benutzer laden
     */
    public static function getNotes(int $userId): string
    {
        try {
            $sql = rex_sql::factory();
            $tableName = rex::getTable('dashboard_notes');
            
            // Prüfen ob Tabelle existiert
            $sql->setQuery("SHOW TABLES LIKE '$tableName'");
            if ($sql->getRows() == 0) {
                // Tabelle existiert nicht, leer zurückgeben
                return '';
            }
            
            $sql->setQuery('SELECT notes FROM ' . $tableName . ' WHERE user_id = ?', [$userId]);
            
            if ($sql->getRows() > 0) {
                return $sql->getValue('notes') ?? '';
            }
            
            return '';
            
        } catch (\Exception $e) {
            error_log('Dashboard Notes getNotes Error: ' . $e->getMessage());
            return '';
        }
    }
    
    /**
     * Notizen für einen Benutzer speichern
     */
    public static function saveNotes(int $userId, string $notes): bool
    {
        try {
            $sql = rex_sql::factory();
            
            // Prüfen ob bereits Eintrag vorhanden
            $sql->setQuery('SELECT id FROM ' . rex::getTable('dashboard_notes') . ' WHERE user_id = ?', [$userId]);
            
            if ($sql->getRows() > 0) {
                // Update
                $updateSql = rex_sql::factory();
                $updateSql->setTable(rex::getTablePrefix() . 'dashboard_notes');
                $updateSql->setWhere('user_id = :user_id', ['user_id' => $userId]);
                $updateSql->setValue('notes', $notes);
                $updateSql->setDateTimeValue('updated_at', time());
                $updateSql->update();
            } else {
                // Insert
                $insertSql = rex_sql::factory();
                $insertSql->setTable(rex::getTablePrefix() . 'dashboard_notes');
                $insertSql->setValue('user_id', $userId);
                $insertSql->setValue('notes', $notes);
                $insertSql->setDateTimeValue('created_at', time());
                $insertSql->setDateTimeValue('updated_at', time());
                $insertSql->insert();
            }
            
            return true;
            
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Notizen für einen Benutzer löschen
     */
    public static function deleteNotes(int $userId): bool
    {
        try {
            $sql = rex_sql::factory();
            $sql->setQuery('DELETE FROM ' . rex::getTable('dashboard_notes') . ' WHERE user_id = ?', [$userId]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Alle Notizen löschen (für Deinstallation)
     */
    public static function deleteAllNotes(): bool
    {
        try {
            $sql = rex_sql::factory();
            $sql->setQuery('DROP TABLE IF EXISTS ' . rex::getTable('dashboard_notes'));
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
