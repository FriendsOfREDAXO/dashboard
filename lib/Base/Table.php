<?php

namespace FriendsOfRedaxo\Dashboard\Base;

use rex_addon;
use rex_string;

abstract class Table extends Item
{
    protected $tableAttributes = [
        'class' => 'bootstrap-table',
        'data-toggle' => 'table',
        'data-pagination' => 'true',
        'data-page-size' => '10',
    ];

    protected $header = [];
    protected $data = [];

    protected function __construct($id, $name)
    {
        $this->setTableAttribute('data-locale', 'de-DE');
        static::addJs(rex_addon::get('dashboard')->getAssetsUrl('js/table.min.js'), 'table.js');
        static::addJs(rex_addon::get('dashboard')->getAssetsUrl('js/table.locale.min.js'), 'table.locale.js');
        parent::__construct($id, $name);
    }

    abstract protected function getTableData();

    public function setTableAttribute($key, $value)
    {
        $this->tableAttributes[$key] = $value;
        return $this;
    }

    protected function getData()
    {
        $tableData = $this->getTableData();

        $header = '';
        foreach ($tableData['header'] as $item) {
            $header .= '<th>' . htmlspecialchars($item) . '</th>';
        }

        $body = '';
        foreach ($tableData['data'] as $row) {
            $body .= '<tr>';
            foreach ($row as $item) {
                $body .= '<td>' . htmlspecialchars($item) . '</td>';
            }
            $body .= '</tr>';
        }

        return '<table' . rex_string::buildAttributes($this->tableAttributes) . '>
    <thead><tr>' . $header . '</tr></thead>
    <tbody>' . $body . '</tbody>
</table>';
    }
}
