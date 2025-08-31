<?php

namespace FriendsOfREDAXO\Dashboard;

use rex_dashboard_item;
use rex_i18n;
use rex;
use rex_escape;
use rex_sql;
use Exception;

/**
 * Dashboard Item: Schnellnotizen
 * Persönliche Notizen für jeden Benutzer
 */
class DashboardItemQuickNotes extends rex_dashboard_item
{
    public function getTitle(): string
    {
        return rex_i18n::msg('dashboard_quick_notes_title', 'Schnellnotizen');
    }

    public function isAvailable(): bool
    {
        // Für alle Backend-Benutzer verfügbar
        return rex::getUser() !== null;
    }

    public function getData()
    {
        $user = rex::getUser();
        if (!$user) {
            return '<p class="text-muted">' . rex_i18n::msg('dashboard_no_permission', 'Keine Berechtigung.') . '</p>';
        }

        // Persönliche Notizen des Benutzers laden
        $userId = $user->getId();
        
        try {
            $notes = DashboardNotes::getNotes($userId);
        } catch (Exception $e) {
            // Fallback: Direkte Datenbankabfrage
            $notes = '';
            try {
                $sql = rex_sql::factory();
                $tableName = rex::getTable('dashboard_notes');
                $sql->setQuery("SHOW TABLES LIKE '$tableName'");
                if ($sql->getRows() > 0) {
                    $sql->setQuery('SELECT notes FROM ' . $tableName . ' WHERE user_id = ?', [$userId]);
                    if ($sql->getRows() > 0) {
                        $notes = $sql->getValue('notes') ?? '';
                    }
                }
            } catch (Exception $e2) {
                // Ignore
            }
        }

        $content = '<div class="quick-notes-widget">';
        
        // Textarea für Notizen
        $content .= '<div class="form-group">';
        $content .= '<textarea id="quick-notes-text" class="form-control" rows="8" placeholder="' . 
                    rex_escape(rex_i18n::msg('dashboard_quick_notes_placeholder', 'Hier können Sie Ihre persönlichen Notizen und Ideen festhalten...')) . 
                    '">' . rex_escape($notes) . '</textarea>';
        $content .= '</div>';
        
        // Buttons
        $content .= '<div class="form-group text-right">';
        $content .= '<button type="button" id="quick-notes-save" class="btn btn-primary btn-sm">';
        $content .= '<i class="fa fa-save"></i> ' . rex_i18n::msg('dashboard_quick_notes_save', 'Speichern');
        $content .= '</button>';
        $content .= ' <button type="button" id="quick-notes-clear" class="btn btn-default btn-sm">';
        $content .= '<i class="fa fa-trash"></i> ' . rex_i18n::msg('dashboard_quick_notes_clear', 'Leeren');
        $content .= '</button>';
        $content .= '</div>';
        
        // Status-Bereich
        $content .= '<div id="quick-notes-status" class="alert" style="display: none; margin-top: 10px;"></div>';
        
        $content .= '</div>';
        
        // JavaScript für Ajax-Funktionalität
        $content .= $this->getJavaScript();
        
        return $content;
    }
    
    private function getJavaScript()
    {
        return '
        <script>
        (function($) {
            $(document).ready(function() {
                var $textarea = $("#quick-notes-text");
                var $saveBtn = $("#quick-notes-save");
                var $clearBtn = $("#quick-notes-clear");
                var $status = $("#quick-notes-status");
                var saveTimeout = null;
                var hasUnsavedChanges = false;
                
                // Auto-Save nach 2 Sekunden ohne Eingabe
                $textarea.on("input", function() {
                    hasUnsavedChanges = true;
                    $saveBtn.prop("disabled", false).removeClass("btn-success").addClass("btn-primary");
                    
                    if (saveTimeout) {
                        clearTimeout(saveTimeout);
                    }
                    
                    saveTimeout = setTimeout(function() {
                        saveNotes(true); // Auto-save
                    }, 2000);
                });
                
                // Manuelles Speichern
                $saveBtn.on("click", function() {
                    if (saveTimeout) {
                        clearTimeout(saveTimeout);
                    }
                    saveNotes(false); // Manual save
                });
                
                // Notizen leeren
                $clearBtn.on("click", function() {
                    if (confirm("' . rex_i18n::msg('dashboard_quick_notes_confirm_clear', 'Möchten Sie wirklich alle Notizen löschen?') . '")) {
                        $textarea.val("");
                        saveNotes(false);
                    }
                });
                
                // Ajax-Speicherfunktion
                function saveNotes(isAutoSave) {
                    var notes = $textarea.val();
                    
                    $.ajax({
                        url: "index.php",
                        type: "POST",
                        data: {
                            "rex-api-call": "dashboard_save_quick_notes",
                            "notes": notes
                        },
                        beforeSend: function() {
                            if (!isAutoSave) {
                                $saveBtn.prop("disabled", true).html("<i class=\"fa fa-spinner fa-spin\"></i> ' . rex_i18n::msg('dashboard_saving', 'Speichern...') . '");
                            }
                        },
                        success: function(response) {
                            hasUnsavedChanges = false;
                            
                            // Response könnte String oder Object sein
                            var data = response;
                            if (typeof response === "string") {
                                try {
                                    data = JSON.parse(response);
                                } catch(e) {
                                    data = {success: false, message: "Invalid response format"};
                                }
                            }
                            
                            if (data.success) {
                                if (!isAutoSave) {
                                    $saveBtn.html("<i class=\"fa fa-check\"></i> ' . rex_i18n::msg('dashboard_saved', 'Gespeichert') . '")
                                            .removeClass("btn-primary").addClass("btn-success")
                                            .prop("disabled", false);
                                    
                                    $status.removeClass("alert-danger").addClass("alert-success")
                                           .html("' . rex_i18n::msg('dashboard_quick_notes_saved', 'Notizen erfolgreich gespeichert!') . '")
                                           .fadeIn().delay(2000).fadeOut();
                                    
                                    // Button nach 3 Sekunden zurücksetzen
                                    setTimeout(function() {
                                        $saveBtn.html("<i class=\"fa fa-save\"></i> ' . rex_i18n::msg('dashboard_quick_notes_save', 'Speichern') . '")
                                                .removeClass("btn-success").addClass("btn-primary");
                                    }, 3000);
                                }
                            } else {
                                // Server returned success=false
                                if (!isAutoSave) {
                                    $saveBtn.html("<i class=\"fa fa-save\"></i> ' . rex_i18n::msg('dashboard_quick_notes_save', 'Speichern') . '")
                                            .prop("disabled", false);
                                    
                                    $status.removeClass("alert-success").addClass("alert-danger")
                                           .html("' . rex_i18n::msg('dashboard_quick_notes_error', 'Fehler beim Speichern der Notizen!') . '" + (data.message ? ": " + data.message : ""))
                                           .fadeIn();
                                }
                            }
                        },
                        error: function(xhr, status, error) {
                            console.log("Dashboard Notes Ajax Error:", {xhr: xhr, status: status, error: error});
                            
                            if (!isAutoSave) {
                                $saveBtn.html("<i class=\"fa fa-save\"></i> ' . rex_i18n::msg('dashboard_quick_notes_save', 'Speichern') . '")
                                        .prop("disabled", false);
                                
                                var errorMsg = "' . rex_i18n::msg('dashboard_quick_notes_error', 'Fehler beim Speichern der Notizen!') . '";
                                if (xhr.responseText) {
                                    try {
                                        var responseData = JSON.parse(xhr.responseText);
                                        if (responseData.message) {
                                            errorMsg += ": " + responseData.message;
                                        }
                                    } catch(e) {
                                        errorMsg += " (HTTP " + xhr.status + ")";
                                    }
                                } else {
                                    errorMsg += " (HTTP " + xhr.status + ")";
                                }
                                
                                $status.removeClass("alert-success").addClass("alert-danger")
                                       .html(errorMsg)
                                       .fadeIn();
                            }
                        }
                    });
                }
                
                // Warnung bei ungespeicherten Änderungen
                $(window).on("beforeunload", function() {
                    if (hasUnsavedChanges) {
                        return "' . rex_i18n::msg('dashboard_quick_notes_unsaved', 'Sie haben ungespeicherte Änderungen in Ihren Notizen.') . '";
                    }
                });
            });
        })(jQuery);
        </script>';
    }
}
