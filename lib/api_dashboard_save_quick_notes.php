<?php

/**
 * API für das Speichern der Schnellnotizen
 */
class rex_api_dashboard_save_quick_notes extends rex_api_function
{
    protected $published = true;

    public function execute()
    {
        // Debug-Logging aktivieren
        error_log('Dashboard Notes API called');
        
        // Nur für eingeloggte Benutzer
        $user = rex::getUser();
        if (!$user) {
            error_log('Dashboard Notes: No user logged in');
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Keine Berechtigung']);
            exit;
        }

        $notes = rex_request('notes', 'string', '');
        $userId = $user->getId();
        
        error_log('Dashboard Notes: User ID: ' . $userId . ', Notes length: ' . strlen($notes));

        try {
            // Prüfen ob Tabelle existiert
            $sql = rex_sql::factory();
            $tableName = rex::getTable('dashboard_notes');
            
            // Tabelle erstellen falls nicht vorhanden
            $sql->setQuery("SHOW TABLES LIKE '$tableName'");
            if ($sql->getRows() == 0) {
                error_log('Dashboard Notes: Creating table');
                $sql->setQuery('
                    CREATE TABLE `' . $tableName . '` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        `user_id` int(11) NOT NULL,
                        `notes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
                        `created_at` datetime NOT NULL,
                        `updated_at` datetime NOT NULL,
                        PRIMARY KEY (`id`),
                        UNIQUE KEY `user_id` (`user_id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
                ');
            }
            
            // Notizen speichern (direkter SQL-Code ohne externe Klasse)
            $checkSql = rex_sql::factory();
            $checkSql->setQuery('SELECT id FROM ' . $tableName . ' WHERE user_id = ?', [$userId]);
            
            if ($checkSql->getRows() > 0) {
                // Update
                error_log('Dashboard Notes: Updating existing record');
                $updateSql = rex_sql::factory();
                $updateSql->setTable(rex::getTablePrefix() . 'dashboard_notes');
                $updateSql->setWhere('user_id = :user_id', ['user_id' => $userId]);
                $updateSql->setValue('notes', $notes);
                $updateSql->setDateTimeValue('updated_at', time());
                $updateSql->update();
            } else {
                // Insert
                error_log('Dashboard Notes: Creating new record');
                $insertSql = rex_sql::factory();
                $insertSql->setTable(rex::getTablePrefix() . 'dashboard_notes');
                $insertSql->setValue('user_id', $userId);
                $insertSql->setValue('notes', $notes);
                $insertSql->setDateTimeValue('created_at', time());
                $insertSql->setDateTimeValue('updated_at', time());
                $insertSql->insert();
            }
            
            error_log('Dashboard Notes: Success');
            echo json_encode(['success' => true, 'message' => 'Notizen gespeichert']);
            
        } catch (Exception $e) {
            error_log('Dashboard Notes Error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Fehler beim Speichern: ' . $e->getMessage()]);
        }
        
        exit;
    }
}
