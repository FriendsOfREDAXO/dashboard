<?php

namespace FriendsOfRedaxo\Dashboard\Base;

abstract class ChartBar extends Chart
{
    protected $chartType = 'bar';

    public function setHorizontal()
    {
        $this->chartOptions['indexAxis'] = 'y';
        return $this;
    }

    public function setVertical()
    {
        unset($this->chartOptions['orientation']);
        return $this;
    }
}
