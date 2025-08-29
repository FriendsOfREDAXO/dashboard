<?php

namespace FriendsOfREDAXO\Dashboard;

use rex_dashboard_item_chart_bar;

class DashboardItemDemoChartBarHorizontal extends rex_dashboard_item_chart_bar
{
    protected function __construct($id, $name)
    {
        parent::__construct($id, $name);
        $this->setHorizontal();
    }

    public function getChartData()
    {
        return [
            'Rot' => rand(1,122),
            'Blau' => rand(1,122),
            'Gelb' => rand(1,122),
            'Grün' => rand(1,122),
            'Lila' => rand(1,122),
            'Orange' => rand(1,122),
        ];
    }
}
