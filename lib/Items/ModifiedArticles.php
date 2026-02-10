<?php

namespace FriendsOfRedaxo\Dashboard\Items;

use FriendsOfRedaxo\Dashboard\Base\Item;
use rex;
use rex_addon;
use rex_clang;
use rex_i18n;
use rex_plugin;
use rex_sql;
use rex_url;

/**
 * Dashboard Item: Geänderte Artikel (Arbeitsversion).
 */
class ModifiedArticles extends Item
{
    public function getTitle(): string
    {
        return rex_i18n::msg('dashboard_modified_articles_title', 'Geänderte Artikel (Arbeitsversion)');
    }

    private function getTimeAgo($timestamp)
    {
        $time = time() - $timestamp;

        if ($time < 60) {
            return rex_i18n::msg('dashboard_just_now');
        }

        $tokens = [
            31536000 => 'dashboard_days_ago', // Simplified to days for > year
            2592000 => 'dashboard_days_ago',  // Simplified to days for > month
            604800 => 'dashboard_days_ago',   // Simplified to days for > week
            86400 => 'dashboard_days_ago',
            3600 => 'dashboard_hours_ago',
            60 => 'dashboard_minutes_ago',
        ];

        foreach ($tokens as $unit => $textKey) {
            if ($time < $unit) {
                continue;
            }
            
            // For years/months we still just show days to be consistent
            if ($unit >= 86400) {
                 $numberOfDays = (int) floor($time / 86400);
                 return sprintf(rex_i18n::msg('dashboard_days_ago'), $numberOfDays);
            }
            
            $numberOfUnits = (int) floor($time / $unit);
            return sprintf(rex_i18n::msg($textKey), $numberOfUnits);
        }
        
        return '';
    }

    public function getData()
    {
        $user = rex::getUser();
        if (!$user) {
            return '<p class="text-muted">' . rex_i18n::msg('dashboard_no_permission', 'Keine Berechtigung.') . '</p>';
        }

        // Prüfen ob Version-Plugin verfügbar ist
        if (!rex_plugin::get('structure', 'version')->isAvailable()) {
            return '<div class="alert alert-warning">' . rex_i18n::msg('dashboard_modified_articles_no_plugin', 'Das Plugin "structure/version" muss aktiviert sein, um dieses Widget zu nutzen.') . '</div>';
        }

        $sql = rex_sql::factory();

        $query = '
            SELECT
                a.id,
                a.name,
                a.catname,
                a.updateuser,
                a.parent_id,
                a.clang_id,
                u.name as username,
                work.max_work_date,
                live.max_live_date
            FROM
                ' . rex::getTable('article') . ' a
            LEFT JOIN (
                SELECT
                    article_id,
                    clang_id,
                    MAX(updatedate) as max_work_date
                FROM ' . rex::getTable('article_slice') . '
                WHERE revision > 0
                GROUP BY article_id, clang_id
            ) work ON a.id = work.article_id AND a.clang_id = work.clang_id
            LEFT JOIN (
                SELECT
                    article_id,
                    clang_id,
                    MAX(updatedate) as max_live_date
                FROM ' . rex::getTable('article_slice') . '
                WHERE revision = 0
                GROUP BY article_id, clang_id
            ) live ON a.id = live.article_id AND a.clang_id = live.clang_id
            LEFT JOIN
                ' . rex::getTable('user') . ' u
                ON a.updateuser = u.login
            WHERE
                work.max_work_date IS NOT NULL
        ';

        // Benutzerrechte prüfen
        if (!$user->isAdmin() && $user->getComplexPerm('structure')) {
            $allowedCategories = $user->getComplexPerm('structure')->getMountpoints();
            if (!empty($allowedCategories)) {
                $query .= ' AND a.parent_id IN (' . implode(',', array_map('intval', $allowedCategories)) . ')';
            } else {
                // Keine Berechtigung für Kategorien
                return '<p class="text-muted">' . rex_i18n::msg('dashboard_no_article_permission', 'Keine Artikel-Berechtigung.') . '</p>';
            }
        }

        $query .= ' ORDER BY work.max_work_date DESC LIMIT 20';

        $sql->setQuery($query);

        if (0 == $sql->getRows()) {
            return '<p class="text-muted">' . rex_i18n::msg('dashboard_modified_articles_none', 'Keine geänderten Artikel vorhanden.') . '</p>';
        }

        $content = '<div class="table-responsive">';
        $content .= '<table class="table table-striped table-hover">';
        $content .= '<thead>';
        $content .= '<tr>';
        $content .= '<th>' . rex_i18n::msg('dashboard_article', 'Artikel') . '</th>';
        $content .= '<th>' . rex_i18n::msg('dashboard_category', 'Kategorie') . '</th>';
        
        // Sprach-Spalte nur anzeigen wenn mehrere Sprachen existieren
        if (count(rex_clang::getAll()) > 1) {
            $content .= '<th>' . rex_i18n::msg('dashboard_language', 'Sprache') . '</th>';
        }

        $content .= '<th>' . rex_i18n::msg('dashboard_modified_articles_date', 'Geändert am') . '</th>';
        $content .= '<th>' . rex_i18n::msg('dashboard_modified_articles_user', 'Bearbeiter') . '</th>';
        $content .= '</tr>';
        $content .= '</thead>';
        $content .= '<tbody>';

        foreach ($sql as $row) {
            $articleId = $row->getValue('id');
            $clangId = $row->getValue('clang_id');
            $parentId = $row->getValue('parent_id');

            // Prüfe Berechtigung für diesen spezifischen Artikel (genau wie in RecentArticles)
            if (!$user->isAdmin() && !$user->getComplexPerm('structure')->hasCategoryPerm($parentId)) {
                continue;
            }

            $articleName = rex_escape($row->getValue('name'));
            $catName = rex_escape($row->getValue('catname'));
            
            $timestamp = strtotime($row->getValue('max_work_date'));
            $liveTimestamp = $row->getValue('max_live_date') ? strtotime($row->getValue('max_live_date')) : 0;
            
            $updateDate = date('d.m.Y H:i', $timestamp);
            $timeAgo = $this->getTimeAgo($timestamp);
            $ageDays = floor((time() - $timestamp) / 86400);
            
            // Mark very old versions (older than 90 days) or outdated ones
            $style = '';
            $icon = '';
            
            if ($liveTimestamp > $timestamp) {
                // Live version is newer than working version
                $style = 'color: #e67e22;'; // orange
                $icon = ' <i class="fa fa-history" title="'.rex_i18n::msg('dashboard_older_than_live').'"></i>';
            } elseif ($ageDays > 90) {
                 // Very old draft
                 $style = 'color: #a94442;'; // danger color
                 $icon = ' <i class="fa fa-exclamation-triangle" title="'.rex_i18n::msg('dashboard_old_version').'"></i>';
            }
            
            $userName = rex_escape($row->getValue('username') ?: $row->getValue('updateuser'));

            $editUrl = rex_url::backendPage('content/edit', [
                'article_id' => $articleId,
                'clang' => $clangId
            ]);

            $content .= '<tr>';
            $content .= '<td><a href="' . $editUrl . '">' . $articleName . '</a></td>';
            $content .= '<td>' . ($catName ?: '-') . '</td>';
            
            // Sprach-Spalte nur anzeigen wenn mehrere Sprachen existieren
            if (count(rex_clang::getAll()) > 1) {
                // Name der Sprache holen
                $langName = rex_clang::get($clangId)->getName();
                $content .= '<td>' . rex_escape($langName) . '</td>';
            }

            $content .= '<td><span style="'.$style.'">' . $updateDate . $icon . '</span><br><small class="text-muted">' . $timeAgo . '</small></td>';
            $content .= '<td>' . $userName . '</td>';
            $content .= '</tr>';
        }

        $content .= '</tbody>';
        $content .= '</table>';
        $content .= '</div>';

        return $content;
    }
}
