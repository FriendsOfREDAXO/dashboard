<?php

namespace FriendsOfRedaxo\Dashboard\Base;

abstract class ChartPie extends Chart
{
    protected $chartType = 'pie';

    public function setDonut()
    {
        $this->chartType = 'doughnut';
        return $this;
    }
}
