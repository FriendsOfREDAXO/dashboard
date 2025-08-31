<?php

use FriendsOfREDAXO\Dashboard\DashboardDemo;
use FriendsOfREDAXO\Dashboard\DashboardDefault;
use FriendsOfREDAXO\Dashboard\DashboardNotes;

$addon = rex_addon::get('dashboard');

if (rex::isBackend()) {
    // register dashboard items
    rex_extension::register(
        'PACKAGES_INCLUDED',
        static function () {
            rex_dashboard::init();
            
            // Init default widgets
            DashboardDefault::initDefaults();
            DashboardDefault::register();
            
            // Init demo items if enabled
            DashboardDemo::init();
        }, rex_extension::LATE
    );

    rex_perm::register('dashboard[move-items]', null, rex_perm::EXTRAS);

    if ('dashboard' == rex_be_controller::getCurrentPagePart(1)) {
        rex_view::addCssFile($addon->getAssetsUrl('css/style.css'));
        rex_view::addCssFile($addon->getAssetsUrl('css/dashboard2-style.css'));
        
        // Bootstrap Table JS (für Tabellen-Widgets)
        rex_view::addJsFile($addon->getAssetsUrl('js/table.min.js'));
        rex_view::addJsFile($addon->getAssetsUrl('js/table.locale.min.js'));
        
        // GridStack and original Dashboard JS (contains GridStack)
        rex_view::addJsFile($addon->getAssetsUrl('js/script.js'));
        
        // Enhanced Dashboard JS with live refresh indicators
        rex_view::addJsFile($addon->getAssetsUrl('js/dashboard-enhanced.js'));
        rex_view::addJsFile($addon->getAssetsUrl('js/chart.min.js'));
    }
}
