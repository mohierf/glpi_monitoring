<?php

/**
 *    ------------------------------------------------------------------------
 *    Copyright notice:
 *    ------------------------------------------------------------------------
 *    Plugin Monitoring for GLPI
 *    Copyright (C) 2011-2016 by the Plugin Monitoring for GLPI Development Team.
 *    Copyright (C) 2019 by the Alignak Development Team.
 *    ------------------------------------------------------------------------
 *
 *    LICENSE
 *
 *    This file is part of Plugin Monitoring project.
 *
 *    Plugin Monitoring for GLPI is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU Affero General Public License as published by
 *    the Free Software Foundation, either version 3 of the License, or
 *    (at your option) any later version.
 *
 *    Plugin Monitoring for GLPI is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *    GNU Affero General Public License for more details.
 *
 *    You should have received a copy of the GNU Affero General Public License
 *    along with Monitoring. If not, see <http://www.gnu.org/licenses/>.
 *
 *    ------------------------------------------------------------------------
 *
 */

include("../../../inc/includes.php");

$title = __('Monitoring - dashboard (components catalogs)', 'monitoring');
if ($_SESSION["glpiactiveprofile"]["interface"] == "central") {
    Session::checkCentralAccess();
    Html::header($title, $_SERVER["PHP_SELF"], "plugins",
        "PluginMonitoringDashboard", "dashboard");
} else {
    Session::checkHelpdeskAccess();
    Html::helpHeader($title, $_SERVER['PHP_SELF']);
}

// Display ressources perfdata ?
if (isset($_SESSION['plugin_monitoring']['ressources_perfdata'])) {
    unset($_SESSION['plugin_monitoring']['ressources_perfdata']);
}
// Reduced or normal interface ?
if (!isset($_SESSION['plugin_monitoring_reduced_interface'])) {
    $_SESSION['plugin_monitoring_reduced_interface'] = false;
}
if (isset($_POST['reduced_interface'])) {
    $_SESSION['plugin_monitoring_reduced_interface'] = $_POST['reduced_interface'];
}

$pmDisplay = new PluginMonitoringDisplay();
$pmMessage = new PluginMonitoringMessage();

$pmMessage->getMessages();

$pmDisplay->dashboard();

$pmDisplay->refreshPage(TRUE);

$pmDisplay->showCounters("Componentscatalog");

$pmComponentscatalog = new PluginMonitoringComponentscatalog();
$pmComponentscatalog->showChecks();

if ($_SESSION["glpiactiveprofile"]["interface"] == "central") {
    Html::footer();
} else {
    Html::helpFooter();
}
