<?php

namespace FriendsOfREDAXO\Dashboard;

use rex_dashboard_item;
use rex_i18n;
use rex;
use rex_addon;
use rex_sql;
use rex_escape;

/**
 * Dashboard Item: Backup Status
 */
class DashboardItemBackupStatus extends rex_dashboard_item
{
    public function getTitle(): string
    {
        return rex_i18n::msg('dashboard_backup_status_title', 'Backup-Status');
    }

    public function isAvailable(): bool
    {
        // Nur für Admins verfügbar
        $user = rex::getUser();
        return $user && $user->isAdmin();
    }

    public function getData()
    {
        // Prüfen ob Backup-Addon verfügbar ist
        $backupAddon = rex_addon::get('backup');
        if (!$backupAddon->isAvailable()) {
            return '<div class="alert alert-warning">
                        <i class="fa fa-exclamation-triangle"></i> 
                        Backup-Addon ist nicht verfügbar
                    </div>';
        }

        try {
            // Backup-Informationen sammeln
            $backupDir = $backupAddon->getDataPath();
            $sqlBackups = $this->getBackupFiles($backupDir, '*.sql');
            $fileBackups = $this->getBackupFiles($backupDir, '*.tar.gz');
            
            $content = '<div class="row">';
            
            // SQL Backup Info
            $content .= '<div class="col-sm-6">';
            $content .= '<h5>SQL-Backup</h5>';
            $content .= '<table class="table table-condensed">';
            
            if (!empty($sqlBackups)) {
                $lastSqlBackup = reset($sqlBackups);
                $content .= '<tr><td>Letztes Backup:</td><td><strong>' . date('d.m.Y H:i', $lastSqlBackup['created']) . '</strong></td></tr>';
                $content .= '<tr><td>Größe:</td><td>' . $this->formatFileSize($lastSqlBackup['size']) . '</td></tr>';
                $content .= '<tr><td>Anzahl Backups:</td><td>' . count($sqlBackups) . '</td></tr>';
            } else {
                $content .= '<tr><td colspan="2"><em>Keine SQL-Backups vorhanden</em></td></tr>';
            }
            
            $content .= '</table>';
            $content .= '</div>';
            
            // Datei Backup Info
            $content .= '<div class="col-sm-6">';
            $content .= '<h5>Datei-Backup</h5>';
            $content .= '<table class="table table-condensed">';
            
            if (!empty($fileBackups)) {
                $lastFileBackup = reset($fileBackups);
                $content .= '<tr><td>Letztes Backup:</td><td><strong>' . date('d.m.Y H:i', $lastFileBackup['created']) . '</strong></td></tr>';
                $content .= '<tr><td>Größe:</td><td>' . $this->formatFileSize($lastFileBackup['size']) . '</td></tr>';
                $content .= '<tr><td>Anzahl Backups:</td><td>' . count($fileBackups) . '</td></tr>';
            } else {
                $content .= '<tr><td colspan="2"><em>Keine Datei-Backups vorhanden</em></td></tr>';
            }
            
            $content .= '</table>';
            $content .= '</div>';
            
            $content .= '</div>';
            
            return $content;
            
        } catch (\Exception $e) {
            return '<div class="alert alert-danger">
                        <i class="fa fa-exclamation-circle"></i> 
                        Fehler beim Laden der Backup-Informationen: ' . rex_escape($e->getMessage()) . '
                    </div>';
        }
    }
    
    private function getBackupFiles($backupDir, $pattern): array
    {
        $backups = [];
        
        if (is_dir($backupDir)) {
            $files = glob($backupDir . $pattern);
            
            foreach ($files as $file) {
                if (is_file($file)) {
                    $backups[] = [
                        'filename' => basename($file),
                        'size' => filesize($file),
                        'created' => filemtime($file)
                    ];
                }
            }
            
            // Nach Erstellungsdatum sortieren (neueste zuerst)
            usort($backups, function($a, $b) {
                return $b['created'] - $a['created'];
            });
        }
        
        return $backups;
    }
    
    private function formatFileSize($size): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }
        
        return round($size, 1) . ' ' . $units[$i];
    }
}
