(function($) {
    let grid
        , items = []
        , $items = []
        , widgetCompactTimeoutHandler = null
        , autoRefreshInterval = null
        , autoRefreshPaused = false
        , autoRefreshIntervalTime = 30000 // 30 seconds (wie Dashboard2)
        , refreshQueue = []
        , refreshInProgress = false
        , dashboard = {
        store: function()
        {
            let data = {};
            for (let i in items) {
                if (!items[i].gridstackNode) {
                    data[$items[i].data('id')] = {
                        'gs-w': 0,
                        'gs-h': 0,
                        'gs-x': 0,
                        'gs-y': 0,
                        'data-active': 0,
                    };
                }
                else {
                    data[$items[i].data('id')] = {
                        'gs-w': items[i].gridstackNode.w,
                        'gs-h': items[i].gridstackNode.h,
                        'gs-x': items[i].gridstackNode.x,
                        'gs-y': items[i].gridstackNode.y,
                        'data-active': 1,
                    };
                }
            }

            $.post('index.php', {
                'page': 'dashboard',
                'data': data,
                'rex-api-call': 'dashboard_store',
            });
        },
        resize: function()
        {
            for (let i in items) {
                if (!items[i].gridstackNode || !$items[i].is(':visible')) {
                    continue;
                }

                // Chart.js Charts neu rendern wenn vorhanden
                let $chart = $items[i].find('canvas[id^="chart-"]');
                if ($chart.length && window.Chart) {
                    let chartId = $chart.attr('id');
                    if (window.Chart.getChart && window.Chart.getChart(chartId)) {
                        try {
                            window.Chart.getChart(chartId).resize();
                        } catch(e) {
                            console.log('Chart resize error:', e);
                        }
                    }
                }
                
                // Bootstrap Tables neu berechnen
                $items[i].find('.bootstrap-table').each(function() {
                    try {
                        if ($(this).data('bootstrap.table')) {
                            $(this).bootstrapTable('resetView');
                        }
                    } catch(e) {
                        console.log('Table resize error:', e);
                    }
                });

                let cellHeight = Math.ceil(($items[i].find('.panel-body').prop('scrollHeight')
                    + $items[i].find('.panel-heading').outerHeight(true)
                    + $items[i].find('.panel-footer').outerHeight(true)
                    + grid.opts.marginTop
                    + grid.opts.marginBottom) / grid.getCellHeight());

                if (cellHeight === items[i].gridstackNode.h) {
                    // do nothing, when height is already set
                    continue;
                }

                $items[i].addClass('resizing');
                setTimeout(function() {
                    $(items[i].el).removeClass('resizing');
                }, 500);

                grid.update(items[i], {
                    h: cellHeight
                });
            }
        },
        compact: function()
        {
            grid.compact();
        },
        getContent: function(ids, callback)
        {
            $.getJSON('index.php', {
                'page': 'dashboard',
                'ids': ids,
                'rex-api-call': 'dashboard_get',
            }, function(data)
            {
                if (typeof callback === 'function') {
                    callback(data);
                }
            });
        },
        startAutoRefresh: function()
        {
            if (autoRefreshInterval) {
                clearInterval(autoRefreshInterval);
            }
            
            autoRefreshInterval = setInterval(function() {
                if (!autoRefreshPaused) {
                    dashboard.refreshAllWidgets();
                }
            }, autoRefreshIntervalTime);
            
            autoRefreshPaused = false;
            dashboard.updateAutoRefreshUI();
        },
        stopAutoRefresh: function()
        {
            if (autoRefreshInterval) {
                clearInterval(autoRefreshInterval);
                autoRefreshInterval = null;
            }
            autoRefreshPaused = true;
            dashboard.updateAutoRefreshUI();
        },
        pauseAutoRefresh: function()
        {
            autoRefreshPaused = true;
            dashboard.updateAutoRefreshUI();
        },
        resumeAutoRefresh: function()
        {
            autoRefreshPaused = false;
            dashboard.updateAutoRefreshUI();
        },
        refreshAllWidgets: function()
        {
            let $items = $('.grid-stack .grid-stack-item');
            
            if ($items.length === 0) return;
            
            // Clear existing queue and start fresh
            refreshQueue = [];
            refreshInProgress = false;
            
            // Add all widgets to queue
            $items.each(function() {
                refreshQueue.push($(this));
            });
            
            // Start processing queue
            dashboard.processRefreshQueue();
        },
        processRefreshQueue: function()
        {
            if (refreshQueue.length === 0) {
                refreshInProgress = false;
                // Nach dem letzten Widget-Refresh automatisch Layout anpassen
                setTimeout(function() {
                    dashboard.resize();
                    dashboard.compact();
                }, 500);
                return;
            }
            
            if (refreshInProgress) {
                return; // Already processing
            }
            
            refreshInProgress = true;
            let $currentWidget = refreshQueue.shift();
            
            // Visual indicator for which widget is being refreshed
            dashboard.showRefreshIndicator($currentWidget);
            
            setTimeout(function() {
                $currentWidget.find('.grid-stack-item-refresh').click();
                
                // Längere Wartezeit damit der Glow-Effekt sichtbar wird
                setTimeout(function() {
                    dashboard.hideRefreshIndicator($currentWidget);
                    refreshInProgress = false;
                    dashboard.processRefreshQueue();
                }, 3000); // 3 Sekunden zwischen Widgets für sichtbaren Glow
            }, 500); // Längere Startverzögerung
        },
        showRefreshIndicator: function($widget)
        {
            // Dashboard 2.0 Effekt: Loading state mit CSS variables
            $widget.addClass('refreshing-active').addClass('loading');
        },
        hideRefreshIndicator: function($widget)
        {
            $widget.removeClass('refreshing-active').removeClass('loading');
        },
        refreshSingleWidget: function($widget)
        {
            dashboard.showRefreshIndicator($widget);
            $widget.addClass('loading');
            
            dashboard.getContent([$widget.data('id')], function(data)
            {
                $widget.find('.grid-stack-item-content .panel-body').html(data[$widget.data('id')].content);
                $widget.find('.grid-stack-item-content .cache-date .date').html(data[$widget.data('id')].date);
                
                // Bootstrap Tables initialisieren falls vorhanden
                $widget.find('.bootstrap-table').each(function() {
                    if ($(this).hasClass('bootstrap-table')) {
                        $(this).bootstrapTable('destroy');
                    }
                    $(this).bootstrapTable();
                });
                
                // Charts neu rendern falls vorhanden  
                let $chart = $widget.find('canvas[id^="chart-"]');
                if ($chart.length && window.Chart) {
                    setTimeout(function() {
                        let chartId = $chart.attr('id');
                        if (window.Chart.getChart && window.Chart.getChart(chartId)) {
                            try {
                                window.Chart.getChart(chartId).resize();
                            } catch(e) {
                                console.log('Chart update error:', e);
                            }
                        }
                    }, 50);
                }
                
                $widget.removeClass('loading');
                dashboard.hideRefreshIndicator($widget);
                
                // Trigger dashboard refresh event
                $(document).trigger('dashboard:refresh', $widget);
                
                // Nach dem Reload automatisch Resize durchführen
                setTimeout(function() {
                    dashboard.resize();
                    dashboard.compact();
                }, 100);
            });
        },
        updateAutoRefreshUI: function()
        {
            let $button = $('#rex-dashboard-auto-refresh');
            let $icon = $button.find('i');
            
            if (autoRefreshInterval && !autoRefreshPaused) {
                $button.removeClass('paused').addClass('active');
                $icon.removeClass('glyphicon-play').addClass('glyphicon-pause');
                $button.attr('title', $button.data('title-pause'));
            } else {
                $button.removeClass('active').addClass('paused');
                $icon.removeClass('glyphicon-pause').addClass('glyphicon-play');
                $button.attr('title', $button.data('title-start'));
            }
        }
    };

    $(document).on('rex:ready', function ()
    {
        if (!$('.grid-stack').length) {
            return;
        }

        let options = {
            column: 3,
            cellHeight: 50,
            minRow: 1, // don't collapse when empty
            float: true,
            handle: '.grid-stack-item-handle',
            styleInHead: true,
            // resizable: false,
            oneColumnModeDomSort: true,
        };

        grid = GridStack.init(options);
        items = grid.getGridItems();

        $('.grid-stack-inactive>div').each(function()
        {
            items.push($(this)[0]);
        });

        for (let i in items) {
            $items[i] = $(items[i]);
        }

        grid.on('change', function (event, items)
        {
            if (widgetCompactTimeoutHandler) {
                clearTimeout(widgetCompactTimeoutHandler);
            }

            widgetCompactTimeoutHandler = setTimeout(function()
            {
                grid.commit();
                dashboard.resize();
                dashboard.compact();
                dashboard.store();
            }, 500);
        });

        // Pause auto-refresh during user interactions
        $('.grid-stack').on('mousedown', function() {
            if (autoRefreshInterval && !autoRefreshPaused) {
                dashboard.pauseAutoRefresh();
                // Resume after 30 seconds of no interaction
                setTimeout(function() {
                    if (autoRefreshInterval && autoRefreshPaused) {
                        dashboard.resumeAutoRefresh();
                    }
                }, 30000);
            }
        });

        $('#rex-dashboard-compact').on('click', function(e) {
            e.preventDefault();
            dashboard.compact();
        });

        $('#rex-dashboard-autosize').on('click', function(e)
        {
            e.preventDefault();

            dashboard.resize();
        });

        $(window).on('resize', function()
        {
            setTimeout(dashboard.resize, 100);
            setTimeout(dashboard.compact, 150);
        });

        $('#widget-select').on('change', function()
        {
            let selectItems = $(this).val();

            grid.batchUpdate();
            for (let i in $items) {
                if (selectItems.indexOf($items[i].data('id')) >= 0) {
                    if (!$items[i].data('active')) {
                        grid.addWidget(items[i]);
                        $items[i].data('active', 1);
                        $items[i].appendTo($('.grid-stack'));
                    }
                }
                else {
                    if ($items[i].data('active')) {
                        grid.removeWidget(items[i], false);
                        $items[i].data('active', 0);
                        $items[i].appendTo($('.grid-stack-inactive'));
                    }
                }
            }

            if (widgetCompactTimeoutHandler) {
                clearTimeout(widgetCompactTimeoutHandler);
            }

            widgetCompactTimeoutHandler = setTimeout(function()
            {
                grid.commit();
                dashboard.resize();
                dashboard.compact();
                dashboard.store();
            }, 500);
        });

        $('.grid-stack')
        .on('click', '.grid-stack-item-hide', function()
        {
            $('#widget-select option[value="' + $(this).closest('.grid-stack-item').data('id') + '"]').prop('selected', false);
            $('#widget-select').change();
        })
        .on('click', '.grid-stack-item-refresh', function()
        {
            let $parent = $(this).closest('.grid-stack-item');
            dashboard.refreshSingleWidget($parent);
        });

        $('#rex-dashboard-settings').on('click', '#rex-dashboard-refresh', function()
        {
            dashboard.refreshAllWidgets();
        });

        $('#rex-dashboard-settings').on('click', '#rex-dashboard-auto-refresh', function(e)
        {
            e.preventDefault();
            
            if (autoRefreshInterval && !autoRefreshPaused) {
                dashboard.pauseAutoRefresh();
            } else {
                if (autoRefreshInterval) {
                    dashboard.resumeAutoRefresh();
                } else {
                    dashboard.startAutoRefresh();
                }
            }
        });

        // Start auto-refresh by default
        dashboard.startAutoRefresh();
        
        // Auto-refresh when dashboard loads (500ms delay for better UX)
        setTimeout(function() {
            dashboard.refreshAllWidgets();
        }, 500);
    });
    
    // Dashboard 2.0 Refresh-Effekt - Langsamer Glow-Schatten
    let refreshStyles = `
        <style id="dashboard-refresh-styles">
        @keyframes dashboardGlowSlow {
            0% { 
                box-shadow: 0 0 5px rgba(51, 122, 183, 0.3);
            }
            50% {
                box-shadow: 0 0 20px rgba(51, 122, 183, 0.8), 0 0 30px rgba(51, 122, 183, 0.5);
            }
            100% {
                box-shadow: 0 0 5px rgba(51, 122, 183, 0.3);
            }
        }
        
        .grid-stack-item.refreshing-active {
            border: 1px solid #337ab7 !important;
            animation: dashboardGlowSlow 4s ease-in-out infinite !important;
            position: relative;
            z-index: 100;
        }
        
        .grid-stack-item.refreshing-active .panel {
            box-shadow: 0 0 15px rgba(51, 122, 183, 0.4) !important;
            transition: all 0.5s ease !important;
        }
        
        .grid-stack-item.loading .panel-body {
            position: relative;
            opacity: 0.7 !important;
        }
        
        .grid-stack-item.loading .panel-body::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.6);
            z-index: 10;
        }
        
        .grid-stack-item.loading .panel-body::before {
            content: '\\f021';
            font-family: 'FontAwesome';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 20px;
            animation: fa-spin 2s infinite linear;
            z-index: 11;
            color: #337ab7;
        }
        </style>
    `;
    
    // Add styles to head
    if (!$('#dashboard-refresh-styles').length) {
        $('head').append(refreshStyles);
    }
    
}(jQuery));
