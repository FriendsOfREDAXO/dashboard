<?php

/**
 * @package yakamara\dashboard
 */
class rex_dashboard
{
    private static $instance = null; // ab php 7.4 ?self

    /** @var rex_dashboard_item[] $items */
    static $items = [];

    private function __construct()
    {

    }

    public static function init()
    {
        if (is_null(static::$instance)) {
            static::$instance = new self();
        }

        foreach (rex_dashboard_item::getCssFiles() as $filename) {
            rex_view::addCssFile($filename);
        }

        foreach (rex_dashboard_item::getJsFiles() as $filename) {
            rex_view::addJsFile($filename);
        }
    }

    public static function get()
    {
        $outputActive = $outputInactive = '';
        foreach (static::$items as $item) {
            if ($item->isActive()) {
                $outputActive .= (new rex_fragment(['item' => $item]))->parse('item.php');
            }
            else {
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
            $select->addOption($item->getName(), $item->getId());

            if ($item->isActive()) {
                $select->setSelected($item->getId());
            }
        }

        return (new rex_fragment([
            'outputActive' => $outputActive,
            'outputInactive' => $outputInactive,
            'widgetSelect' => $select->get(),
        ]))->parse('dashboard.php');
    }

    public static function addItem(rex_dashboard_item $item)
    {
        static::$items[$item->getId()] = $item;
    }

    public static function getItem($id) : ?rex_dashboard_item
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
