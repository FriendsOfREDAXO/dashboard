<?php

$addon = rex_addon::get('dashboard');

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