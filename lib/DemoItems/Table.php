<?php

namespace FriendsOfRedaxo\Dashboard\DemoItems;

use FriendsOfRedaxo\Dashboard\Base\Table as BaseTable;
use rex_sql;

class Table extends BaseTable
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
