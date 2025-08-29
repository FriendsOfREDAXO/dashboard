<?php

namespace FriendsOfREDAXO\Dashboard;

use rex_dashboard_item;
use rex_addon;
use rex_i18n;
use rex;
use rex_escape;

/**
 * Dashboard Item: AddOn Statistiken (nur für Admins)
 */
class DashboardItemAddonStatistics extends rex_dashboard_item
{
    public function getTitle(): string
    {
        return rex_i18n::msg('dashboard_addon_statistics_title', 'AddOn Statistiken');
    }

    public function getData()
    {
        // Nur für Admins verfügbar
        $user = rex::getUser();
        if (!$user || !$user->isAdmin()) {
            return '<p class="text-muted">' . rex_i18n::msg('dashboard_admin_only', 'Nur für Administratoren verfügbar.') . '</p>';
        }
        
        $content = '';
        
        // AddOn-Statistiken sammeln
        $allAddons = rex_addon::getAvailableAddons();
        $totalAddons = count($allAddons);
        $activeAddons = 0;
        $coreAddons = 0;
        $availableUpdates = 0;
        
        foreach ($allAddons as $addon) {
            if ($addon->isAvailable()) {
                $activeAddons++;
            }
            // Zähle Core-AddOns
            $coreAddonsList = ['backup', 'be_style', 'cronjob', 'install', 'media_manager', 'mediapool', 'metainfo', 'phpmailer', 'project', 'structure', 'users'];
            if (in_array($addon->getPackageId(), $coreAddonsList)) {
                $coreAddons++;
            }
        }
        
        // Updates prüfen falls Install-AddOn verfügbar
        $installAddon = rex_addon::get('install');
        if ($installAddon->isAvailable()) {
            try {
                $updatePackages = \rex_install_packages::getUpdatePackages();
                $availableUpdates = count($updatePackages);
            } catch (\Exception $e) {
                // Ignoriere Fehler
            }
        }
        
        $content .= '<div class="row text-center">';
        $content .= '<div class="col-xs-6 col-sm-3">';
        $content .= '<h4 class="text-success">' . $activeAddons . '</h4>';
        $content .= '<small>' . rex_i18n::msg('dashboard_active_addons', 'Aktive AddOns') . '</small>';
        $content .= '</div>';
        $content .= '<div class="col-xs-6 col-sm-3">';
        $content .= '<h4 class="text-primary">' . $coreAddons . '</h4>';
        $content .= '<small>' . rex_i18n::msg('dashboard_core_addons', 'Core AddOns') . '</small>';
        $content .= '</div>';
        $content .= '<div class="col-xs-6 col-sm-3">';
        $content .= '<h4 class="text-warning">' . $availableUpdates . '</h4>';
        $content .= '<small>' . rex_i18n::msg('dashboard_updates', 'Updates') . '</small>';
        $content .= '</div>';
        $content .= '<div class="col-xs-6 col-sm-3">';
        $content .= '<h4 class="text-info">' . $totalAddons . '</h4>';
        $content .= '<small>' . rex_i18n::msg('dashboard_total', 'Gesamt') . '</small>';
        $content .= '</div>';
        $content .= '</div>';
        
        // Zusätzliche Details
        $inactiveAddons = $totalAddons - $activeAddons;
        if ($inactiveAddons > 0) {
            $content .= '<div style="margin-top: 20px; padding: 10px; background-color: #f8f9fa; border-radius: 4px;">';
            $content .= '<small class="text-muted">';
            $content .= '<i class="fa fa-info-circle"></i> ';
            $content .= $inactiveAddons . ' ' . rex_i18n::msg('dashboard_inactive_addons_info', 'AddOn(s) installiert aber nicht aktiviert');
            $content .= '</small>';
            $content .= '</div>';
        }
        
        return $content;
    }
}
