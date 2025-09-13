<?php

namespace FriendsOfRedaxo\Dashboard\DemoItems;

use FriendsOfRedaxo\Dashboard\Base\Item;
use rex_i18n;

/**
 * Demo Dashboard Item: Big Number Widget.
 */
class BigNumber extends Item
{
    public function getTitle(): string
    {
        return rex_i18n::msg('dashboard_demo_big_number_title', 'Follower Demo');
    }

    public function getData()
    {
        // Demo-Zahl (könnte aus API, Datenbank etc. kommen)
        $bigNumber = 42;
        $label = 'Follower';
        $trend = '+15%'; // Optional: Trend-Indikator

        $output = '<div class="big-number-widget">';

        // Haupt-Zahl (responsive skalierend)
        $output .= '<div class="big-number-display">';
        $output .= '<div class="big-number-value" id="big-number-' . uniqid() . '">' . number_format($bigNumber, 0, ',', '.') . '</div>';
        $output .= '</div>';

        // Label und Trend
        $output .= '<div class="big-number-info">';
        $output .= '<div class="big-number-label">';
        $output .= '<i class="fa fa-users"></i> ' . $label;
        $output .= '</div>';

        if ($trend) {
            $trendClass = str_starts_with($trend, '+') ? 'trend-up' : 'trend-down';
            $trendIcon = str_starts_with($trend, '+') ? 'fa-arrow-up' : 'fa-arrow-down';
            $output .= '<div class="big-number-trend ' . $trendClass . '">';
            $output .= '<i class="fa ' . $trendIcon . '"></i> ' . $trend;
            $output .= '</div>';
        }

        $output .= '</div>'; // big-number-info
        $output .= '</div>'; // big-number-widget

        // Responsive CSS
        $output .= '<style>
        .big-number-widget {
            padding: 1rem;
            text-align: center;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            min-height: 120px;
        }
        
        .big-number-display {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 0.75rem;
        }
        
        .big-number-value {
            font-size: clamp(2rem, 8vw, 4rem);
            font-weight: 900;
            line-height: 1;
            color: #007bff;
            text-shadow: 0 2px 4px rgba(0,123,255,0.2);
            transition: all 0.3s ease;
        }
        
        .big-number-value:hover {
            transform: scale(1.05);
            color: #0056b3;
        }
        
        .big-number-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        
        .big-number-label {
            font-size: 0.9rem;
            color: #6c757d;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .big-number-trend {
            font-size: 0.85rem;
            font-weight: 600;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }
        
        .big-number-trend.trend-up {
            color: #28a745;
            background-color: rgba(40, 167, 69, 0.1);
        }
        
        .big-number-trend.trend-down {
            color: #dc3545;
            background-color: rgba(220, 53, 69, 0.1);
        }
        
        /* Responsive Anpassungen für verschiedene Widget-Größen */
        
        /* 1 Spalte (klein) */
        .grid-stack-item[gs-w="1"] .big-number-value {
            font-size: clamp(1.5rem, 6vw, 2.5rem);
        }
        
        .grid-stack-item[gs-w="1"] .big-number-widget {
            min-height: 100px;
            padding: 0.75rem;
        }
        
        .grid-stack-item[gs-w="1"] .big-number-info {
            flex-direction: column;
            gap: 0.25rem;
        }
        
        /* 2 Spalten (normal) */
        .grid-stack-item[gs-w="2"] .big-number-value {
            font-size: clamp(2.5rem, 7vw, 3.5rem);
        }
        
        /* 3 Spalten (breit) */
        .grid-stack-item[gs-w="3"] .big-number-value {
            font-size: clamp(3rem, 8vw, 5rem);
        }
        
        .grid-stack-item[gs-w="3"] .big-number-widget {
            min-height: 140px;
            padding: 1.5rem;
        }
        
        /* Mobile Optimierung */
        @media (max-width: 768px) {
            .big-number-value {
                font-size: clamp(1.5rem, 10vw, 3rem) !important;
            }
            
            .big-number-widget {
                min-height: 80px !important;
                padding: 0.5rem !important;
            }
            
            .big-number-label,
            .big-number-trend {
                font-size: 0.8rem;
            }
        }
        
        /* Animation für Zahl-Updates */
        @keyframes numberPulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        
        .big-number-value.updating {
            animation: numberPulse 0.6s ease-in-out;
        }
        </style>';

        // Optional: JavaScript für animierte Zahl-Updates
        $output .= '<script>
        (function() {
            // Simuliere gelegentliche Zahl-Updates für Demo-Zwecke
            const numberElement = document.querySelector("#big-number-' . uniqid() . '");
            
            function animateNumber() {
                if (numberElement) {
                    numberElement.classList.add("updating");
                    setTimeout(() => {
                        numberElement.classList.remove("updating");
                    }, 600);
                }
            }
            
            // Optional: Demo-Update alle 30 Sekunden
            // setInterval(animateNumber, 30000);
            
            // Bei Dashboard-Refresh animieren
            document.addEventListener("dashboard:refresh", animateNumber);
        })();
        </script>';

        return $output;
    }
}
