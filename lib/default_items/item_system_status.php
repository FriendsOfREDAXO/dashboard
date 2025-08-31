<?php

namespace FriendsOfREDAXO\Dashboard;

use rex_dashboard_item;
use rex_i18n;
use rex;
use rex_formatter;
use rex_path;
use rex_escape;

/**
 * Dashboard Item: System-Status
 */
class DashboardItemSystemStatus extends rex_dashboard_item
{
    public function getTitle(): string
    {
        return rex_i18n::msg('dashboard_system_status_title', 'System-Status');
    }

    public function isAvailable(): bool
    {
        // Nur für Admins verfügbar
        $user = rex::getUser();
        return $user && $user->isAdmin();
    }

    public function getData()
    {
        // System-Informationen sammeln
        $phpVersion = PHP_VERSION;
        $redaxoVersion = rex::getVersion();
        $memoryLimit = ini_get('memory_limit');
        $maxExecutionTime = ini_get('max_execution_time');
        $diskFreeSpace = $this->formatBytes(\disk_free_space(rex_path::base()));
        $diskTotalSpace = $this->formatBytes(\disk_total_space(rex_path::base()));
        
        // Cache-Informationen
        $cacheDir = rex_path::cache();
        $cacheSize = $this->getDirSize($cacheDir);
        
        // Aktuelle Benutzer (wenn Backend-Sessions vorhanden)
        $currentUsers = $this->getCurrentUsers();
        
        $content = '<div class="row">';
        
        // System Info
        $content .= '<div class="col-sm-6">';
        $content .= '<h5>System</h5>';
        $content .= '<table class="table table-condensed">';
        $content .= '<tr><td>REDAXO Version:</td><td><strong>' . $redaxoVersion . '</strong></td></tr>';
        $content .= '<tr><td>PHP Version:</td><td><strong>' . $phpVersion . '</strong></td></tr>';
        $content .= '<tr><td>Memory Limit:</td><td>' . $memoryLimit . '</td></tr>';
        $content .= '<tr><td>Max Execution Time:</td><td>' . $maxExecutionTime . 's</td></tr>';
        $content .= '</table>';
        $content .= '</div>';
        
        // Speicher Info
        $content .= '<div class="col-sm-6">';
        $content .= '<h5>Speicher</h5>';
        $content .= '<table class="table table-condensed">';
        $content .= '<tr><td>Freier Speicher:</td><td><strong>' . $diskFreeSpace . '</strong></td></tr>';
        $content .= '<tr><td>Gesamtspeicher:</td><td>' . $diskTotalSpace . '</td></tr>';
        $content .= '<tr><td>Cache-Größe:</td><td>' . $this->formatBytes($cacheSize) . '</td></tr>';
        $content .= '</table>';
        $content .= '</div>';
        
        $content .= '</div>';
        
        // Benutzer-Info
        if (!empty($currentUsers)) {
            $content .= '<div class="row" style="margin-top: 15px;">';
            $content .= '<div class="col-sm-12">';
            $content .= '<h5>Aktuelle Benutzer (' . count($currentUsers) . ')</h5>';
            $content .= '<div class="row">';
            
            foreach ($currentUsers as $user) {
                $content .= '<div class="col-sm-4">';
                $content .= '<div class="panel panel-default">';
                $content .= '<div class="panel-body text-center">';
                $content .= '<strong>' . rex_escape($user) . '</strong>';
                $content .= '</div></div></div>';
            }
            
            $content .= '</div></div></div>';
        }
        
        return $content;
    }
    
    private function formatBytes($size, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }
        
        return round($size, $precision) . ' ' . $units[$i];
    }
    
    private function getDirSize($dir)
    {
        $size = 0;
        if (is_dir($dir)) {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS)
            );
            
            foreach ($files as $file) {
                $size += $file->getSize();
            }
        }
        
        return $size;
    }
    
    private function getCurrentUsers()
    {
        // Vereinfachte Implementierung - könnte erweitert werden
        // mit Session-Tracking oder Login-Log
        $user = rex::getUser();
        return $user ? [$user->getLogin()] : [];
    }
}
