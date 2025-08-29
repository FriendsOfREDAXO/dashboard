<?php

$addon = rex_addon::get('dashboard');

echo rex_view::title($addon->i18n('config_title'));

// Initialisiere Default-Werte falls noch nicht gesetzt
if (null === $addon->getConfig('demo_enabled')) {
    $addon->setConfig('demo_enabled', '0');
}

// Default Widget Defaults initialisieren
if (null === $addon->getConfig('default_widgets_enabled')) {
    $addon->setConfig('default_widgets_enabled', '0');
}

// Zurück zum Dashboard Button (oberhalb des Formulars, rechts ausgerichtet)
echo '<div class="text-right" style="margin-bottom: 20px;">';
echo '<a class="btn btn-abort" href="' . rex_url::backendPage('dashboard') . '">' . rex_i18n::msg('back') . ' zum Dashboard</a>';
echo '</div>';

// Instanzieren des Formulars
$form = rex_config_form::factory('dashboard');

// Füge subpage Parameter hinzu, damit Form-Action korrekt ist
$form->addParam('subpage', 'config');

// Demo aktivieren/deaktivieren - Select
$field = $form->addSelectField('demo_enabled', null, ['class' => 'form-control selectpicker']);
$field->setLabel($addon->i18n('config_demo_enabled'));
$select = $field->getSelect();
$select->addOption($addon->i18n('config_demo_disabled'), '0');
$select->addOption($addon->i18n('config_demo_enabled'), '1');

// Separator
$form->addRawField('<hr><h4>Default Widgets</h4>');

// Default Widgets aktivieren/deaktivieren
$field = $form->addSelectField('default_widgets_enabled', null, ['class' => 'form-control selectpicker']);
$field->setLabel('Default Widgets aktivieren');
$field->setNotice('Aktiviert nützliche Standard-Widgets für REDAXO Core-Funktionen');
$select = $field->getSelect();
$select->addOption('Deaktiviert', '0');
$select->addOption('Aktiviert', '1');

// Nur zeigen wenn Default Widgets aktiviert sind
echo '<div id="default-widgets-options" style="display: none;">';

// Einzelne Default Widgets mit Größen-Optionen
$defaultWidgets = [
    'default_recent_articles' => 'Zuletzt aktualisierte Artikel',
    'default_new_articles' => 'Neue Artikel (30 Tage)',
    'default_media_storage' => 'Medien-Speicherverbrauch (Chart)',
    'default_article_status' => 'Artikel-Status (Chart)',
    'default_system_status' => 'System-Status',
    'default_user_activity' => 'Benutzer-Aktivität (Chart)',
    'default_addon_updates' => 'AddOn Verwaltung (nur Admins)',
];

foreach ($defaultWidgets as $configKey => $label) {
    // Widget aktivieren/deaktivieren
    $field = $form->addCheckboxField($configKey);
    $field->addOption($label, '1');
    
    // Größe (klein/breit) auswählen
    $sizeField = $form->addSelectField($configKey . '_columns', null, ['class' => 'form-control widget-size-select', 'data-widget' => $configKey]);
    $sizeField->setLabel('Größe (' . $label . ')');
    $select = $sizeField->getSelect();
    $select->addOption('Klein (1 Spalte)', '1');
    $select->addOption('Breit (2 Spalten)', '2');
}

echo '</div>';

// JavaScript für Show/Hide der Widget-Optionen und Size-Controls
echo '<script>
$(document).ready(function() {
    function toggleDefaultWidgetOptions() {
        var enabled = $(\'[name="config[default_widgets_enabled]"]\').val();
        if (enabled == "1") {
            $("#default-widgets-options").show();
        } else {
            $("#default-widgets-options").hide();
        }
    }
    
    function toggleWidgetSizeOptions() {
        $(".widget-size-select").each(function() {
            var widgetKey = $(this).data("widget");
            var widgetEnabled = $(\'[name="config[\' + widgetKey + \']"]\').is(":checked");
            if (widgetEnabled) {
                $(this).closest(".rex-form-group").show();
            } else {
                $(this).closest(".rex-form-group").hide();
            }
        });
    }
    
    // Initial state
    toggleDefaultWidgetOptions();
    toggleWidgetSizeOptions();
    
    // On change
    $(\'[name="config[default_widgets_enabled]"]\').change(function() {
        toggleDefaultWidgetOptions();
    });
    
    // Widget checkbox changes
    $(\'input[type="checkbox"][name^="config[default_"]\').change(function() {
        toggleWidgetSizeOptions();
    });
});
</script>';

// Ausgabe des Formulars
$fragment = new rex_fragment();
$fragment->setVar('class', 'edit', false);
$fragment->setVar('title', 'Dashboard Konfiguration', false);
$fragment->setVar('body', $form->get(), false);
echo $fragment->parse('core/page/section.php');

?>
