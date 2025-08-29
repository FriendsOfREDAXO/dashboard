<?php

namespace FriendsOfREDAXO\Dashboard;

use rex_dashboard_item_chart_pie;
use rex_sql;
use rex_i18n;
use rex;
use rex_addon;

/**
 * Dashboard Item: Medien-Speicherverbrauch nach Dateityp
 */
class DashboardItemMediaStorage extends rex_dashboard_item_chart_pie
{
    public function getTitle(): string
    {
        return rex_i18n::msg('dashboard_media_storage_title', 'Medien-Speicherverbrauch nach Kategorie');
    }

    public function getChartData()
    {
        $sql = rex_sql::factory();
        
        // Prüfe ob Mediapool-Addon aktiv ist
        if (!rex_addon::get('mediapool')->isAvailable()) {
            return ['Kein Mediapool verfügbar' => 1];
        }
        
        // Definiere Kategorien basierend auf Dateitypen
        $categories = [
            'Dokumente' => ['pdf', 'doc', 'docx', 'txt', 'rtf', 'odt', 'xls', 'xlsx', 'ppt', 'pptx'],
            'Bilder' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp', 'tiff', 'ico'],
            'Videos' => ['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm', 'mkv', 'm4v'],
            'Audio' => ['mp3', 'wav', 'ogg', 'flac', 'aac', 'm4a', 'wma'],
            'Archive' => ['zip', 'rar', '7z', 'tar', 'gz', 'bz2'],
            'Web-Dateien' => ['css', 'js', 'html', 'htm', 'xml', 'json'],
        ];
        
        $query = '
            SELECT 
                LOWER(SUBSTRING_INDEX(filename, ".", -1)) as extension,
                SUM(filesize) as total_size,
                COUNT(*) as file_count
            FROM ' . rex::getTable('media') . ' 
            WHERE filesize > 0
            GROUP BY extension
            ORDER BY total_size DESC
        ';
        
        $data = $sql->getArray($query);
        $categoryData = [];
        $otherSize = 0;
        $otherCount = 0;
        
        if (empty($data)) {
            return ['Keine Mediendateien gefunden' => 1];
        }
        
        // Initialisiere Kategorien
        foreach ($categories as $categoryName => $extensions) {
            $categoryData[$categoryName] = ['size' => 0, 'count' => 0];
        }
        
        // Gruppiere Dateien nach Kategorien
        foreach ($data as $row) {
            $extension = strtolower($row['extension'] ?: '');
            $size = $row['total_size'];
            $count = $row['file_count'];
            
            $assigned = false;
            foreach ($categories as $categoryName => $extensions) {
                if (in_array($extension, $extensions)) {
                    $categoryData[$categoryName]['size'] += $size;
                    $categoryData[$categoryName]['count'] += $count;
                    $assigned = true;
                    break;
                }
            }
            
            // Wenn keine Kategorie passt, zu "Sonstige" hinzufügen
            if (!$assigned) {
                $otherSize += $size;
                $otherCount += $count;
            }
        }
        
        // Sonstige hinzufügen falls vorhanden
        if ($otherSize > 0) {
            $categoryData['Sonstige'] = ['size' => $otherSize, 'count' => $otherCount];
        }
        
        // Erstelle Chart-Daten
        $chartData = [];
        foreach ($categoryData as $categoryName => $info) {
            if ($info['size'] > 0) {
                $sizeInMB = round($info['size'] / (1024 * 1024), 2);
                $label = $categoryName . ' (' . $info['count'] . ' Dateien)';
                $chartData[$label] = $sizeInMB;
            }
        }
        
        // Sortiere nach Größe
        arsort($chartData);
        
        return empty($chartData) ? ['Keine Mediendateien gefunden' => 1] : $chartData;
    }
    
    protected function __construct($id, $name)
    {
        parent::__construct($id, $name);
        
        // Chart-Optionen anpassen
        $this->setOptions([
            'plugins' => [
                'tooltip' => [
                    'callbacks' => [
                        'label' => 'function(context) { return context.label + ": " + context.parsed + " MB"; }'
                    ]
                ]
            ]
        ]);
    }
}
