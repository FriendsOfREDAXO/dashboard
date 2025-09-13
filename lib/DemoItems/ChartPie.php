<?php

namespace FriendsOfRedaxo\Dashboard\DemoItems;

use FriendsOfRedaxo\Dashboard\Base\ChartPie as BaseChartPie;

class ChartPie extends BaseChartPie
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
