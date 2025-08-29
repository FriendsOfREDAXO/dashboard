<?php

namespace FriendsOfREDAXO\Dashboard;

use rex_dashboard_item_chart_pie;

class DashboardItemDemoChartPie extends rex_dashboard_item_chart_pie
{
    public function getChartData()
    {
        return [
            'Rot' => 12,
            'Blau' => 19,
            'Gelb' => 3,
            'Grün' => 5,
            'Lila' => 2,
            'Orange' => 3,
        ];
    }
}
