<?php

use FriendsOfRedaxo\Dashboard\Api\Get;
use FriendsOfRedaxo\Dashboard\Api\Store;
use FriendsOfRedaxo\Dashboard\Dashboard;
use FriendsOfRedaxo\Dashboard\DashboardDefault;
use FriendsOfRedaxo\Dashboard\DemoItems\DashboardDemo;

$addon = rex_addon::get('dashboard');

if (rex::isBackend()) {
    // register dashboard items
    rex_extension::register(
        'PACKAGES_INCLUDED',
        static function () {
            Dashboard::init();

            // Init default widgets
            DashboardDefault::initDefaults();
            DashboardDefault::register();

            // Init demo items if enabled
            DashboardDemo::init();
        }, rex_extension::LATE,
    );
    rex_api_function::register('dashboard_get', Get::class);
    rex_api_function::register('dashboard_store', Store::class);

    rex_perm::register('dashboard[move-items]', null, rex_perm::EXTRAS);

    if ('dashboard' == rex_be_controller::getCurrentPagePart(1)) {
        rex_view::addCssFile($addon->getAssetsUrl('css/style.css'));
        rex_view::addCssFile($addon->getAssetsUrl('css/dashboard2-style.css'));

        // Bootstrap Table JS (für Tabellen-Widgets)
        rex_view::addJsFile($addon->getAssetsUrl('js/table.min.js'));
        rex_view::addJsFile($addon->getAssetsUrl('js/table.locale.min.js'));

        rex_view::addJsFile($addon->getAssetsUrl('js/script.js'));
        rex_view::addJsFile($addon->getAssetsUrl('js/chart.min.js'));
    }
}
