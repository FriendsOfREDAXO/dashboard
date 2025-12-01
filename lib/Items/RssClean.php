<?php

namespace FriendsOfRedaxo\Dashboard\Items;

use Exception;
use FriendsOfRedaxo\Dashboard\Base\Item;
use rex_config;
use rex_i18n;
use rex_response;

use function array_slice;
use function count;

/**
 * Dashboard Item: RSS-Feed Widget (Clean Version).
 */
class RssClean extends Item
{
    protected function __construct($id, $name)
    {
        parent::__construct($id, $name);
    }

    public function getTitle(): string
    {
        return rex_i18n::msg('dashboard_rss_feed_title', 'RSS Feed');
    }

    protected function getData()
    {
        // RSS-Feeds aus Konfiguration laden - sowohl neues als auch altes Format unterstützen
        $rssFeeds = rex_config::get('dashboard', 'rss_feeds', []);

        // Fallback: Altes Format (einzelner Feed) in neues Format konvertieren
        if (empty($rssFeeds)) {
            $legacyUrl = rex_config::get('dashboard', 'rss_feed_url', '');
            $legacyName = rex_config::get('dashboard', 'rss_feed_name', '');

            if (!empty($legacyUrl)) {
                $rssFeeds = [
                    [
                        'title' => !empty($legacyName) ? $legacyName : 'RSS-Feed',
                        'url' => $legacyUrl,
                    ],
                ];
            }
        }

        if (empty($rssFeeds)) {
            return '<div class="alert alert-info">
                        <i class="fa fa-info-circle"></i> 
                        <strong>Keine RSS-Feeds konfiguriert</strong><br>
                        <small>Gehe zu <em>Dashboard > Konfiguration</em> um RSS-Feeds hinzuzufügen.</small>
                    </div>';
        }

        // Ersten konfigurierten Feed verwenden
        $firstFeed = reset($rssFeeds);
        $rssUrl = $firstFeed['url'] ?? '';
        $feedTitle = $firstFeed['title'] ?? 'RSS-Feed';

        if (empty($rssUrl)) {
            return '<div class="alert alert-warning">
                        <i class="fa fa-exclamation-triangle"></i> 
                        <strong>RSS-Feed URL ist leer</strong><br>
                        <small>Bitte prüfe die Konfiguration.</small>
                    </div>';
        }

        // RSS-Feed laden
        $rssData = $this->loadRssFeed($rssUrl);

        if (!$rssData) {
            return '<div class="alert alert-danger">
                        <i class="fa fa-exclamation-triangle"></i> 
                        <strong>RSS-Feed konnte nicht geladen werden</strong><br>
                        <small class="text-muted">URL: ' . rex_escape($rssUrl) . '</small><br>
                        <small>Mögliche Ursachen: SSL-Probleme, Timeout, ungültiges XML</small>
                    </div>';
        }

        if (empty($rssData['items'])) {
            return '<div class="alert alert-warning">
                        <i class="fa fa-info-circle"></i> 
                        <strong>RSS-Feed ist leer</strong><br>
                        <small class="text-muted">Feed: ' . rex_escape($feedTitle) . '</small>
                    </div>';
        }

        // JavaScript-basierte Paginierung - alle Items laden, per JS 2 pro Seite anzeigen
        $itemsPerPage = 2;
        $maxItems = min(30, count($rssData['items'])); // Maximal 30 Items laden
        $items = array_slice($rssData['items'], 0, $maxItems);
        $totalPages = ceil($maxItems / $itemsPerPage);

        $output = '<div class="rss-widget" id="rss-widget-container">';
        $output .= '<h6 class="text-muted mb-3"><i class="fa fa-rss"></i> ' . rex_escape($feedTitle) . '</h6>';

        // Container für RSS-Items
        $output .= '<div id="rss-items-container" style="min-height: 200px;">';

        // Alle RSS-Items als HTML generieren
        foreach ($items as $index => $item) {
            $pubDate = !empty($item['pubDate']) ? date('d.m.Y H:i', strtotime($item['pubDate'])) : '';
            $page = floor($index / $itemsPerPage) + 1;
            $isFirstPage = 1 === $page;

            $output .= '<div class="rss-item mb-3 rss-page-' . $page . '" data-page="' . $page . '" style="display: ' . ($isFirstPage ? 'block' : 'none') . ';">';
            $output .= '<h6 class="mb-1">';
            $output .= '<a href="' . rex_escape($item['link']) . '" target="_blank" class="text-decoration-none">';
            $output .= rex_escape($item['title']);
            $output .= ' <i class="fa fa-external-link fa-xs ms-1"></i>';
            $output .= '</a></h6>';

            if (!empty($item['description'])) {
                $description = strip_tags($item['description']);
                $description = mb_substr($description, 0, 120) . (mb_strlen($description) > 120 ? '...' : '');
                $output .= '<p class="text-muted mb-1 small">' . rex_escape($description) . '</p>';
            }

            if ($pubDate) {
                $output .= '<small class="text-muted"><i class="fa fa-clock"></i> ' . $pubDate . '</small>';
            }

            $output .= '</div>';

            // Trennlinie zwischen Items der gleichen Seite
            $isLastItemOnPage = ($index + 1) % $itemsPerPage === 0;
            $isLastItemOverall = $index === count($items) - 1;

            if (!$isLastItemOnPage && !$isLastItemOverall) {
                $output .= '<hr class="my-2 rss-page-' . $page . '" data-page="' . $page . '" style="display: ' . ($isFirstPage ? 'block' : 'none') . ';">';
            }
        }

        $output .= '</div>'; // Ende rss-items-container

        // JavaScript-Paginierung für Bootstrap 3
        if ($totalPages > 1) {
            $output .= '<nav class="text-center" style="margin-top: 20px;">';
            $output .= '<ul class="pagination pagination-sm" id="rss-pagination" style="margin: 0; justify-content: center;">';

            // Previous Button
            $output .= '<li id="rss-prev">';
            $output .= '<a href="#" onclick="rssChangePage(-1); return false;">&laquo;</a>';
            $output .= '</li>';

            // Page Numbers
            for ($i = 1; $i <= $totalPages; ++$i) {
                $active = 1 === $i ? ' class="active"' : '';
                $output .= '<li data-page="' . $i . '"' . $active . '>';
                $output .= '<a href="#" onclick="rssShowPage(' . $i . '); return false;">' . $i . '</a>';
                $output .= '</li>';
            }

            // Next Button
            $output .= '<li id="rss-next">';
            $output .= '<a href="#" onclick="rssChangePage(1); return false;">&raquo;</a>';
            $output .= '</li>';

            $output .= '</ul>';
            $output .= '</nav>';
        }

        // JavaScript für Paginierung
        $output .= '
        <script nonce="' . rex_response::getNonce() . '">
        (function() {
            let rssCurrentPage = 1;
            let rssTotalPages = ' . $totalPages . ';
            
            window.rssShowPage = function(page) {
                var container = document.getElementById("rss-items-container");
                if (!container) return;
                
                // Fade out
                container.style.opacity = "0";
                container.style.transition = "opacity 0.3s ease-in-out";
                
                setTimeout(function() {
                    // Alle Items verstecken
                    var allItems = container.querySelectorAll(".rss-item, hr");
                    for (var i = 0; i < allItems.length; i++) {
                        allItems[i].style.display = "none";
                    }
                    
                    // Items der gewählten Seite anzeigen
                    var pageItems = container.querySelectorAll("[data-page=\"" + page + "\"]");
                    for (var i = 0; i < pageItems.length; i++) {
                        pageItems[i].style.display = "block";
                    }
                    
                    // Fade in
                    container.style.opacity = "1";
                    
                    // Pagination Updates (Bootstrap 3 style)
                    var paginationItems = document.querySelectorAll("#rss-pagination li");
                    for (var i = 0; i < paginationItems.length; i++) {
                        paginationItems[i].classList.remove("active");
                    }
                    
                    var pageItem = document.querySelector("#rss-pagination li[data-page=\"" + page + "\"]");
                    if (pageItem) {
                        pageItem.classList.add("active");
                    }
                    
                    // Prev/Next Buttons (Bootstrap 3 style)
                    var prevBtn = document.getElementById("rss-prev");
                    var nextBtn = document.getElementById("rss-next");
                    
                    if (prevBtn) {
                        if (page <= 1) {
                            prevBtn.classList.add("disabled");
                        } else {
                            prevBtn.classList.remove("disabled");
                        }
                    }
                    
                    if (nextBtn) {
                        if (page >= rssTotalPages) {
                            nextBtn.classList.add("disabled");
                        } else {
                            nextBtn.classList.remove("disabled");
                        }
                    }
                    
                    rssCurrentPage = page;
                    
                }, 150); // Halbe Fade-out Zeit
            };
            
            window.rssChangePage = function(direction) {
                let newPage = rssCurrentPage + direction;
                if (newPage >= 1 && newPage <= rssTotalPages) {
                    rssShowPage(newPage);
                }
            };
            
            // Initial setup - sofort nach DOM-Aufbau
            setTimeout(function() {
                rssShowPage(1);
            }, 100);
            
        })();
        </script>';

        $output .= '</div>';

        return $output;
    }

    /**
     * Lädt RSS-Feed Daten mit verbesserter Fehlerbehandlung.
     */
    private function loadRssFeed($url)
    {
        try {
            // Timeout und User-Agent setzen
            $context = stream_context_create([
                'http' => [
                    'timeout' => 15,
                    'user_agent' => 'REDAXO Dashboard Widget 2.0',
                    'method' => 'GET',
                    'header' => "Accept: application/rss+xml, application/xml, text/xml\r\n",
                ],
                // SSL-Verifizierung aktiviert lassen für Sicherheit
                /*
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true,
                ],
                */
            ]);

            // RSS-Feed laden
            $xml = @file_get_contents($url, false, $context);

            if (false === $xml) {
                $error = error_get_last();
                error_log('RSS Feed Error: Could not load URL ' . $url . ' - ' . ($error['message'] ?? 'Unknown error'));
                return false;
            }

            // XML parsen
            libxml_use_internal_errors(true);
            $rss = @simplexml_load_string($xml);

            if (false === $rss) {
                $errors = libxml_get_errors();
                $errorMsg = !empty($errors) ? $errors[0]->message : 'Invalid XML';
                error_log('RSS Feed XML Error: ' . trim($errorMsg));
                libxml_clear_errors();
                return false;
            }

            $items = [];

            // RSS 2.0 Format
            if (isset($rss->channel->item)) {
                foreach ($rss->channel->item as $item) {
                    $items[] = [
                        'title' => (string) $item->title,
                        'link' => (string) $item->link,
                        'description' => (string) $item->description,
                        'pubDate' => (string) $item->pubDate,
                    ];
                }
            }
            // Atom Format
            elseif (isset($rss->entry)) {
                foreach ($rss->entry as $entry) {
                    $link = (string) $entry->link['href'] ?: (string) $entry->link;
                    $items[] = [
                        'title' => (string) $entry->title,
                        'link' => $link,
                        'description' => (string) ($entry->summary ?: $entry->content),
                        'pubDate' => (string) ($entry->published ?: $entry->updated),
                    ];
                }
            }
            // Fallback: Prüfe andere Formate
            elseif (isset($rss->item)) {
                foreach ($rss->item as $item) {
                    $items[] = [
                        'title' => (string) $item->title,
                        'link' => (string) $item->link,
                        'description' => (string) $item->description,
                        'pubDate' => (string) $item->pubDate,
                    ];
                }
            }

            $feedTitle = (string) ($rss->channel->title ?? $rss->title ?? 'RSS-Feed');

            return [
                'title' => $feedTitle,
                'items' => $items,
            ];
        } catch (Exception $e) {
            // Debug: Error-Log für Entwicklung
            error_log('RSS Feed Exception: ' . $e->getMessage());
            return false;
        }
    }
}
