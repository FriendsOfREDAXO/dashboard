<?php

$addon = rex_addon::get('dashboard');

// Handle AJAX requests for RSS Widget
if (rex_request('ajax', 'string') === 'save_rss_config') {
    header('Content-Type: application/json');
    
    try {
        $widgetId = rex_request('widget_id', 'string');
        $feedUrl = rex_request('feed_url', 'string');
        $feedName = rex_request('feed_name', 'string');
        
        if (empty($widgetId) || empty($feedUrl)) {
            throw new Exception('Fehlende Parameter');
        }
        
        // Validiere URL
        if (!filter_var($feedUrl, FILTER_VALIDATE_URL)) {
            throw new Exception('Ungültige URL');
        }
        
        // Speichere in Session
        if (!isset($_SESSION['dashboard_rss_feeds'])) {
            $_SESSION['dashboard_rss_feeds'] = [];
        }
        
        $_SESSION['dashboard_rss_feeds'][$widgetId] = [
            'url' => $feedUrl,
            'name' => $feedName ?: 'RSS Feed',
        ];
        
        echo json_encode(['success' => true]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
    exit;
}

if (rex_request('ajax', 'string') === 'load_rss_feed') {
    header('Content-Type: application/json');
    
    try {
        $widgetId = rex_request('widget_id', 'string');
        $page = (int) rex_request('page', 'int', 1);
        
        if (empty($widgetId)) {
            throw new Exception('Widget ID fehlt');
        }
        
        // Hole Konfiguration aus Session
        if (!isset($_SESSION['dashboard_rss_feeds'][$widgetId])) {
            throw new Exception('Feed-Konfiguration nicht gefunden');
        }
        
        $config = $_SESSION['dashboard_rss_feeds'][$widgetId];
        
        // Lade RSS Feed Content
        $content = loadRSSFeedContent($config['url'], $config['name'], $page, $widgetId);
        
        echo json_encode([
            'success' => true,
            'content' => $content
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
    exit;
}

/**
 * Lädt RSS Feed Content
 */
function loadRSSFeedContent(string $feedUrl, string $feedName, int $page = 1, string $widgetId = ''): string
{
    $itemsPerPage = 5;
    $maxItems = 100;
    
    // RSS Feed laden
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $feedUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_USERAGENT, 'REDAXO Dashboard RSS Reader');
    
    $rssData = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if (curl_errno($ch) || $httpCode !== 200) {
        curl_close($ch);
        throw new Exception('Fehler beim Laden des RSS-Feeds');
    }
    
    curl_close($ch);
    
    // XML parsen
    libxml_use_internal_errors(true);
    $xml = simplexml_load_string($rssData);
    
    if (!$xml) {
        throw new Exception('Ungültiges RSS-Feed-Format');
    }
    
    $items = [];
    
    // RSS 2.0 Format
    if (isset($xml->channel->item)) {
        foreach ($xml->channel->item as $item) {
            $items[] = [
                'title' => (string) $item->title,
                'link' => (string) $item->link,
                'description' => (string) $item->description,
                'pubDate' => (string) $item->pubDate,
            ];
            
            if (count($items) >= $maxItems) break;
        }
    }
    // Atom Format
    elseif (isset($xml->entry)) {
        foreach ($xml->entry as $entry) {
            $items[] = [
                'title' => (string) $entry->title,
                'link' => (string) $entry->link['href'],
                'description' => (string) $entry->summary,
                'pubDate' => (string) $entry->published,
            ];
            
            if (count($items) >= $maxItems) break;
        }
    }
    
    if (empty($items)) {
        return '<div class="alert alert-info">Keine RSS-Einträge gefunden.</div>';
    }
    
    // Paginierung
    $totalItems = count($items);
    $totalPages = ceil($totalItems / $itemsPerPage);
    $page = max(1, min($page, $totalPages));
    $offset = ($page - 1) * $itemsPerPage;
    $pageItems = array_slice($items, $offset, $itemsPerPage);
    
    // Content generieren
    $content = '<div class="rss-feed-content">';
    
    foreach ($pageItems as $item) {
        $content .= '<div class="rss-item" style="border-bottom: 1px solid #eee; padding: 10px 0;">';
        $content .= '<h5><a href="' . rex_escape($item['link']) . '" target="_blank" style="color: #337ab7;">';
        $content .= rex_escape($item['title']) . '</a></h5>';
        
        if ($item['pubDate']) {
            $date = date('d.m.Y H:i', strtotime($item['pubDate']));
            $content .= '<small class="text-muted">' . $date . '</small>';
        }
        
        if ($item['description']) {
            $description = strip_tags($item['description']);
            $description = mb_strlen($description) > 200 ? mb_substr($description, 0, 200) . '...' : $description;
            $content .= '<p style="margin: 5px 0 0 0;">' . rex_escape($description) . '</p>';
        }
        
        $content .= '</div>';
    }
    
    // Paginierung
    if ($totalPages > 1) {
        $content .= '<div class="rss-pagination" style="margin-top: 15px; text-align: center;">';
        $content .= '<nav><ul class="pagination pagination-sm" style="margin: 0;">';
        
        for ($i = 1; $i <= $totalPages; $i++) {
            $active = $i === $page ? ' class="active"' : '';
            $content .= '<li' . $active . '>';
            $content .= '<a href="#" onclick="dashboardRSSWidget.loadFeed(\'' . rex_escape($widgetId) . '\', ' . $i . '); return false;">';
            $content .= $i . '</a></li>';
        }
        
        $content .= '</ul></nav>';
        $content .= '</div>';
    }
    
    $content .= '</div>';
    
    return $content;
}

// Handle config subpage
$subpage = rex_request('subpage', 'string', '');

if ('config' === $subpage) {
    // Check if user is admin
    if (!rex::getUser() || !rex::getUser()->isAdmin()) {
        throw new rex_exception('Access denied');
    }
    
    include __DIR__ . '/config.php';
    return;
}

// Default dashboard view
echo rex_view::title(rex_dashboard::getHeader() . $addon->i18n('title'));
echo '<div id="rex-dashboard">'.rex_dashboard::get().'</div>';

?>