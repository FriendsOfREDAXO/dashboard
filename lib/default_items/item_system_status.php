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

    public function getData()
    {
        // System-Informationen sammeln
        $phpVersion = PHP_VERSION;
        $redaxoVersion = rex::getVersion();
        $memoryLimit = ini_get('memory_limit');
        $maxExecutionTime = ini_get('max_execution_time');
        
        // Website-Speicherverbrauch ab Webroot
        $webrootDir = rex_path::base();
        $cacheDir = rex_path::cache();
        $dataDir = rex_path::data();
        $mediaDir = rex_path::media();
        $vendorDir = $webrootDir . 'vendor/';
        $assetsDir = $webrootDir . 'assets/';
        $dockerDir = $webrootDir . '.docker/';
        
        $totalWebsiteSize = $this->getDirSize($webrootDir);
        $cacheSize = $this->getDirSize($cacheDir);
        $dataSize = $this->getDirSize($dataDir);
        $mediaSize = $this->getDirSize($mediaDir);
        $vendorSize = is_dir($vendorDir) ? $this->getDirSize($vendorDir) : 0;
        $assetsSize = is_dir($assetsDir) ? $this->getDirSize($assetsDir) : 0;
        $dockerSize = is_dir($dockerDir) ? $this->getDirSize($dockerDir) : 0;
        
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
        
        // Website-Speicher Info
        $content .= '<div class="col-sm-6">';
        $content .= '<h5>Website-Speicherverbrauch</h5>';
        $content .= '<table class="table table-condensed">';
        $content .= '<tr style="border-top: 2px solid #ddd;"><td><strong>Gesamte Website:</strong></td><td><strong>' . $this->formatBytes($totalWebsiteSize) . '</strong></td></tr>';
        
        // Zeige die größten Verzeichnisse zuerst
        $directories = [
            'Vendor-Verzeichnis' => $vendorSize,
            'Docker-Daten (.docker)' => $dockerSize,
            'Media-Verzeichnis' => $mediaSize,
            'Assets-Verzeichnis' => $assetsSize,
            'Cache-Verzeichnis' => $cacheSize,
            'Data-Verzeichnis' => $dataSize,
        ];
        
        // Sortiere nach Größe (größte zuerst)
        arsort($directories);
        
        foreach ($directories as $name => $size) {
            if ($size > 0) {
                $percentage = $totalWebsiteSize > 0 ? round(($size / $totalWebsiteSize) * 100, 1) : 0;
                $content .= '<tr><td>&nbsp;&nbsp;↳ ' . $name . ':</td><td>' . $this->formatBytes($size) . ' <small>(' . $percentage . '%)</small></td></tr>';
            }
        }
        
        $content .= '</table>';
        $content .= '</div>';
        
        $content .= '</div>';
        
        // Warnung bei ungewöhnlich großen Websites
        if ($totalWebsiteSize > 500 * 1024 * 1024) { // > 500 MB
            $content .= '<div class="row" style="margin-top: 15px;">';
            $content .= '<div class="col-sm-12">';
            $content .= '<div class="alert alert-info">';
            $content .= '<strong>Entwicklungsumgebung erkannt:</strong> ';
            if ($vendorSize > 300 * 1024 * 1024 && $dockerSize > 50 * 1024 * 1024) {
                $content .= 'Diese Größe ist normal für Entwicklung (Vendor + Docker). Live-Website wäre nur ~' . $this->formatBytes($totalWebsiteSize - $vendorSize - $dockerSize) . '.';
            } elseif ($vendorSize > 300 * 1024 * 1024) {
                $content .= 'Das Vendor-Verzeichnis enthält Entwicklungs-Tools. Live-Website wäre nur ~' . $this->formatBytes($totalWebsiteSize - $vendorSize) . '.';
            } elseif ($dockerSize > 100 * 1024 * 1024) {
                $content .= 'Docker-Datenbank-Dateien sind groß - das ist normal für Entwicklungsumgebungen.';
            } else {
                $content .= 'Prüfen Sie, ob alle Dateien benötigt werden.';
            }
            $content .= '</div>';
            $content .= '</div>';
            $content .= '</div>';
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
            try {
                $files = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS)
                );
                
                foreach ($files as $file) {
                    try {
                        // Überspringe spezielle Dateien (Sockets, Pipes, etc.)
                        if ($file->isFile() && $file->isReadable()) {
                            $size += $file->getSize();
                        }
                    } catch (\Exception $e) {
                        // Überspringe Dateien, die nicht gelesen werden können
                        continue;
                    }
                }
            } catch (\Exception $e) {
                // Falls das Verzeichnis nicht lesbar ist, gib 0 zurück
                return 0;
            }
        }
        
        return $size;
    }
}
