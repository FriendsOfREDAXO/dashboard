<?php

/**
 * Uninstall-Script für Dashboard AddOn
 * Löscht die Tabelle für Schnellnotizen
 */

use FriendsOfREDAXO\Dashboard\DashboardNotes;

// Tabelle für Schnellnotizen löschen
DashboardNotes::deleteAllNotes();
