<?php

namespace FriendsOfRedaxo\Dashboard\Items;

use FriendsOfRedaxo\Dashboard\Base\Item;
use rex_i18n;

/**
 * Dashboard Item: Big Number Demo.
 */
class BigNumberDemo extends Item
{
    public function getTitle(): string
    {
        return rex_i18n::msg('dashboard_big_number_demo_title', 'Follower Count');
    }

    public function getData()
    {
        // Demo-Zahl (42 Follower)
        $bigNumber = 42;
        $label = 'Followers';
        $trend = '+15%'; // Trend-Indikator

        $output = '<div class="big-number-widget">';

        // Haupt-Zahl (responsive skalierend)
        $output .= '<div class="big-number-display">';
        $output .= '<div class="big-number-value">' . number_format($bigNumber, 0, ',', '.') . '</div>';
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

        // Responsive CSS - angepasst für Dashboard-Umgebung
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
            font-size: clamp(3rem, 20vw, 6rem);
            font-weight: 900;
            line-height: 0.8;
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
        
        /* Widget-spezifische Anpassungen */
        
        /* Kleine Widgets (1 Spalte) */
        .grid-stack-item[gs-w="1"] .big-number-value {
            font-size: clamp(2.5rem, 15vw, 4rem);
        }
        
        .grid-stack-item[gs-w="1"] .big-number-widget {
            min-height: 120px;
            padding: 1rem;
        }
        
        .grid-stack-item[gs-w="1"] .big-number-info {
            flex-direction: column;
            gap: 0.25rem;
        }
        
        .grid-stack-item[gs-w="1"] .big-number-label {
            font-size: 0.8rem;
        }
        
        .grid-stack-item[gs-w="1"] .big-number-trend {
            font-size: 0.75rem;
            padding: 0.2rem 0.4rem;
        }
        
        /* Normale Widgets (2 Spalten) */
        .grid-stack-item[gs-w="2"] .big-number-value {
            font-size: clamp(4rem, 18vw, 7rem);
        }
        
        /* Große Widgets (3 Spalten) */
        .grid-stack-item[gs-w="3"] .big-number-value {
            font-size: clamp(5rem, 22vw, 9rem);
        }
        
        .grid-stack-item[gs-w="3"] .big-number-widget {
            min-height: 160px;
            padding: 2rem;
        }
        
        /* Mobile Optimierung */
        @media (max-width: 768px) {
            .big-number-value {
                font-size: clamp(2.5rem, 20vw, 4.5rem) !important;
            }
            
            .big-number-widget {
                min-height: 100px !important;
                padding: 1rem !important;
            }
            
            .big-number-label,
            .big-number-trend {
                font-size: 0.75rem !important;
            }
            
            .big-number-info {
                flex-direction: column !important;
                gap: 0.25rem !important;
            }
        }
        
        /* Pulse-Animation bei Updates */
        @keyframes bigNumberPulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.08); }
            100% { transform: scale(1); }
        }
        
        .big-number-value.updating {
            animation: bigNumberPulse 0.6s ease-in-out;
        }
        </style>';

        return $output;
    }
}
