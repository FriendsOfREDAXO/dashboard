<?php

namespace FriendsOfREDAXO\Dashboard;

use rex_addon;
use rex;
use rex_dashboard;
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
            
            rex_dashboard::addItem(
                DashboardItemDemo::factory('dashboard-demo-1', 'Demo 1')
            );

            rex_dashboard::addItem(
                DashboardItemDemo::factory('dashboard-demo-2', 'Demo 2')
                    ->setColumns(2)
            );

            rex_dashboard::addItem(
                DashboardItemDemo::factory('dashboard-demo-3', 'Demo 3')
                    ->setColumns(3)
            );

            rex_dashboard::addItem(
                DashboardItemDemoChartBarHorizontal::factory('dashboard-demo-chart-bar-horizontal', 'Chartdemo Balken horizontal')
            );

            rex_dashboard::addItem(
                DashboardItemDemoChartBarVertical::factory('dashboard-demo-chart-bar-vertical', 'Chartdemo Balken vertikal')
            );

            rex_dashboard::addItem(
                DashboardItemDemoChartPie::factory('dashboard-demo-chart-pie', 'Chartdemo Kreisdiagramm')
            );

            rex_dashboard::addItem(
                DashboardItemDemoChartPie::factory('dashboard-demo-chart-donut', 'Chartdemo Donutdiagramm')
                    ->setDonut()
            );

            rex_dashboard::addItem(
                DashboardItemDemoTable::factory('dashboard-demo-table-sql', 'Tabelle (SQL)')
                    ->setTableAttribute('data-locale', 'de-DE')
            );

            rex_dashboard::addItem(
                DashboardItemDemoChartLine::factory('dashboard-demo-chart-line', 'Liniendiagramm')
            );
        }
    }
}
