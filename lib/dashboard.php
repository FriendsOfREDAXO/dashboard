<?php

namespace FriendsOfRedaxo\Dashboard;

use FriendsOfRedaxo\Dashboard\Base\Item;
use rex;
use rex_fragment;
use rex_i18n;
use rex_select;
use rex_url;
use rex_view;

class Dashboard
{
    private static $instance; // ab php 7.4 ?self

    /** @var array<ItemBase> */
    public static $items = [];

    private function __construct() {}

    public static function init()
    {
        if (null === static::$instance) {
            static::$instance = new self();
        }

        foreach (Item::getCssFiles() as $filename) {
            rex_view::addCssFile($filename);
        }

        foreach (Item::getJsFiles() as $filename) {
            rex_view::addJsFile($filename);
        }
    }

    public static function get()
    {
        $outputActive = $outputInactive = '';
        foreach (static::$items as $item) {
            if ($item->isActive()) {
                $outputActive .= (new rex_fragment(['item' => $item]))->parse('item.php');
            } else {
                $outputInactive .= (new rex_fragment(['item' => $item]))->parse('item.php');
            }
        }

        // Generate widget select for dashboard settings
        $select = new rex_select();
        $select->setSize(1);
        $select->setName('widgets[]');
        $select->setId('widget-select');
        $select->setMultiple();
        $select->setAttribute('class', 'form-control selectpicker');
        $select->setAttribute('data-selected-text-format', 'static');
        $select->setAttribute('data-title', rex_i18n::msg('dashboard_select_widget_title'));
        $select->setAttribute('data-dropdown-align-right', 'auto');

        foreach (static::$items as $item) {
            $select->addOption(htmlspecialchars_decode($item->getName()), $item->getId());

            if ($item->isActive()) {
                $select->setSelected($item->getId());
            }
        }

        // Generate config button for admins
        $configButton = '';
        if (rex::getUser() && rex::getUser()->isAdmin()) {
            $configUrl = rex_url::backendPage('dashboard', ['subpage' => 'config']);
            $configButton = '<a href="' . $configUrl . '" class="btn btn-default" title="' . rex_i18n::msg('dashboard_config_title') . '"><i class="rex-icon fa-cog"></i></a> ';
        }

        return (new rex_fragment([
            'outputActive' => $outputActive,
            'outputInactive' => $outputInactive,
            'widgetSelect' => $select->get(),
            'configButton' => $configButton,
        ]))->parse('dashboard.php');
    }

    public static function addItem(Item $item)
    {
        static::$items[$item->getId()] = $item;
    }

    public static function getItem($id): ?Item
    {
        return static::$items[$id] ?? null;
    }

    public static function getItems($ids)
    {
        $items = [];

        if (empty($ids)) {
            return static::$items;
        }

        foreach ($ids as $id) {
            if (static::itemExists($id)) {
                $items[$id] = static::$items[$id];
            }
        }

        return $items;
    }

    public static function getHeader()
    {
        return '';
    }

    public static function itemExists($id)
    {
        foreach (static::$items as $item) {
            if ($item->getId() === $id) {
                return true;
            }
        }

        return false;
    }
}
