<?php

namespace FriendsOfREDAXO\Dashboard;

use rex_dashboard_item_table;
use rex_sql;

class DashboardItemDemoTable extends rex_dashboard_item_table
{
    protected $header = [];
    protected $data = [];

    protected function getTableData()
    {
        $tableData = rex_sql::factory()->setQuery('
            SELECT  id ID
                    , label Label
                    , dbtype `DB-Type`
            FROM rex_metainfo_type
            ORDER BY id ASC
        ')->getArray();

        if (!empty($tableData)) {
            $this->data = $tableData;
            $this->header = array_keys($tableData[0]);
        }

        return [
            'data' => $this->data,
            'header' => $this->header,
        ];
    }
}
