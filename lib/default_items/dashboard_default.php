<?php

namespace FriendsOfREDAXO\Dashboard;

use rex_addon;
use rex_config;
use rex;
use rex_i18n;

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
        
        // Prüfe ob Default-Widgets aktiviert sind
        if (!$addon->getConfig('default_widgets_enabled', false)) {
            return;
        }
        
        // Zuletzt aktualisierte Artikel
        if ($addon->getConfig('default_recent_articles', true)) {
            \rex_dashboard::addItem(
                DashboardItemRecentArticles::factory('dashboard-default-recent-articles', rex_i18n::msg('dashboard_recent_articles_title', 'Zuletzt aktualisierte Artikel'))
                    ->setColumns($addon->getConfig('default_recent_articles_columns', 2))
            );
        }
        
        // Neue Artikel
        if ($addon->getConfig('default_new_articles', true)) {
            \rex_dashboard::addItem(
                DashboardItemNewArticles::factory('dashboard-default-new-articles', rex_i18n::msg('dashboard_new_articles_title', 'Neue Artikel (30 Tage)'))
                    ->setColumns($addon->getConfig('default_new_articles_columns', 2))
            );
        }

        
        // Medien-Speicherverbrauch (Chart)
        if ($addon->getConfig('default_media_storage', true)) {
            \rex_dashboard::addItem(
                DashboardItemMediaStorage::factory('dashboard-default-media-storage', rex_i18n::msg('dashboard_media_storage_title', 'Medien-Speicherverbrauch'))
                    ->setColumns($addon->getConfig('default_media_storage_columns', 1))
            );
        }
        
        // Artikel-Status Übersicht (Chart)
        if ($addon->getConfig('default_article_status', true)) {
            \rex_dashboard::addItem(
                DashboardItemArticleStatus::factory('dashboard-default-article-status', rex_i18n::msg('dashboard_article_status_title', 'Artikel-Status'))
                    ->setColumns($addon->getConfig('default_article_status_columns', 1))
            );
        }
        
        // System-Status (nur für Admins)
        if ($addon->getConfig('default_system_status', true) && rex::getUser() && rex::getUser()->isAdmin()) {
            \rex_dashboard::addItem(
                DashboardItemSystemStatus::factory('dashboard-default-system-status', rex_i18n::msg('dashboard_system_status_title', 'System-Status'))
                    ->setColumns($addon->getConfig('default_system_status_columns', 2))
            );
        }
        
        // Backup-Status (nur für Admins)
        if ($addon->getConfig('default_backup_status', true) && rex::getUser() && rex::getUser()->isAdmin()) {
            \rex_dashboard::addItem(
                DashboardItemBackupStatus::factory('dashboard-default-backup-status', rex_i18n::msg('dashboard_backup_status_title', 'Backup-Status'))
                    ->setColumns($addon->getConfig('default_backup_status_columns', 1))
            );
        }
        
        // Clock Widget Defaults
        if (null === $addon->getConfig('default_clock')) {
            $addon->setConfig('default_clock', false);
        }
        if (null === $addon->getConfig('default_clock_columns')) {
            $addon->setConfig('default_clock_columns', 1);
        }
        
        // System-Log Widget Defaults
        if (null === $addon->getConfig('default_system_log')) {
            $addon->setConfig('default_system_log', false);
        }
        if (null === $addon->getConfig('default_system_log_columns')) {
            $addon->setConfig('default_system_log_columns', 2);
        }
        
        // System-Log (nur für Admins)
        if ($addon->getConfig('default_system_log', true) && rex::getUser() && rex::getUser()->isAdmin()) {
            \rex_dashboard::addItem(
                DashboardItemSystemLog::factory('dashboard-default-system-log', rex_i18n::msg('dashboard_system_log_title', 'System-Log'))
                    ->setColumns($addon->getConfig('default_system_log_columns', 2))
            );
        }
        
        // AddOn Updates (nur für Admins)
        if ($addon->getConfig('default_addon_updates', true) && rex::getUser() && rex::getUser()->isAdmin()) {
            \rex_dashboard::addItem(
                DashboardItemAddonUpdates::factory('dashboard-default-addon-updates', rex_i18n::msg('dashboard_addon_updates_title', 'AddOn Updates & Übersicht'))
                    ->setColumns($addon->getConfig('default_addon_updates_columns', 2))
            );
        }
        
        // AddOn Statistiken (nur für Admins)
        if ($addon->getConfig('default_addon_statistics', true) && rex::getUser() && rex::getUser()->isAdmin()) {
            \rex_dashboard::addItem(
                DashboardItemAddonStatistics::factory('dashboard-default-addon-statistics', rex_i18n::msg('dashboard_addon_statistics_title', 'AddOn Statistiken'))
                    ->setColumns($addon->getConfig('default_addon_statistics_columns', 1))
            );
        }
        
        // Benutzer-Aktivität (Chart) (nur für Admins)
        if ($addon->getConfig('default_user_activity', true) && rex::getUser() && rex::getUser()->isAdmin()) {
            \rex_dashboard::addItem(
                DashboardItemUserActivity::factory('dashboard-default-user-activity', rex_i18n::msg('dashboard_user_activity_title', 'Benutzer-Aktivität (Chart)'))
                    ->setColumns($addon->getConfig('default_user_activity_columns', 1))
            );
        }
        
        // Schnellnotizen (für alle Benutzer)
        if ($addon->getConfig('default_quick_notes', true)) {
            \rex_dashboard::addItem(
                DashboardItemQuickNotes::factory('dashboard-default-quick-notes', rex_i18n::msg('dashboard_quick_notes_title', 'Schnellnotizen'))
                    ->setColumns($addon->getConfig('default_quick_notes_columns', 1))
            );
        }
        
        // RSS-Feed Widget (Clean Version ohne Bootstrap Table)
        \rex_dashboard::addItem(
            DashboardItemRssClean::factory('dashboard-default-rss-feed', rex_i18n::msg('dashboard_rss_feed_title', 'RSS-Feed'))
                ->setColumns($addon->getConfig('default_rss_feed_columns', 2))
        );
        
        // Demo Countdown Widget (nur wenn Demo-Items erlaubt)
        if ($addon->getConfig('demo_items_enabled', false) && $addon->getConfig('default_countdown_demo', true)) {
            \rex_dashboard::addItem(
                DashboardItemCountdownDemo::factory('dashboard-default-countdown-demo', rex_i18n::msg('dashboard_countdown_demo_title', 'Countdown Neujahr'))
                    ->setColumns($addon->getConfig('default_countdown_demo_columns', 1))
            );
        }
        
        // Big Number Demo Widget (nur wenn Demo-Items erlaubt)  
        if ($addon->getConfig('demo_items_enabled', false) && $addon->getConfig('default_big_number_demo', true)) {
            \rex_dashboard::addItem(
                DashboardItemBigNumberDemo::factory('dashboard-default-big-number-demo', rex_i18n::msg('dashboard_big_number_demo_title', 'Follower Count'))
                    ->setColumns($addon->getConfig('default_big_number_demo_columns', 1))
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
            // Debug-Modus temporär aktiviert  
            'debug' => false,
            'default_widgets_enabled' => false,  // Muss explizit aktiviert werden
            'demo_items_enabled' => false,       // Demo-Items müssen explizit aktiviert werden
            'default_recent_articles' => true,
            'default_recent_articles_columns' => 2, // normal breit (2 Spalten)
            'default_new_articles' => true,
            'default_new_articles_columns' => 2,    // normal breit (2 Spalten)
            'default_media_storage' => true,
            'default_media_storage_columns' => 1,   // klein
            'default_article_status' => true,
            'default_article_status_columns' => 1,  // klein
            'default_system_status' => true,
            'default_system_status_columns' => 2,   // breit
            'default_backup_status' => true,
            'default_backup_status_columns' => 1,   // klein
            'default_clock' => true,
            'default_clock_columns' => 1,           // klein
            'default_addon_updates' => true,
            'default_addon_updates_columns' => 2,   // breit
            'default_addon_statistics' => true,
            'default_addon_statistics_columns' => 1, // klein
            'default_user_activity' => true,
            'default_user_activity_columns' => 1,    // klein
            'default_quick_notes' => true,
            'default_quick_notes_columns' => 1,      // klein
            'default_rss_feed' => true,
            'default_rss_feed_columns' => 2,        // breit
            'default_countdown_demo' => true,
            'default_countdown_demo_columns' => 1,   // klein (1 Spalte)
            'default_big_number_demo' => true,
            'default_big_number_demo_columns' => 1,  // klein (1 Spalte)
            'rss_feed_url' => '',
            'rss_items_per_page' => 2,
            // RSS-Feeds Konfiguration - KEINE Standard-URLs setzen!
            'rss_feeds' => [
                // User soll eigene RSS-Feeds konfigurieren
            ],
        ];
        
        foreach ($defaults as $key => $defaultValue) {
            if (null === $addon->getConfig($key)) {
                $addon->setConfig($key, $defaultValue);
            }
        }
    }
}
