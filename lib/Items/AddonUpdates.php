<?php

namespace FriendsOfRedaxo\Dashboard\Items;

use Exception;
use FriendsOfRedaxo\Dashboard\Base\Item;
use rex;
use rex_addon;
use rex_i18n;
use rex_install_packages;

use function array_slice;
use function count;

/**
 * Dashboard Item: AddOn Updates (nur für Admins).
 */
class AddonUpdates extends Item
{
    public function getTitle(): string
    {
        return rex_i18n::msg('dashboard_addon_updates_title', 'AddOn Updates & Übersicht');
    }

    public function getData()
    {
        // Nur für Admins verfügbar
        $user = rex::getUser();
        if (!$user || !$user->isAdmin()) {
            return '<p class="text-muted">Nur für Administratoren verfügbar.</p>';
        }

        $content = '';

        // Prüfe ob Install-AddOn verfügbar ist
        $installAddon = rex_addon::get('install');
        if (!$installAddon->isAvailable()) {
            $content .= '<div class="alert alert-warning">';
            $content .= '<i class="fa fa-exclamation-triangle"></i> Install-AddOn nicht verfügbar';
            $content .= '</div>';
            return $content;
        }

        $availableUpdates = [];
        $newestAddons = [];

        try {
            // Verfügbare Updates abrufen
            $updatePackages = rex_install_packages::getUpdatePackages();
            foreach ($updatePackages as $key => $package) {
                if (isset($package['files']) && !empty($package['files'])) {
                    $latestVersion = reset($package['files'])['version'];
                    $availableUpdates[] = [
                        'name' => $package['name'],
                        'key' => $key,
                        'current_version' => rex_addon::get($key)->getVersion(),
                        'latest_version' => $latestVersion,
                    ];
                }
            }

            // Alle verfügbaren AddOns für "Neueste" Liste (nur 3 Items)
            $allPackages = rex_install_packages::getAddPackages();

            // Sortiere nach Datum der letzten Aktualisierung
            uasort($allPackages, static function ($a, $b) {
                return strtotime($b['updated']) - strtotime($a['updated']);
            });

            // Nehme die ersten 3
            $count = 0;
            foreach ($allPackages as $key => $package) {
                if ($count >= 3) {
                    break;
                }

                $newestAddons[] = [
                    'name' => $package['name'],
                    'author' => $package['author'] ?? '',
                    'updated' => $package['updated'],
                    'installed' => rex_addon::exists($key),
                ];
                ++$count;
            }
        } catch (Exception $e) {
            $content .= '<div class="alert alert-warning">';
            $content .= '<i class="fa fa-exclamation-triangle"></i> Fehler beim Abrufen der Daten';
            $content .= '</div>';
            return $content;
        }

        // Update-Status kompakt anzeigen
        if (!empty($availableUpdates)) {
            $content .= '<div class="alert alert-warning" style="padding: 10px; margin-bottom: 15px;">';
            $content .= '<strong><i class="fa fa-exclamation-triangle"></i> ' . count($availableUpdates) . ' Updates verfügbar</strong>';

            foreach (array_slice($availableUpdates, 0, 2) as $addon) {
                $content .= '<br><small>' . rex_escape($addon['name']) . ' (' . rex_escape($addon['current_version']) . ' → ' . rex_escape($addon['latest_version']) . ')</small>';
            }

            if (count($availableUpdates) > 2) {
                $content .= '<br><small>... und ' . (count($availableUpdates) - 2) . ' weitere</small>';
            }

            $content .= '</div>';
        } else {
            $content .= '<div class="alert alert-success" style="padding: 10px; margin-bottom: 15px;">';
            $content .= '<i class="fa fa-check-circle"></i> <strong>Alle AddOns aktuell</strong>';
            $content .= '</div>';
        }

        // Neueste AddOns kompakt
        if (!empty($newestAddons)) {
            $content .= '<h6 style="margin-bottom: 10px;">Neueste AddOns</h6>';

            foreach ($newestAddons as $addon) {
                $content .= '<div style="padding: 8px 0; border-bottom: 1px solid #eee;">';
                $content .= '<div style="display: flex; justify-content: space-between; align-items: center;">';
                $content .= '<div>';
                $content .= '<strong style="font-size: 13px;">' . rex_escape($addon['name']) . '</strong>';
                if ($addon['installed']) {
                    $content .= ' <span class="label label-success" style="font-size: 10px;">Installiert</span>';
                }
                $content .= '<br><small style="color: #666;">von ' . rex_escape($addon['author']) . '</small>';
                $content .= '</div>';
                $content .= '<small style="color: #999;">' . rex_escape(date('d.m.Y', strtotime($addon['updated']))) . '</small>';
                $content .= '</div>';
                $content .= '</div>';
            }
        }

        return $content;
    }
}
