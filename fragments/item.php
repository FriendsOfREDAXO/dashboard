<?php

namespace FriendsOfRedaxo\Dashboard;

use rex;
use rex_fragment;
use rex_i18n;
use rex_string;

/** @var rex_fragment $this
 * @var DashboardItem $item
 */
$item = $this->getVar('item');
$content = $item->getContent();
?>
<div class="grid-stack-item"<?= rex_string::buildAttributes($item->getAttributes())?>>
    <div class="grid-stack-item-content">
        <div class="panel panel-info">
            <?php if ($item->getOption('show-header')): ?>
                <header class="panel-heading">
                    <div class="panel-title"><?= rex_escape(htmlspecialchars_decode($item->getName())) ?></div>
                    <div class="actions">
                        <?php if ($item->isCached()): ?>
                            <div class="grid-stack-item-refresh" title="<?= rex_i18n::msg('dashboard_action_refresh_title') . ' ' . $item->getCacheDate()->format(rex_i18n::msg('dashboard_action_refresh_title_dateformat')) ?>"><i class="glyphicon glyphicon-refresh"></i></div>
                        <?php endif ?>
                        <div class="grid-stack-item-hide" title="<?= rex_i18n::msg('dashboard_action_hide_title') ?>"><i class="glyphicon glyphicon-remove-circle"></i></div>
                        <?php if (rex::getUser()->hasPerm('dashboard[move-items]')): ?>
                            <div class="grid-stack-item-handle"><i class="glyphicon glyphicon-move"></i></div>
                        <?php endif ?>
                    </div>
                </header>
            <?php endif ?>
            <div class="panel-body"><?= $content ?></div>
            <footer class="panel-footer">
                <div class="rex-form-panel-footer">
                    <div class="cache-date">
                        <?= rex_i18n::msg('dashboard_action_refresh_title') . ' <span class="date">' . $item->getCacheDate()->format(rex_i18n::msg('dashboard_action_refresh_title_dateformat')) . '</span>' ?>
                    </div>
                </div>
            </footer>
        </div>
    </div>
</div>
