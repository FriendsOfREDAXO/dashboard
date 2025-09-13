<?php

namespace FriendsOfRedaxo\Dashboard\Api;

use FriendsOfRedaxo\Dashboard\Base\Item;
use FriendsOfRedaxo\Dashboard\Dashboard;
use rex;
use rex_api_function;
use rex_api_result;
use rex_config;
use rex_i18n;

class Store extends rex_api_function
{
    protected $published = false;

    public function execute()
    {
        if (!($user = rex::getUser())) {
            return new rex_api_result(false, rex_i18n::msg('dashboard_api_store_failed_user'));
        }

        $data = rex_request('data', 'array', []);

        // validate
        $storeData = rex_config::get('dashboard', 'items_' . $user->getId(), []);
        foreach ($data as $id => $itemData) {
            if (!Dashboard::itemExists($id)) {
                continue;
            }

            foreach (Item::ATTRIBUTES as $attribute) {
                $storeData[$id][$attribute] = (int) ($itemData[$attribute] ?? 0);
            }
        }

        rex_config::set('dashboard', 'items_' . $user->getId(), $storeData);

        return new rex_api_result(true, rex_i18n::msg('dashboard_api_store_success'));
    }
}
