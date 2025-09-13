<?php

namespace FriendsOfRedaxo\Dashboard\Items;

use DateTime;
use FriendsOfRedaxo\Dashboard\Base\Item;
use rex_i18n;

/**
 * Dashboard Item: Uhr.
 */
class Clock extends Item
{
    public function getTitle(): string
    {
        return rex_i18n::msg('dashboard_clock_title', 'Uhr');
    }

    public function getData()
    {
        // Timezone Info
        $timezone = date_default_timezone_get();
        $serverTime = new DateTime();

        $content = '<div class="clock-widget text-center">';

        // Analog Clock Container
        $content .= '<div id="analogClock" style="margin: 20px auto; position: relative; width: 200px; height: 200px;"></div>';

        // Digital Time Display
        $content .= '<div style="margin-top: 20px;">';
        $content .= '<h2 id="digitalTime" class="text-primary" style="margin: 0; font-family: monospace; font-weight: bold;"></h2>';
        $content .= '<p id="digitalDate" class="text-muted" style="margin: 5px 0 0 0; font-size: 16px;"></p>';
        $content .= '<small class="text-muted">Zeitzone: ' . $timezone . '</small>';
        $content .= '</div>';

        $content .= '</div>';

        // CSS für die analoge Uhr
        $content .= '<style>
            .clock-face {
                position: relative;
                width: 200px;
                height: 200px;
                border: 3px solid #333;
                border-radius: 50%;
                background: white;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            }
            
            .clock-center {
                position: absolute;
                width: 10px;
                height: 10px;
                background: #333;
                border-radius: 50%;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                z-index: 10;
            }
            
            .clock-hand {
                position: absolute;
                background: #333;
                transform-origin: bottom center;
                border-radius: 2px;
            }
            
            .hour-hand {
                width: 4px;
                height: 60px;
                left: 50%;
                bottom: 50%;
                margin-left: -2px;
            }
            
            .minute-hand {
                width: 3px;
                height: 80px;
                left: 50%;
                bottom: 50%;
                margin-left: -1.5px;
            }
            
            .second-hand {
                width: 1px;
                height: 90px;
                left: 50%;
                bottom: 50%;
                margin-left: -0.5px;
                background: #e74c3c;
            }
            
            .clock-number {
                position: absolute;
                font-weight: bold;
                font-size: 16px;
                text-align: center;
                width: 20px;
                height: 20px;
                line-height: 20px;
            }
        </style>';

        // JavaScript für die Uhr
        $content .= '<script>
            function updateClock() {
                const now = new Date();
                
                // Digital time
                const timeStr = now.toLocaleTimeString("de-DE");
                const dateStr = now.toLocaleDateString("de-DE", {
                    weekday: "long",
                    year: "numeric", 
                    month: "long",
                    day: "numeric"
                });
                
                document.getElementById("digitalTime").textContent = timeStr;
                document.getElementById("digitalDate").textContent = dateStr;
                
                // Analog clock
                updateAnalogClock(now);
            }
            
            function updateAnalogClock(now) {
                const clockContainer = document.getElementById("analogClock");
                
                // Clock face erstellen (nur beim ersten Mal)
                if (!clockContainer.querySelector(".clock-face")) {
                    createClockFace(clockContainer);
                }
                
                const hours = now.getHours() % 12;
                const minutes = now.getMinutes();
                const seconds = now.getSeconds();
                
                // Zeiger-Winkel berechnen (0 Grad = 12 Uhr)
                const hourAngle = (hours * 30) + (minutes * 0.5);
                const minuteAngle = minutes * 6;
                const secondAngle = seconds * 6;
                
                // Zeiger aktualisieren
                const hourHand = clockContainer.querySelector(".hour-hand");
                const minuteHand = clockContainer.querySelector(".minute-hand");
                const secondHand = clockContainer.querySelector(".second-hand");
                
                if (hourHand) hourHand.style.transform = `rotate(${hourAngle}deg)`;
                if (minuteHand) minuteHand.style.transform = `rotate(${minuteAngle}deg)`;
                if (secondHand) secondHand.style.transform = `rotate(${secondAngle}deg)`;
            }
            
            function createClockFace(container) {
                const face = document.createElement("div");
                face.className = "clock-face";
                
                // Zahlen hinzufügen
                for (let i = 1; i <= 12; i++) {
                    const number = document.createElement("div");
                    number.className = "clock-number";
                    number.textContent = i;
                    
                    const angle = (i * 30) - 90; // -90 um bei 12 zu starten
                    const x = Math.cos(angle * Math.PI / 180) * 80;
                    const y = Math.sin(angle * Math.PI / 180) * 80;
                    
                    number.style.left = `calc(50% + ${x}px - 10px)`;
                    number.style.top = `calc(50% + ${y}px - 10px)`;
                    
                    face.appendChild(number);
                }
                
                // Zeiger hinzufügen
                const hourHand = document.createElement("div");
                hourHand.className = "clock-hand hour-hand";
                face.appendChild(hourHand);
                
                const minuteHand = document.createElement("div");
                minuteHand.className = "clock-hand minute-hand";
                face.appendChild(minuteHand);
                
                const secondHand = document.createElement("div");
                secondHand.className = "clock-hand second-hand";
                face.appendChild(secondHand);
                
                // Mittelpunkt
                const center = document.createElement("div");
                center.className = "clock-center";
                face.appendChild(center);
                
                container.appendChild(face);
            }
            
            // Initial update und dann jede Sekunde
            updateClock();
            setInterval(updateClock, 1000);
        </script>';

        return $content;
    }
}
