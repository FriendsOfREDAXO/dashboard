<?php

namespace FriendsOfRedaxo\Dashboard\Items;

use FriendsOfRedaxo\Dashboard\Base\Item;
use rex_i18n;

/**
 * Demo Dashboard Item: Countdown zum nächsten Jahr.
 */
class CountdownDemo extends Item
{
    public function getTitle(): string
    {
        return rex_i18n::msg('dashboard_countdown_demo_title', 'Countdown Neujahr 2026');
    }

    public function getData()
    {
        $currentYear = date('Y');
        $nextYear = $currentYear + 1;
        $targetDate = mktime(0, 0, 0, 1, 1, $nextYear); // 1. Januar des nächsten Jahres

        $output = '<div class="countdown-demo-compact">';

        // Kompakter Header
        $output .= '<div class="text-center mb-3">';
        $output .= '<h6 class="text-primary mb-1">';
        $output .= '<i class="fa fa-rocket"></i> Neujahr ' . $nextYear;
        $output .= '</h6>';
        $output .= '</div>';

        // Kompakter Countdown in 2x2 Grid
        $output .= '<div id="countdown-' . uniqid() . '" class="countdown-compact">';
        $output .= '<div class="countdown-grid">';

        // Tage (oben links)
        $output .= '<div class="countdown-item">';
        $output .= '<div class="countdown-box bg-primary">';
        $output .= '<div class="countdown-value" id="days-display">0</div>';
        $output .= '<div class="countdown-unit">Tage</div>';
        $output .= '</div>';
        $output .= '</div>';

        // Stunden (oben rechts)
        $output .= '<div class="countdown-item">';
        $output .= '<div class="countdown-box bg-success">';
        $output .= '<div class="countdown-value" id="hours-display">0</div>';
        $output .= '<div class="countdown-unit">Std</div>';
        $output .= '</div>';
        $output .= '</div>';

        // Minuten (unten links)
        $output .= '<div class="countdown-item">';
        $output .= '<div class="countdown-box bg-warning">';
        $output .= '<div class="countdown-value" id="minutes-display">0</div>';
        $output .= '<div class="countdown-unit">Min</div>';
        $output .= '</div>';
        $output .= '</div>';

        // Sekunden (unten rechts)
        $output .= '<div class="countdown-item">';
        $output .= '<div class="countdown-box bg-danger">';
        $output .= '<div class="countdown-value" id="seconds-display">0</div>';
        $output .= '<div class="countdown-unit">Sek</div>';
        $output .= '</div>';
        $output .= '</div>';

        $output .= '</div>'; // countdown-grid
        $output .= '</div>'; // countdown-compact

        // Zieldatum für JavaScript
        $output .= '<div class="text-center mt-2">';
        $output .= '<small class="text-muted">';
        $output .= '<i class="fa fa-target"></i> ' . date('d.m.Y', $targetDate);
        $output .= '</small>';
        $output .= '</div>';

        $output .= '</div>'; // countdown-demo-compact

        // JavaScript für Countdown (kompakt)
        $output .= '<script>
        (function() {
            const targetDate = ' . ($targetDate * 1000) . '; // JavaScript verwendet Millisekunden
            
            function updateCompactCountdown() {
                const now = new Date().getTime();
                const distance = targetDate - now;
                
                if (distance > 0) {
                    const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                    
                    // Kompakte Werte einfügen
                    const daysEl = document.getElementById("days-display");
                    const hoursEl = document.getElementById("hours-display");
                    const minutesEl = document.getElementById("minutes-display");
                    const secondsEl = document.getElementById("seconds-display");
                    
                    if (daysEl) daysEl.textContent = days;
                    if (hoursEl) hoursEl.textContent = hours.toString().padStart(2, "0");
                    if (minutesEl) minutesEl.textContent = minutes.toString().padStart(2, "0");
                    if (secondsEl) secondsEl.textContent = seconds.toString().padStart(2, "0");
                    
                } else {
                    // Countdown ist abgelaufen - Feuerwerk!
                    const container = document.querySelector(".countdown-demo-compact");
                    if (container) {
                        container.innerHTML = "<div class=\"text-center p-3\"><div class=\"alert alert-success mb-0\"><i class=\"fa fa-fireworks fa-2x text-warning\"></i><br><strong class=\"text-success\">Frohes Neues Jahr " + ' . $nextYear . ' + "!</strong></div></div>";
                    }
                }
            }
            
            // Countdown sofort und alle Sekunden aktualisieren
            updateCompactCountdown();
            const countdownInterval = setInterval(updateCompactCountdown, 1000);
            
            // Cleanup bei Dashboard-Refresh
            document.addEventListener("dashboard:refresh", function() {
                if (countdownInterval) clearInterval(countdownInterval);
            });
        })();
        </script>';

        // Kompaktes CSS
        $output .= '<style>
        .countdown-demo-compact {
            padding: 0.5rem;
        }
        
        .countdown-compact .countdown-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            grid-template-rows: 1fr 1fr;
            gap: 0.5rem;
            width: 100%;
        }
        
        .countdown-compact .countdown-item {
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .countdown-compact .countdown-box {
            padding: 0.75rem 0.5rem;
            border-radius: 0.5rem;
            text-align: center;
            color: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.2s;
            width: 100%;
            min-height: 60px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .countdown-compact .countdown-box:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        
        .countdown-compact .countdown-value {
            font-size: 1.4rem;
            font-weight: bold;
            line-height: 1;
            margin-bottom: 0.25rem;
        }
        
        .countdown-compact .countdown-unit {
            font-size: 0.75rem;
            opacity: 0.9;
            font-weight: 500;
        }
        
        .countdown-compact .bg-primary { background-color: #007bff !important; }
        .countdown-compact .bg-success { background-color: #28a745 !important; }  
        .countdown-compact .bg-warning { background-color: #ffc107 !important; color: #212529 !important; }
        .countdown-compact .bg-danger { background-color: #dc3545 !important; }
        
        @media (max-width: 768px) {
            .countdown-compact .countdown-value { font-size: 1.2rem; }
            .countdown-compact .countdown-unit { font-size: 0.7rem; }
            .countdown-compact .countdown-box { min-height: 50px; padding: 0.5rem 0.25rem; }
        }
        </style>';

        return $output;
    }
}
