<?php

namespace FriendsOfRedaxo\Dashboard\DemoItems;

use FriendsOfRedaxo\Dashboard\Dashboard;
use rex;
use rex_addon;
use rex_view;

class DashboardDemo
{
    public static function init()
    {
        $addon = rex_addon::get('dashboard');

        // Check if demo is enabled (check for string "1" since rex_config stores as string)
        if ('1' !== $addon->getConfig('demo_enabled', '0')) {
            return;
        }

        if (rex::isBackend()) {
            // Explizit Chart.js laden für Demo-Charts
            rex_view::addJsFile($addon->getAssetsUrl('js/chart.min.js'));

            Dashboard::addItem(
                Info::factory('dashboard-demo-1', 'Demo 1'),
            );

            Dashboard::addItem(
                Info::factory('dashboard-demo-2', 'Demo 2')
                    ->setColumns(2),
            );

            Dashboard::addItem(
                Info::factory('dashboard-demo-3', 'Demo 3')
                    ->setColumns(3),
            );

            Dashboard::addItem(
                ChartBarHorizontal::factory('dashboard-demo-chart-bar-horizontal', 'Chartdemo Balken horizontal'),
            );

            Dashboard::addItem(
                ChartBarVertical::factory('dashboard-demo-chart-bar-vertical', 'Chartdemo Balken vertikal'),
            );

            Dashboard::addItem(
                ChartPie::factory('dashboard-demo-chart-pie', 'Chartdemo Kreisdiagramm'),
            );

            Dashboard::addItem(
                ChartPie::factory('dashboard-demo-chart-donut', 'Chartdemo Donutdiagramm')
                    ->setDonut(),
            );

            Dashboard::addItem(
                Table::factory('dashboard-demo-table-sql', 'Tabelle (SQL)')
                    ->setTableAttribute('data-locale', 'de-DE'),
            );

            Dashboard::addItem(
                ChartLine::factory('dashboard-demo-chart-line', 'Liniendiagramm'),
            );

            Dashboard::addItem(
                BigNumber::factory('dashboard-demo-big-number', 'Big Number Demo')
                    ->setColumns(1), // Als kleines Widget
            );
        }
    }
}
