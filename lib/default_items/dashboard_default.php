<?php

namespace FriendsOfREDAXO\Dashboard;

use rex_addon;
use rex_config;
use rex;

/**
 * Verwaltet die Standard-Dashboard-Items für REDAXO
 */
class DashboardDefault
{
    /**
     * Registriert alle Default-Dashboard-Items
     */
    public static function register()
    {
        $addon = rex_addon::get('dashboard');
        
        // TEMPORÄR: Lade immer die Default-Widgets für Debugging
        // Prüfe ob Default-Widgets aktiviert sind
        // if (!$addon->getConfig('default_widgets_enabled', false)) {
        //     return;
        // }
        
        // Zuletzt aktualisierte Artikel
        if ($addon->getConfig('default_recent_articles', true)) {
            \rex_dashboard::addItem(
                DashboardItemRecentArticles::factory('dashboard-default-recent-articles', 'Zuletzt aktualisierte Artikel')
            );
        }
        
        // Neue Artikel
        if ($addon->getConfig('default_new_articles', true)) {
            \rex_dashboard::addItem(
                DashboardItemNewArticles::factory('dashboard-default-new-articles', 'Neue Artikel (30 Tage)')
                    ->setColumns($addon->getConfig('default_new_articles_columns', 2))
            );
        }

        
        // Medien-Speicherverbrauch (Chart)
        if ($addon->getConfig('default_media_storage', true)) {
            \rex_dashboard::addItem(
                DashboardItemMediaStorage::factory('dashboard-default-media-storage', 'Medien-Speicherverbrauch')
                    ->setColumns($addon->getConfig('default_media_storage_columns', 1))
            );
        }
        
        // Artikel-Status Übersicht (Chart)
        if ($addon->getConfig('default_article_status', true)) {
            \rex_dashboard::addItem(
                DashboardItemArticleStatus::factory('dashboard-default-article-status', 'Artikel-Status')
                    ->setColumns($addon->getConfig('default_article_status_columns', 1))
            );
        }
        
        // System-Status
        if ($addon->getConfig('default_system_status', true)) {
            \rex_dashboard::addItem(
                DashboardItemSystemStatus::factory('dashboard-default-system-status', 'System-Status')
                    ->setColumns($addon->getConfig('default_system_status_columns', 2))
            );
        }
        
        // Benutzer-Aktivität (Chart)
        if ($addon->getConfig('default_user_activity', true)) {
            \rex_dashboard::addItem(
                DashboardItemUserActivity::factory('dashboard-default-user-activity', 'Benutzer-Aktivität')
                    ->setColumns($addon->getConfig('default_user_activity_columns', 2))
            );
        }
        
        // AddOn Updates (nur für Admins)
        if ($addon->getConfig('default_addon_updates', true) && rex::getUser() && rex::getUser()->isAdmin()) {
            \rex_dashboard::addItem(
                DashboardItemAddonUpdates::factory('dashboard-default-addon-updates', 'AddOn Verwaltung')
                    ->setColumns($addon->getConfig('default_addon_updates_columns', 2))
            );
        }
        
        // AddOn Statistiken (nur für Admins)
        if ($addon->getConfig('default_addon_statistics', true) && rex::getUser() && rex::getUser()->isAdmin()) {
            \rex_dashboard::addItem(
                DashboardItemAddonStatistics::factory('dashboard-default-addon-statistics', 'AddOn Statistiken')
                    ->setColumns($addon->getConfig('default_addon_statistics_columns', 1))
            );
        }
    }
    
    /**
     * Initialisiert die Default-Konfiguration
     */
    public static function initDefaults()
    {
        $addon = rex_addon::get('dashboard');
        
        // Setze Default-Werte für alle Widgets
        $defaults = [
            'default_widgets_enabled' => false,  // Muss explizit aktiviert werden
            'default_recent_articles' => true,
            'default_recent_articles_columns' => 2, // breit
            'default_new_articles' => true,
            'default_new_articles_columns' => 2,    // breit
            'default_media_storage' => true,
            'default_media_storage_columns' => 1,   // klein
            'default_article_status' => true,
            'default_article_status_columns' => 1,  // klein
            'default_system_status' => true,
            'default_system_status_columns' => 2,   // breit
            'default_user_activity' => true,
            'default_user_activity_columns' => 2,   // breit
            'default_addon_updates' => true,
            'default_addon_updates_columns' => 2,   // breit
            'default_addon_statistics' => true,
            'default_addon_statistics_columns' => 1, // klein
        ];
        
        foreach ($defaults as $key => $defaultValue) {
            if (null === $addon->getConfig($key)) {
                $addon->setConfig($key, $defaultValue);
            }
        }
    }
}
