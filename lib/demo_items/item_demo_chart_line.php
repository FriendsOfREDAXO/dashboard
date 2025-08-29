<?php

namespace FriendsOfREDAXO\Dashboard;

use rex_dashboard_item_chart_line;

class DashboardItemDemoChartLine extends rex_dashboard_item_chart_line
{
    public function getChartData()
    {
        return [
            'Linie 1' => [
                'Rot' => 12,
                'Blau' => 19,
                'Gelb' => 3,
                'Grün' => 5,
                'Lila' => 2,
                'Orange' => 3,
            ],
            'Linie 2' => [
                'Rot' => 3,
                'Blau' => 5,
                'Gelb' => 8,
                'Grün' => 10,
                'Lila' => 11,
                'Orange' => 11.5,
            ],
            'Linie 3' => [
                'Rot' => 5,
                'Blau' => 13,
                'Gelb' => 16,
                'Grün' => 12,
                'Lila' => 7,
                'Orange' => 2,
            ]
        ];
    }
}
