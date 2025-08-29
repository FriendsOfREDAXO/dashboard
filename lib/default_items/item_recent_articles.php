<?php

namespace FriendsOfREDAXO\Dashboard;

use rex_dashboard_item;
use rex_sql;
use rex_clang;
use rex_url;
use rex_i18n;
use rex;
use rex_formatter;
use rex_escape;

/**
 * Dashboard Item: Zuletzt aktualisierte Artikel
 */
class DashboardItemRecentArticles extends rex_dashboard_item
{
    public function getTitle(): string
    {
        return rex_i18n::msg('dashboard_recent_articles_title', 'Zuletzt aktualisierte Artikel');
    }

    public function getData()
    {
        $sql = rex_sql::factory();
        
        $query = '
            SELECT 
                a.id,
                a.name,
                a.updatedate,
                a.updateuser,
                a.clang_id,
                c.name as category_name,
                cl.name as lang_name
            FROM ' . rex::getTable('article') . ' a
            LEFT JOIN ' . rex::getTable('article') . ' c ON a.path LIKE CONCAT("%|", c.id, "|%") AND c.startarticle = 1 AND c.clang_id = a.clang_id
            LEFT JOIN ' . rex::getTable('clang') . ' cl ON a.clang_id = cl.id
            WHERE a.updatedate > 0
            ORDER BY a.updatedate DESC
            LIMIT 10
        ';
        
        $articles = $sql->getArray($query);
        
        if (empty($articles)) {
            return '<p class="text-muted">Keine Artikel gefunden.</p>';
        }
        
        $content = '<div class="table-responsive">';
        $content .= '<table class="table table-striped table-hover">';
        $content .= '<thead>';
        $content .= '<tr>';
        $content .= '<th>Artikel</th>';
        $content .= '<th>Kategorie</th>';
        $content .= '<th>Sprache</th>';
        $content .= '<th>Aktualisiert</th>';
        $content .= '<th>Von</th>';
        $content .= '</tr>';
        $content .= '</thead>';
        $content .= '<tbody>';
        
        foreach ($articles as $article) {
            $editUrl = rex_url::backendPage('content/edit', [
                'article_id' => $article['id'],
                'clang' => $article['clang_id'] ?? 1
            ]);
            
            $content .= '<tr>';
            $content .= '<td><a href="' . $editUrl . '">' . rex_escape($article['name']) . '</a></td>';
            $content .= '<td>' . rex_escape($article['category_name'] ?: '-') . '</td>';
            $content .= '<td>' . rex_escape($article['lang_name'] ?: '-') . '</td>';
            $content .= '<td>' . rex_formatter::strftime($article['updatedate'], 'datetime') . '</td>';
            $content .= '<td>' . rex_escape($article['updateuser'] ?: '-') . '</td>';
            $content .= '</tr>';
        }
        
        $content .= '</tbody>';
        $content .= '</table>';
        $content .= '</div>';
        
        return $content;
    }
}
