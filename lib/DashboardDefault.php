<?php

namespace FriendsOfRedaxo\Dashboard;

use FriendsOfRedaxo\Dashboard\Items\AddonStatistics;
use FriendsOfRedaxo\Dashboard\Items\AddonUpdates;
use FriendsOfRedaxo\Dashboard\Items\ArticleStatus;
use FriendsOfRedaxo\Dashboard\Items\BackupStatus;
use FriendsOfRedaxo\Dashboard\Items\BigNumberDemo;
use FriendsOfRedaxo\Dashboard\Items\Clock;
use FriendsOfRedaxo\Dashboard\Items\CountdownDemo;
use FriendsOfRedaxo\Dashboard\Items\MediaStorage;
use FriendsOfRedaxo\Dashboard\Items\ModifiedArticles;
use FriendsOfRedaxo\Dashboard\Items\NewArticles;
use FriendsOfRedaxo\Dashboard\Items\RecentArticles;
use FriendsOfRedaxo\Dashboard\Items\RssClean;
use FriendsOfRedaxo\Dashboard\Items\SystemStatus;
use FriendsOfRedaxo\Dashboard\Items\UserActivity;
use rex;
use rex_addon;
use rex_i18n;

/**
 * Verwaltet die Standard-Dashboard-Items für REDAXO.
 */
class DashboardDefault
{
    /**
     * Registriert alle Default-Dashboard-Items.
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
            Dashboard::addItem(
                RecentArticles::factory('dashboard-default-recent-articles', rex_i18n::msg('dashboard_recent_articles_title', 'Zuletzt aktualisierte Artikel'))
                    ->setColumns($addon->getConfig('default_recent_articles_columns', 2)),
            );
        }

        // Geänderte Artikel (Arbeitsversion)
        if ($addon->getConfig('default_modified_articles', true)) {
            Dashboard::addItem(
                ModifiedArticles::factory('dashboard-default-modified-articles', rex_i18n::msg('dashboard_modified_articles_title', 'Geänderte Artikel (Arbeitsversion)'))
                    ->setColumns($addon->getConfig('default_modified_articles_columns', 2)),
            );
        }

        // Neue Artikel
        if ($addon->getConfig('default_new_articles', true)) {
            Dashboard::addItem(
                NewArticles::factory('dashboard-default-new-articles', rex_i18n::msg('dashboard_new_articles_title', 'Neue Artikel (30 Tage)'))
                    ->setColumns($addon->getConfig('default_new_articles_columns', 2)),
            );
        }

        // Medien-Speicherverbrauch (Chart)
        if ($addon->getConfig('default_media_storage', true)) {
            Dashboard::addItem(
                MediaStorage::factory('dashboard-default-media-storage', rex_i18n::msg('dashboard_media_storage_title', 'Medien-Speicherverbrauch'))
                    ->setColumns($addon->getConfig('default_media_storage_columns', 1)),
            );
        }

        // Artikel-Status Übersicht (Chart)
        if ($addon->getConfig('default_article_status', true)) {
            Dashboard::addItem(
                ArticleStatus::factory('dashboard-default-article-status', rex_i18n::msg('dashboard_article_status_title', 'Artikel-Status'))
                    ->setColumns($addon->getConfig('default_article_status_columns', 1)),
            );
        }

        // System-Status (nur für Admins)
        if ($addon->getConfig('default_system_status', true) && rex::getUser() && rex::getUser()->isAdmin()) {
            Dashboard::addItem(
                SystemStatus::factory('dashboard-default-system-status', rex_i18n::msg('dashboard_system_status_title', 'System-Status'))
                    ->setColumns($addon->getConfig('default_system_status_columns', 2)),
            );
        }

        // Backup-Status (nur für Admins)
        if ($addon->getConfig('default_backup_status', true) && rex::getUser() && rex::getUser()->isAdmin()) {
            Dashboard::addItem(
                BackupStatus::factory('dashboard-default-backup-status', rex_i18n::msg('dashboard_backup_status_title', 'Backup-Status'))
                    ->setColumns($addon->getConfig('default_backup_status_columns', 1)),
            );
        }

        // Uhr Widget
        if ($addon->getConfig('default_clock', true)) {
            Dashboard::addItem(
                Clock::factory('dashboard-default-clock', rex_i18n::msg('dashboard_clock_title', 'Uhr'))
                    ->setColumns($addon->getConfig('default_clock_columns', 1)),
            );
        }

        // AddOn Updates (nur für Admins)
        if ($addon->getConfig('default_addon_updates', true) && rex::getUser() && rex::getUser()->isAdmin()) {
            Dashboard::addItem(
                AddonUpdates::factory('dashboard-default-addon-updates', rex_i18n::msg('dashboard_addon_updates_title', 'AddOn Updates & Übersicht'))
                    ->setColumns($addon->getConfig('default_addon_updates_columns', 2)),
            );
        }

        // AddOn Statistiken (nur für Admins)
        if ($addon->getConfig('default_addon_statistics', true) && rex::getUser() && rex::getUser()->isAdmin()) {
            Dashboard::addItem(
                AddonStatistics::factory('dashboard-default-addon-statistics', rex_i18n::msg('dashboard_addon_statistics_title', 'AddOn Statistiken'))
                    ->setColumns($addon->getConfig('default_addon_statistics_columns', 1)),
            );
        }

        // Benutzer-Aktivität (Chart) (nur für Admins)
        if ($addon->getConfig('default_user_activity', true) && rex::getUser() && rex::getUser()->isAdmin()) {
            Dashboard::addItem(
                UserActivity::factory('dashboard-default-user-activity', rex_i18n::msg('dashboard_user_activity_title', 'Benutzer-Aktivität (Chart)'))
                    ->setColumns($addon->getConfig('default_user_activity_columns', 1)),
            );
        }

        // RSS-Feed Widget (Clean Version ohne Bootstrap Table)
        Dashboard::addItem(
            RssClean::factory('dashboard-default-rss-feed', rex_i18n::msg('dashboard_rss_feed_title', 'RSS-Feed'))
                ->setColumns($addon->getConfig('default_rss_feed_columns', 2)),
        );

        // Demo Countdown Widget (nur wenn Demo-Items erlaubt)
        if ($addon->getConfig('demo_items_enabled', false) && $addon->getConfig('default_countdown_demo', true)) {
            Dashboard::addItem(
                CountdownDemo::factory('dashboard-default-countdown-demo', rex_i18n::msg('dashboard_countdown_demo_title', 'Countdown Neujahr'))
                    ->setColumns($addon->getConfig('default_countdown_demo_columns', 1)),
            );
        }

        // Big Number Demo Widget (nur wenn Demo-Items erlaubt)
        if ($addon->getConfig('demo_items_enabled', false) && $addon->getConfig('default_big_number_demo', true)) {
            Dashboard::addItem(
                BigNumberDemo::factory('dashboard-default-big-number-demo', rex_i18n::msg('dashboard_big_number_demo_title', 'Follower Count'))
                    ->setColumns($addon->getConfig('default_big_number_demo_columns', 1)),
            );
        }
    }

    /**
     * Initialisiert die Default-Konfiguration.
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
