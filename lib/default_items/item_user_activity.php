<?php

namespace FriendsOfREDAXO\Dashboard;

use rex_dashboard_item_chart_line;
use rex_sql;
use rex_sql_table;
use rex_i18n;
use rex;

/**
 * Dashboard Item: Benutzer-Aktivität (Artikel-Bearbeitungen der letzten 7 Tage)
 */
class DashboardItemUserActivity extends rex_dashboard_item_chart_line
{
    public function getTitle(): string
    {
        return rex_i18n::msg('dashboard_user_activity_title', 'Benutzer-Aktivität (7 Tage)');
    }

    public function isAvailable(): bool
    {
        // Nur für Admins verfügbar
        $user = rex::getUser();
        return $user && $user->isAdmin();
    }

    public function getChartData()
    {
        $sql = rex_sql::factory();
        
        // Erstelle Datum-Array für die letzten 7 Tage
        $dates = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $dateLabel = date('d.m.', strtotime("-{$i} days"));
            $dates[$date] = $dateLabel;
        }
        
        // Artikel-Updates pro Tag (echte Aktivität)
        $query = '
            SELECT 
                DATE(FROM_UNIXTIME(updatedate)) as date,
                COUNT(*) as updates,
                COUNT(DISTINCT updateuser) as active_users
            FROM ' . rex::getTable('article') . '
            WHERE updatedate >= ' . strtotime('-7 days') . '
              AND updatedate > 0
              AND updateuser != ""
            GROUP BY DATE(FROM_UNIXTIME(updatedate))
            ORDER BY date ASC
        ';
        
        $updateData = $sql->getArray($query);
        $articleUpdates = [];
        $activeUsers = [];
        
        // Initialisiere alle Tage mit 0
        foreach ($dates as $date => $label) {
            $articleUpdates[$label] = 0;
            $activeUsers[$label] = 0;
        }
        
        // Fülle tatsächliche Update-Daten
        foreach ($updateData as $row) {
            if ($row['date']) {
                $dateLabel = date('d.m.', strtotime($row['date']));
                if (isset($articleUpdates[$dateLabel])) {
                    $articleUpdates[$dateLabel] = (int)$row['updates'];
                    $activeUsers[$dateLabel] = (int)$row['active_users'];
                }
            }
        }
        
        // Neue Artikel pro Tag
        $query = '
            SELECT 
                DATE(FROM_UNIXTIME(createdate)) as date,
                COUNT(*) as creates
            FROM ' . rex::getTable('article') . '
            WHERE createdate >= ' . strtotime('-7 days') . '
              AND createdate > 0
              AND createuser != ""
            GROUP BY DATE(FROM_UNIXTIME(createdate))
            ORDER BY date ASC
        ';
        
        $createData = $sql->getArray($query);
        $articleCreates = [];
        
        // Initialisiere alle Tage mit 0
        foreach ($dates as $date => $label) {
            $articleCreates[$label] = 0;
        }
        
        // Fülle tatsächliche Create-Daten
        foreach ($createData as $row) {
            if ($row['date']) {
                $dateLabel = date('d.m.', strtotime($row['date']));
                if (isset($articleCreates[$dateLabel])) {
                    $articleCreates[$dateLabel] = (int)$row['creates'];
                }
            }
        }
        
        // Backend-Logins (falls rex_user_log Tabelle existiert)
        $loginData = [];
        if (rex_sql_table::get(rex::getTable('user_log'))->exists()) {
            $query = '
                SELECT 
                    DATE(FROM_UNIXTIME(timestamp)) as date,
                    COUNT(*) as logins
                FROM ' . rex::getTable('user_log') . '
                WHERE timestamp >= ' . strtotime('-7 days') . '
                  AND action = "login"
                GROUP BY DATE(FROM_UNIXTIME(timestamp))
                ORDER BY date ASC
            ';
            
            $loginResult = $sql->getArray($query);
            
            // Initialisiere alle Tage mit 0
            foreach ($dates as $date => $label) {
                $loginData[$label] = 0;
            }
            
            // Fülle Login-Daten
            foreach ($loginResult as $row) {
                if ($row['date']) {
                    $dateLabel = date('d.m.', strtotime($row['date']));
                    if (isset($loginData[$dateLabel])) {
                        $loginData[$dateLabel] = (int)$row['logins'];
                    }
                }
            }
        }
        
        $chartData = [
            rex_i18n::msg('dashboard_articles_edited', 'Artikel bearbeitet') => $articleUpdates,
            rex_i18n::msg('dashboard_articles_created', 'Artikel erstellt') => $articleCreates,
            rex_i18n::msg('dashboard_active_users', 'Aktive Benutzer') => $activeUsers
        ];
        
        // Backend-Logins hinzufügen falls verfügbar
        if (!empty($loginData) && array_sum($loginData) > 0) {
            $chartData[rex_i18n::msg('dashboard_backend_logins', 'Backend-Logins')] = $loginData;
        }
        
        return $chartData;
    }
    
    protected function __construct($id, $name)
    {
        parent::__construct($id, $name);
        
        $this->setOptions([
            'plugins' => [
                'tooltip' => [
                    'callbacks' => [
                        'label' => 'function(context) { return context.dataset.label + ": " + context.parsed.y; }'
                    ]
                ]
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'stepSize' => 1
                    ]
                ]
            ]
        ]);
    }
}
