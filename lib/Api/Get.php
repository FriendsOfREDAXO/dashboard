<?php

namespace FriendsOfRedaxo\Dashboard\Api;

use FriendsOfRedaxo\Dashboard\Dashboard;
use rex;
use rex_api_function;
use rex_i18n;

class Get extends rex_api_function
{
    protected $published = false;

    public function execute()
    {
        if (!($user = rex::getUser())) {
            return '[]';
        }

        $ids = rex_request('ids', 'array', []);
        $result = [];
        foreach (Dashboard::getItems($ids) as $item) {
            $result[$item->getId()] = [
                'content' => $item->getContent(true),
                'date' => $item->getCacheDate()->format(rex_i18n::msg('dashboard_action_refresh_title_dateformat')),
            ];
        }

        echo json_encode($result);
        exit;
        // return new rex_api_result(true, json_encode($result));
    }
}
