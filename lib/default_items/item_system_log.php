<?php

namespace FriendsOfREDAXO\Dashboard;

use rex_dashboard_item;
use rex_i18n;
use rex;
use rex_logger;
use rex_log_file;
use rex_formatter;
use rex_escape;
use rex_url;
use LimitIterator;
use IntlDateFormatter;
use Exception;

/**
 * Dashboard Item: System-Log (nur für Admins)
 */
class DashboardItemSystemLog extends rex_dashboard_item
{
    public function getTitle(): string
    {
        return rex_i18n::msg('dashboard_system_log_title', 'System-Log');
    }

    public function isAvailable(): bool
    {
        // Nur für Admins verfügbar
        $user = rex::getUser();
        return $user && $user->isAdmin();
    }

    public function getData()
    {
        $logFile = rex_logger::getPath();
        
        if (!is_file($logFile) || !is_readable($logFile) || filesize($logFile) <= 0) {
            return '<div class="alert alert-info">
                        <i class="fa fa-info-circle"></i> 
                        ' . rex_i18n::msg('syslog_empty', 'Log-Datei ist leer oder nicht lesbar') . '
                    </div>';
        }

        try {
            $content = '<div class="system-log-widget">';
            
            // Log-Einträge laden (letzte 10)
            $file = rex_log_file::factory($logFile);
            $entries = [];
            
            foreach (new LimitIterator($file, 0, 10) as $entry) {
                $entries[] = $entry;
            }
            
            if (empty($entries)) {
                return '<div class="alert alert-info">
                            <i class="fa fa-info-circle"></i> 
                            Keine Log-Einträge gefunden
                        </div>';
            }

            $content .= '<div class="log-entries">';
            
            foreach (array_reverse($entries) as $entry) {
                /** @var rex_log_entry $entry */
                $data = $entry->getData();
                
                $type = rex_escape($data[0] ?? 'INFO');
                $message = rex_escape($data[1] ?? 'Keine Nachricht');
                $file = $data[2] ?? null;
                $line = $data[3] ?? null;
                
                // CSS-Klasse je nach Log-Level
                $class = match (strtolower($type)) {
                    'success' => 'success',
                    'debug' => 'default',
                    'info', 'notice', 'deprecated' => 'info',
                    'warning' => 'warning',
                    default => 'danger'
                };
                
                $content .= '<div class="log-entry">';
                $content .= '<div class="log-header">';
                $content .= '<span class="log-timestamp">' . rex_formatter::intlDateTime($entry->getTimestamp(), [IntlDateFormatter::SHORT, IntlDateFormatter::MEDIUM]) . '</span>';
                $content .= '<span class="label label-' . $class . '">' . $type . '</span>';
                $content .= '</div>';
                
                $content .= '<div class="log-message">' . nl2br($message) . '</div>';
                
                // Datei und Zeile anzeigen wenn vorhanden
                if ($file && $line) {
                    $content .= '<div class="log-file">';
                    $content .= '<small class="text-muted">';
                    $content .= '<i class="fa fa-file-code-o"></i> ';
                    $content .= rex_escape(basename($file)) . ':' . $line;
                    $content .= '</small>';
                    $content .= '</div>';
                }
                
                $content .= '</div>';
            }
            
            $content .= '</div>';
            
            // Link zum vollständigen Log
            $content .= '<div class="log-actions" style="margin-top: 15px; text-align: center;">';
            $content .= '<a href="' . rex_url::backendPage('system/log/redaxo') . '" class="btn btn-default btn-sm">';
            $content .= '<i class="fa fa-external-link"></i> ';
            $content .= rex_i18n::msg('dashboard_system_log_view_all', 'Vollständiges Log anzeigen');
            $content .= '</a>';
            $content .= '</div>';
            
            $content .= '</div>';

        } catch (Exception $e) {
            return '<div class="alert alert-warning">
                        <i class="fa fa-exclamation-triangle"></i> 
                        Fehler beim Laden der Log-Einträge: ' . rex_escape($e->getMessage()) . '
                    </div>';
        }

        // CSS für das Widget
        $content .= '
        <style>
        .system-log-widget .log-entries {
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid #ddd;
            border-radius: 4px;
            background: #fafafa;
        }
        
        .system-log-widget .log-entry {
            padding: 10px;
            border-bottom: 1px solid #eee;
            font-size: 12px;
        }
        
        .system-log-widget .log-entry:last-child {
            border-bottom: none;
        }
        
        .system-log-widget .log-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 5px;
        }
        
        .system-log-widget .log-timestamp {
            font-weight: 500;
            color: #666;
        }
        
        .system-log-widget .log-message {
            color: #333;
            font-weight: 500;
            margin-bottom: 5px;
        }
        
        .system-log-widget .log-file {
            margin-top: 5px;
        }
        
        .system-log-widget .label {
            font-size: 10px;
            padding: 2px 6px;
        }
        
        /* Dark Theme Support */
        body.rex-theme-dark .system-log-widget .log-entries {
            background: #2d3142;
            border-color: #495057;
        }
        
        body.rex-theme-dark .system-log-widget .log-entry {
            border-bottom-color: #495057;
        }
        
        body.rex-theme-dark .system-log-widget .log-timestamp {
            color: #adb5bd;
        }
        
        body.rex-theme-dark .system-log-widget .log-message {
            color: #f8f9fa;
        }
        </style>';

        return $content;
    }
}
