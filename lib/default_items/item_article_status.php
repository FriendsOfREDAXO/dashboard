<?php

namespace FriendsOfREDAXO\Dashboard;

use rex_dashboard_item_chart_bar;
use rex_sql;
use rex_i18n;
use rex;

/**
 * Dashboard Item: Artikel-Status Übersicht
 */
class DashboardItemArticleStatus extends rex_dashboard_item_chart_bar
{
    public function getTitle(): string
    {
        return rex_i18n::msg('dashboard_article_status_title', 'Artikel-Status Übersicht');
    }

    public function getChartData()
    {
        $sql = rex_sql::factory();
        
        // Artikel nach Status
        $query = '
            SELECT 
                status,
                COUNT(*) as count
            FROM ' . rex::getTable('article') . '
            GROUP BY status
        ';
        
        $data = $sql->getArray($query);
        $chartData = [];
        
        foreach ($data as $row) {
            $statusName = $row['status'] == 1 ? 'Online' : 'Offline';
            $chartData[$statusName] = (int)$row['count'];
        }
        
        // Zusätzliche Statistiken
        $totalArticles = array_sum($chartData);
        
        // Artikel nach Template (wenn verfügbar)
        $templateQuery = '
            SELECT 
                t.name as template_name,
                COUNT(a.id) as count
            FROM ' . rex::getTable('article') . ' a
            LEFT JOIN ' . rex::getTable('template') . ' t ON a.template_id = t.id
            WHERE a.startarticle = 0
            GROUP BY a.template_id, t.name
            ORDER BY count DESC
            LIMIT 5
        ';
        
        $templateData = $sql->getArray($templateQuery);
        
        // Hauptstatus-Daten zurückgeben
        return $chartData;
    }
    
    protected function __construct($id, $name)
    {
        parent::__construct($id, $name);
        
        // Vertikale Balken
        $this->setOptions([
            'plugins' => [
                'tooltip' => [
                    'callbacks' => [
                        'label' => 'function(context) { return context.label + ": " + context.parsed + " Artikel"; }'
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
