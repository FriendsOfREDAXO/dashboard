<?php

namespace FriendsOfRedaxo\Dashboard\Items;

use FriendsOfRedaxo\Dashboard\Base\ChartLine;
use rex;
use rex_i18n;
use rex_sql;

/**
 * Dashboard Item: Benutzer-Aktivität (Artikel-Bearbeitungen der letzten 7 Tage).
 */
class UserActivity extends ChartLine
{
    protected function __construct($id, $name)
    {
        parent::__construct($id, $name);

        $this->setOptions([
            'plugins' => [
                'tooltip' => [
                    'callbacks' => [
                        'label' => 'function(context) { return context.dataset.label + ": " + context.parsed.y + " ' . rex_i18n::msg('dashboard_articles', 'Artikel') . '"; }',
                    ],
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'stepSize' => 1,
                    ],
                ],
            ],
        ]);
    }

    public function getTitle(): string
    {
        return rex_i18n::msg('dashboard_user_activity_title', 'Benutzer-Aktivität (7 Tage)');
    }

    public function getChartData()
    {
        $sql = rex_sql::factory();

        // Erstelle Datum-Array für die letzten 7 Tage
        $chartData = [];
        $dates = [];

        for ($i = 6; $i >= 0; --$i) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $dateLabel = date('d.m.', strtotime("-{$i} days"));
            $dates[$date] = $dateLabel;
        }

        // Artikel-Updates pro Tag
        $query = '
            SELECT 
                DATE(FROM_UNIXTIME(updatedate)) as date,
                COUNT(*) as updates
            FROM ' . rex::getTable('article') . '
            WHERE updatedate >= ' . strtotime('-7 days') . '
            GROUP BY DATE(FROM_UNIXTIME(updatedate))
            ORDER BY date ASC
        ';

        $updateData = $sql->getArray($query);
        $updates = [];

        // Initialisiere alle Tage mit 0
        foreach ($dates as $date => $label) {
            $updates[$label] = 0;
        }

        // Fülle tatsächliche Daten
        foreach ($updateData as $row) {
            if ($row['date']) {  // Prüfe auf nicht-null Datum
                $dateLabel = date('d.m.', strtotime($row['date']));
                if (isset($updates[$dateLabel])) {
                    $updates[$dateLabel] = (int) $row['updates'];
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
            GROUP BY DATE(FROM_UNIXTIME(createdate))
            ORDER BY date ASC
        ';

        $createData = $sql->getArray($query);
        $creates = [];

        // Initialisiere alle Tage mit 0
        foreach ($dates as $date => $label) {
            $creates[$label] = 0;
        }

        // Fülle tatsächliche Daten
        foreach ($createData as $row) {
            if ($row['date']) {  // Prüfe auf nicht-null Datum
                $dateLabel = date('d.m.', strtotime($row['date']));
                if (isset($creates[$dateLabel])) {
                    $creates[$dateLabel] = (int) $row['creates'];
                }
            }
        }

        return [
            rex_i18n::msg('dashboard_articles_edited', 'Artikel bearbeitet') => $updates,
            rex_i18n::msg('dashboard_articles_created', 'Artikel erstellt') => $creates,
        ];
    }
}
