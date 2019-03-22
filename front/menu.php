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

Session::checkCentralAccess();

Session::checkRight("plugin_monitoring_configuration", READ);

Html::header(
    __('Monitoring - dashboard', 'monitoring'),
    '', 'config', 'pluginmonitoringmenu', 'menu');

$toDisplayArea = 0;

/*
 * Redirect to the dashboard if acces is granted and no configuration is allowed
 */
if (Session::haveRight("plugin_monitoring_dashboard", READ)
    and !Session::haveRight("plugin_monitoring_configuration", READ)) {
    Html::redirect($CFG_GLPI['root_doc'] . "/plugins/monitoring/front/dashboard.php");
}

echo "<table class='tab_cadre'>";
echo "<tr>";

/*
 * Add monitoring framework restart commands if necessary
 */
if (Session::haveRight("plugin_monitoring_command_fmwk", CREATE)) {
    echo "<td style='width: 17%; padding: 1%;'>";

    PluginMonitoringLog::hasConfigurationChanged(true);

    PluginMonitoringLog::isFrameworkRunning(true);

    PluginMonitoringDashboard::restartFramework();

    echo "</td>";
    echo "<td style='width: 77%; padding: 1%;'>";
} else {
    echo "<td style='width: 97%; padding: 1%;'>";
}

if (Session::haveRight("plugin_monitoring_dashboard", READ)) {
    $toDisplayArea++;

    echo '<table class="tab_cadre" style="width:100%; padding: 10px">';

    echo '<tr class="tab_bg_1">';
    echo '<th class="center" height="40px">';
    echo '<a href="' . $CFG_GLPI["root_doc"] . '/plugins/monitoring/front/dashboard.php">' . __("Dashboard", "monitoring") . '</a>';
    echo '</th>';
    echo '</tr>';
    echo '</table>';

    echo '<br>';
}


//if (Session::haveRight("plugin_monitoring_displayview", READ)) {
//    $toDisplayArea++;
//
//    echo '<table class="tab_cadre" style="width:100%; padding: 10px">';
//
//    echo '<tr class="tab_bg_1">';
//    echo '<th colspan="6" height="15px" width="50%" style="border-bottom: 1px solid">';
//    echo __('Monitoring views', 'monitoring');
//    echo '</th>';
//    echo '</tr>';
//
//    echo '<tr class="tab_bg_1">';
//    echo '<th class="center" height="40px">';
//    echo '<a href="' . $CFG_GLPI["root_doc"] . '/plugins/monitoring/front/displayview.php">' . __("Views", "monitoring") . '</a>';
//    echo '&nbsp;&nbsp;&nbsp;<em>To be replaced with real views...</em>';
//    echo '</th>';
//    echo '</tr>';
//    echo '</table>';
//
//    echo '<br>';
//}


//if (Session::haveRight("plugin_monitoring_componentscatalog", READ)) {
//    $toDisplayArea++;
//
//    echo '<table class="tab_cadre" style="width:100%; padding: 10px">';
//
//    echo '<tr class="tab_bg_1">';
//    echo '<th class="center" height="40px">';
//    echo '<a href="' . $CFG_GLPI["root_doc"] . '/plugins/monitoring/front/componentscatalog.php">' . __("Components catalogs", "monitoring") . '</a>';
//    echo '</th>';
//    echo '</tr>';
//
//    echo '<tr class="tab_bg_1">';
//    echo '<th class="center" height="40px">';
//    echo '<a href="' . $CFG_GLPI["root_doc"] . '/plugins/monitoring/front/hosttemplate.php">' . __("Hosts templates", "monitoring") . '</a>';
//    echo '</th>';
//    echo '</tr>';
//    echo '</table>';
//
//    echo '<br>';
//}


if (Session::haveRight("plugin_monitoring_configuration", READ)) {
    $toDisplayArea++;

    echo '<table class="tab_cadre" style="width:100%; padding: 10px">';

    echo '<tr class="tab_bg_1">';
    echo '<th colspan="6" height="30px" width="50%" style="border-bottom: 1px solid">';
    echo __('Monitoring items', 'monitoring');
    echo '</th>';
    echo '</tr>';

    echo '<tr class="tab_bg_1">';
    echo '<th colspan="1" height="20px" width="15%" style="border-bottom: 1px solid">';
    echo '<a href="' . $CFG_GLPI["root_doc"] . '/plugins/monitoring/front/componentscatalog.php">' . __("Components catalogs", "monitoring") . '</a>';
    echo '</th>';
    echo '<th colspan="2" height="20px" width="35%" style="border-bottom: 1px solid">';
    echo '<a href="' . $CFG_GLPI["root_doc"] . '/plugins/monitoring/front/component.php">' . __("Components", "monitoring") . '</a>';
    echo '</th>';

    echo '<th width="15%">';
    echo '<a href="' . $CFG_GLPI["root_doc"] . '/plugins/monitoring/front/contacttemplate.php">' . __("Contact templates", "monitoring") . '</a>';
    echo '</th>';

    echo '<th width="15%">';
    echo '<a href="' . $CFG_GLPI["root_doc"] . '/plugins/monitoring/front/hostnotificationtemplate.php">' . __("Hosts notifications templates", "monitoring") . '</a>';
    echo '</th>';

    echo '<th width="15%">';
    echo '<a href="' . $CFG_GLPI["root_doc"] . '/plugins/monitoring/front/servicenotificationtemplate.php">' . __("Services notifications templates", "monitoring") . '</a>';
    echo '</th>';
    echo '</tr>';


    echo '<tr class="tab_bg_1">';
    echo '<th width="15%" height="30px">';
    echo '<a href="' . $CFG_GLPI["root_doc"] . '/plugins/monitoring/front/command.php">' . __("Commands", "monitoring") . '</a>';
    echo '</th>';

    echo '<th width="15%">';
    echo '<a href="' . $CFG_GLPI["root_doc"] . '/plugins/monitoring/front/check.php">' . __("Check strategies", "monitoring") . '</a>';
    echo '</th>';

    echo '<th width="15%">';
    echo '<a href="' . $CFG_GLPI["root_doc"] . '/front/calendar.php">' . __("Check periods") . '</a>';
    echo '</th>';

    echo '<th colspan="3" height="30" width="50%">';
    echo '</th>';
    echo '</tr>';


    echo '<tr class="tab_bg_1">';
    echo '<th width="15%" height="30px">';
    echo '<a href="' . $CFG_GLPI["root_doc"] . '/plugins/monitoring/front/eventhandler.php">' . __("Event handlers", "monitoring") . '</a>';
    echo '</th>';

    echo '<th width="15%">';
    echo '</th>';

//    echo '<th width="15%">';
//    echo '<a href="' . $CFG_GLPI["root_doc"] . '/plugins/monitoring/front/perfdata.php">' . __("Graph templates", "monitoring") . '</a>';
//    echo '</th>';

    echo '<th>';
    echo '<a href="' . $CFG_GLPI["root_doc"] . '/plugins/monitoring/front/notificationcommand.php">' . __("Notification commands", "monitoring") . '</a>';
    echo '</th>';

    echo '<th width="15%">';
    echo '<a href="' . $CFG_GLPI["root_doc"] . '/plugins/monitoring/front/realm.php">' . __("Realms", "monitoring") . '</a>';
    echo '</th>';

    echo '<th width="15%">';
    echo '<a href="' . $CFG_GLPI["root_doc"] . '/plugins/monitoring/front/tag.php">' . __("Tags", "monitoring") . '</a>';
    echo '</th>';
    echo '</tr>';

    echo '</table>';
}
echo "</td>";
echo "</tr>";
echo "</table>";

if ($toDisplayArea <= 0) {
    echo "<table class='tab_cadre'>";
    echo "<tr class=''>";
    echo "<th style='height:80px'>";
    echo __('Sorry, your profile does not allow any views in the Monitoring', 'monitoring');
    echo "</th>";
    echo "</tr>";
    echo "</table>";
}

Html::footer();
