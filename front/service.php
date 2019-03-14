<?php

/**
 *    ------------------------------------------------------------------------
 *    Copyright notice:
 *    ------------------------------------------------------------------------
 *    Plugin Monitoring for GLPI
 *    Copyright (C) 2011-2016 by the Plugin Monitoring for GLPI Development Team.
 *    Copyright (C) 2019 by Frédéric Mohier.
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

$title = __('Monitoring - dashboard (resources)', 'monitoring');
if ($_SESSION["glpiactiveprofile"]["interface"] == "central") {
    Session::checkCentralAccess();
    Html::header($title, $_SERVER["PHP_SELF"], "plugins",
        "PluginMonitoringDashboard", "dashboard");
} else {
    Session::checkHelpdeskAccess();
    Html::helpHeader($title, $_SERVER['PHP_SELF']);
}

if (!isset($_GET['itemtype'])) {
    $_GET['itemtype'] = "PluginMonitoringService";
}
$params = Search::manageParams("PluginMonitoringService", $_GET);

// Reduced or normal interface ?
if (!isset($_SESSION['plugin_monitoring']['reduced_interface'])) {
    $_SESSION['plugin_monitoring']['reduced_interface'] = false;
}
if (isset($_POST['reduced_interface'])) {
    $_SESSION['plugin_monitoring']['reduced_interface'] = $_POST['reduced_interface'];
}

$pmDisplay = new PluginMonitoringDisplay();
$pmDisplay->dashboard(true);
$pmDisplay->showServicesCounters(true);

// Manage search
if (isset($_SESSION['plugin_monitoring']['service'])) {
    $_GET = $_SESSION['plugin_monitoring']['service'];
}
if (isset($_GET['reset'])) {
    unset($_SESSION['glpisearch']['PluginMonitoringService']);
}
if (isset($_GET['glpi_tab'])) {
    unset($_GET['glpi_tab']);
}
if (isset($_GET['hidesearch'])) {
    echo "<table class='tab_cadre_fixe'>";
    echo "<tr class='tab_bg_1'>";
    echo "<th>";
    echo "<a onClick='$(\"#search_form\").toggle();'>" . __('Display search form', 'monitoring') . "</a>";
    echo "</th>";
    echo "</tr>";
    echo "</table>";
    echo "<div style='display: none;' id='search_form'>";
}
Search::showGenericSearch("PluginMonitoringService", $params);
if (isset($_GET['hidesearch'])) {
    echo "</div>";
}
$perfdatas = false;
if (isset($_GET['perfdatas'])) {
    $perfdatas = true;
}

$pmDisplay->showResourcesBoard('', $perfdatas, $params);
if (isset($_SESSION['glpisearch']['PluginMonitoringService']['reset'])) {
    unset($_SESSION['glpisearch']['PluginMonitoringService']['reset']);
}

if ($_SESSION["glpiactiveprofile"]["interface"] == "central") {
    Html::footer();
} else {
    Html::helpFooter();
}
