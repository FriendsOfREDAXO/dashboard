<?php

namespace FriendsOfRedaxo\Dashboard\DemoItems;

use FriendsOfRedaxo\Dashboard\Base\ChartBar;

class ChartBarHorizontal extends ChartBar
{
    protected function __construct($id, $name)
    {
        parent::__construct($id, $name);
        $this->setHorizontal();
    }

    public function getChartData()
    {
        return [
            'Rot' => random_int(1, 122),
            'Blau' => random_int(1, 122),
            'Gelb' => random_int(1, 122),
            'Grün' => random_int(1, 122),
            'Lila' => random_int(1, 122),
            'Orange' => random_int(1, 122),
        ];
    }
}
