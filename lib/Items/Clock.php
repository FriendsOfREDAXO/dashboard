<?php

namespace FriendsOfRedaxo\Dashboard\Items;

use FriendsOfRedaxo\Dashboard\Base\Item;
use rex_i18n;

/**
 * Dashboard Item: Uhr
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
        $serverTime = new \DateTime();
        
        $content = '<div class="apple-watch-clock-widget text-center">';
        
        // Apple Watch Style Analog Clock Container
        $content .= '<div id="appleWatchClock" style="margin: 20px auto; position: relative; width: 250px; height: 250px;"></div>';
        
        // Digital Time Display (Apple Watch Style)
        $content .= '<div style="margin-top: 20px;">';
        $content .= '<h2 id="digitalTime" style="margin: 0; font-family: -apple-system, BlinkMacSystemFont, system-ui, sans-serif; font-weight: 500; color: #1d1d1f; font-size: 28px;"></h2>';
        $content .= '<p id="digitalDate" style="margin: 8px 0 0 0; font-size: 16px; color: #86868b; font-family: -apple-system, BlinkMacSystemFont, system-ui, sans-serif;"></p>';
        $content .= '<small style="color: #86868b; font-family: -apple-system, BlinkMacSystemFont, system-ui, sans-serif;">Zeitzone: ' . $timezone . '</small>';
        $content .= '</div>';
        
        $content .= '</div>';
        
        // CSS für die Apple Watch Style Uhr mit Dark Mode Support
        $content .= '<style>
            .apple-watch-clock-face {
                position: relative;
                width: 250px;
                height: 250px;
                border-radius: 50%;
                background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 50%, #f8f9fa 100%);
                box-shadow: 
                    0 0 0 6px rgba(0,0,0,0.05),
                    0 0 0 12px rgba(0,0,0,0.1),
                    0 12px 40px rgba(0,0,0,0.15),
                    inset 0 2px 8px rgba(255,255,255,0.8);
                border: 3px solid #dee2e6;
                overflow: hidden;
                transition: all 0.3s ease;
            }
            
            /* Dark Mode Support */
            @media (prefers-color-scheme: dark) {
                .apple-watch-clock-face {
                    background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 50%, #1a1a1a 100%);
                    box-shadow: 
                        0 0 0 6px rgba(255,255,255,0.1),
                        0 0 0 12px rgba(0,0,0,0.8),
                        0 12px 40px rgba(0,0,0,0.4),
                        inset 0 2px 8px rgba(255,255,255,0.1);
                    border: 3px solid #333333;
                }
            }
            
            /* REDAXO Backend Dark Theme Detection */
            body.rex-theme-dark .apple-watch-clock-face,
            .rex-theme-dark .apple-watch-clock-face {
                background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 50%, #1a1a1a 100%);
                box-shadow: 
                    0 0 0 6px rgba(255,255,255,0.1),
                    0 0 0 12px rgba(0,0,0,0.8),
                    0 12px 40px rgba(0,0,0,0.4),
                    inset 0 2px 8px rgba(255,255,255,0.1);
                border: 3px solid #333333;
            }
            
            .apple-watch-clock-face::before {
                content: "";
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                border-radius: 50%;
                background: radial-gradient(circle at 30% 20%, rgba(255,255,255,0.4) 0%, transparent 50%);
                pointer-events: none;
            }
            
            .clock-center {
                position: absolute;
                width: 12px;
                height: 12px;
                background: #1d1d1f;
                border-radius: 50%;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                z-index: 20;
                box-shadow: 0 2px 8px rgba(0,0,0,0.3);
            }
            
            .clock-hand {
                position: absolute;
                transform-origin: bottom center;
                border-radius: 3px;
                transition: transform 0.3s cubic-bezier(0.4, 0.0, 0.2, 1);
            }
            
            .hour-hand {
                width: 6px;
                height: 70px;
                left: 50%;
                bottom: 50%;
                margin-left: -3px;
                background: linear-gradient(to top, #1d1d1f 0%, #4a4a4a 100%);
                box-shadow: 0 2px 6px rgba(0,0,0,0.3);
                z-index: 15;
                transition: background 0.3s ease, box-shadow 0.3s ease;
            }
            
            .minute-hand {
                width: 4px;
                height: 95px;
                left: 50%;
                bottom: 50%;
                margin-left: -2px;
                background: linear-gradient(to top, #1d1d1f 0%, #4a4a4a 100%);
                box-shadow: 0 2px 6px rgba(0,0,0,0.3);
                z-index: 16;
                transition: background 0.3s ease, box-shadow 0.3s ease;
            }
            
            /* Dark Mode für Zeiger */
            @media (prefers-color-scheme: dark) {
                .hour-hand, .minute-hand {
                    background: linear-gradient(to top, #ffffff 0%, #cccccc 100%);
                    box-shadow: 0 2px 6px rgba(255,255,255,0.2);
                }
            }
            
            /* REDAXO Backend Dark Theme für Zeiger */
            body.rex-theme-dark .hour-hand,
            body.rex-theme-dark .minute-hand,
            .rex-theme-dark .hour-hand,
            .rex-theme-dark .minute-hand {
                background: linear-gradient(to top, #ffffff 0%, #cccccc 100%);
                box-shadow: 0 2px 6px rgba(255,255,255,0.2);
            }
            
            .second-hand {
                width: 2px;
                height: 110px;
                left: 50%;
                bottom: 50%;
                margin-left: -1px;
                background: linear-gradient(to top, #ff3b30 0%, #ff6b60 100%);
                z-index: 18;
                box-shadow: 0 2px 6px rgba(255,59,48,0.4);
                animation: smooth-tick 1s infinite;
            }
            
            @keyframes smooth-tick {
                0%, 100% { transform: rotate(var(--second-angle, 0deg)); }
                50% { transform: rotate(var(--second-angle, 0deg)) scale(1.02); }
            }
            
            .clock-number {
                position: absolute;
                font-weight: 600;
                font-size: 18px;
                text-align: center;
                width: 24px;
                height: 24px;
                line-height: 24px;
                color: #1d1d1f;
                font-family: -apple-system, BlinkMacSystemFont, system-ui, sans-serif;
                text-shadow: 0 1px 2px rgba(255,255,255,0.8);
                transition: color 0.3s ease, text-shadow 0.3s ease;
            }
            
            /* Dark Mode für Ziffern */
            @media (prefers-color-scheme: dark) {
                .clock-number {
                    color: #ffffff;
                    text-shadow: 0 2px 4px rgba(0,0,0,0.8);
                }
            }
            
            /* REDAXO Backend Dark Theme für Ziffern */
            body.rex-theme-dark .clock-number,
            .rex-theme-dark .clock-number {
                color: #ffffff;
                text-shadow: 0 2px 4px rgba(0,0,0,0.8);
            }
            
            .clock-tick {
                position: absolute;
                background: #86868b;
                transform-origin: bottom center;
                transition: background 0.3s ease;
            }
            
            .clock-tick-major {
                width: 3px;
                height: 20px;
                background: #1d1d1f;
                transition: background 0.3s ease;
            }
            
            .clock-tick-minor {
                width: 1px;
                height: 10px;
                background: #86868b;
                transition: background 0.3s ease;
            }
            
            /* Dark Mode für Markierungen */
            @media (prefers-color-scheme: dark) {
                .clock-tick {
                    background: #999999;
                }
                .clock-tick-major {
                    background: #ffffff;
                }
                .clock-tick-minor {
                    background: #999999;
                }
            }
            
            /* REDAXO Backend Dark Theme für Markierungen */
            body.rex-theme-dark .clock-tick,
            .rex-theme-dark .clock-tick {
                background: #999999;
            }
            body.rex-theme-dark .clock-tick-major,
            .rex-theme-dark .clock-tick-major {
                background: #ffffff;
            }
            body.rex-theme-dark .clock-tick-minor,
            .rex-theme-dark .clock-tick-minor {
                background: #999999;
            }
            
            .apple-watch-clock-widget {
                padding: 20px;
                background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
                border-radius: 16px;
                color: #212529;
                box-shadow: 0 8px 32px rgba(0,0,0,0.08);
                border: 1px solid rgba(0,0,0,0.1);
                transition: all 0.3s ease;
            }
            
            /* Dark Mode für Widget-Hintergrund */
            @media (prefers-color-scheme: dark) {
                .apple-watch-clock-widget {
                    background: linear-gradient(135deg, #2d3748 0%, #1a202c 100%);
                    color: #f7fafc;
                    box-shadow: 0 8px 32px rgba(0,0,0,0.3);
                    border: 1px solid rgba(255,255,255,0.1);
                }
            }
            
            /* REDAXO Backend Dark Theme für Widget-Hintergrund */
            body.rex-theme-dark .apple-watch-clock-widget,
            .rex-theme-dark .apple-watch-clock-widget {
                background: linear-gradient(135deg, #2d3748 0%, #1a202c 100%);
                color: #f7fafc;
                box-shadow: 0 8px 32px rgba(0,0,0,0.3);
                border: 1px solid rgba(255,255,255,0.1);
            }
        </style>';
        
        // JavaScript für die Apple Watch Style Uhr
        $content .= '<script>
            function updateAppleWatchClock() {
                const now = new Date();
                
                // Digital time mit Apple-Style Formatierung
                const timeStr = now.toLocaleTimeString("de-DE", {
                    hour: "2-digit",
                    minute: "2-digit",
                    second: "2-digit"
                });
                const dateStr = now.toLocaleDateString("de-DE", {
                    weekday: "long",
                    year: "numeric", 
                    month: "long",
                    day: "numeric"
                });
                
                document.getElementById("digitalTime").textContent = timeStr;
                document.getElementById("digitalDate").textContent = dateStr;
                
                // Analog clock
                updateAppleWatchAnalogClock(now);
            }
            
            function updateAppleWatchAnalogClock(now) {
                const clockContainer = document.getElementById("appleWatchClock");
                
                // Clock face erstellen (nur beim ersten Mal)
                if (!clockContainer.querySelector(".apple-watch-clock-face")) {
                    createAppleWatchClockFace(clockContainer);
                }
                
                const hours = now.getHours() % 12;
                const minutes = now.getMinutes();
                const seconds = now.getSeconds();
                const milliseconds = now.getMilliseconds();
                
                // Smooth second hand movement
                const smoothSeconds = seconds + (milliseconds / 1000);
                
                // Zeiger-Winkel berechnen (0 Grad = 12 Uhr)
                const hourAngle = (hours * 30) + (minutes * 0.5) + (seconds * 0.0083);
                const minuteAngle = (minutes * 6) + (seconds * 0.1);
                const secondAngle = smoothSeconds * 6;
                
                // Zeiger aktualisieren mit smooth transitions
                const hourHand = clockContainer.querySelector(".hour-hand");
                const minuteHand = clockContainer.querySelector(".minute-hand");
                const secondHand = clockContainer.querySelector(".second-hand");
                
                if (hourHand) hourHand.style.transform = `rotate(${hourAngle}deg)`;
                if (minuteHand) minuteHand.style.transform = `rotate(${minuteAngle}deg)`;
                if (secondHand) {
                    secondHand.style.setProperty("--second-angle", `${secondAngle}deg`);
                    secondHand.style.transform = `rotate(${secondAngle}deg)`;
                }
            }
            
            function createAppleWatchClockFace(container) {
                const face = document.createElement("div");
                face.className = "apple-watch-clock-face";
                
                // Stundenmarkierungen (Apple Watch Style)
                for (let i = 0; i < 60; i++) {
                    const tick = document.createElement("div");
                    const isMajor = i % 5 === 0;
                    tick.className = `clock-tick ${isMajor ? "clock-tick-major" : "clock-tick-minor"}`;
                    
                    const angle = i * 6; // 6 Grad pro Minute
                    const radius = isMajor ? 105 : 110;
                    const x = Math.cos((angle - 90) * Math.PI / 180) * radius;
                    const y = Math.sin((angle - 90) * Math.PI / 180) * radius;
                    
                    tick.style.left = `calc(50% + ${x}px - ${isMajor ? 1.5 : 0.5}px)`;
                    tick.style.top = `calc(50% + ${y}px - ${isMajor ? 20 : 10}px)`;
                    tick.style.transform = `rotate(${angle}deg)`;
                    
                    face.appendChild(tick);
                }
                
                // Zahlen nur für 12, 3, 6, 9 (Apple Watch Style)
                [12, 3, 6, 9].forEach(num => {
                    const number = document.createElement("div");
                    number.className = "clock-number";
                    number.textContent = num;
                    
                    const angle = (num === 12 ? 0 : num * 30) - 90;
                    const x = Math.cos(angle * Math.PI / 180) * 85;
                    const y = Math.sin(angle * Math.PI / 180) * 85;
                    
                    number.style.left = `calc(50% + ${x}px - 12px)`;
                    number.style.top = `calc(50% + ${y}px - 12px)`;
                    
                    face.appendChild(number);
                });
                
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
                
                // Mittelpunkt (Apple Watch Style)
                const center = document.createElement("div");
                center.className = "clock-center";
                face.appendChild(center);
                
                container.appendChild(face);
            }
            
            // Initial update und dann alle 100ms für smooth second hand
            updateAppleWatchClock();
            setInterval(updateAppleWatchClock, 100);
        </script>';
        
        return $content;
    }
}
