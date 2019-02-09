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

Session::checkCentralAccess();

Html::header(__('Monitoring - dashboard', 'monitoring'), $_SERVER["PHP_SELF"], "plugins",
    "PluginMonitoringDashboard", "menu");

$pmMessage = new PluginMonitoringMessage();
$pmMessage->getMessages();

$toDisplayArea = 0;

/*
 * Redirect to the dashboard if acces is granted and no configuration is allowed
 */
if (Session::haveRight("plugin_monitoring_dashboard", READ)
    && !Session::haveRight("config", READ)) {
    Html::redirect($CFG_GLPI['root_doc'] . "/plugins/monitoring/front/dashboard.php");
}

/*
 * Add Shinken restart commands if necessary
 */
if (Session::haveRight("plugin_monitoring_restartshinken", READ)) {
    PluginMonitoringDisplay::restartShinken();
}

/*
if (Session::haveRight("plugin_monitoring_dashboard", READ)
      && (
         Session::haveRight("plugin_monitoring_restartshinken", CREATE)
         || Session::haveRight("plugin_monitoring_systemstatus", PluginMonitoringSystem::DASHBOARD)
         || Session::haveRight("plugin_monitoring_hoststatus", PluginMonitoringHost::DASHBOARD)
         || Session::haveRight("plugin_monitoring_componentscatalog", PluginMonitoringComponentscatalog::DASHBOARD)
         || Session::haveRight("plugin_monitoring_service", PluginMonitoringService::DASHBOARD)
         || Session::haveRight("plugin_monitoring_displayview", PluginMonitoringDisplayview::DASHBOARD))) {
   $toDisplayArea++;

   echo "<table class='tab_cadre' width='90%'>";
   echo "<tr class='tab_bg_1'>";
   echo "<th colspan='". count($PM_ALIGNAK_ELEMENTS) ."'>";
   echo "NEW MENU ALIGNAK";
   echo "</th>";
   echo "</tr>";

   echo "<tr class='tab_bg_1'>";
   echo "<td height='30' align='center' colspan='". count($PM_ALIGNAK_ELEMENTS) ."'>";
   echo "<a href='".$CFG_GLPI['root_doc']
        ."/plugins/monitoring/front/componentscatalog.php'>"
        .__('Components catalog (templates + rules)', 'monitoring')."</a>";
   echo "</td>";
   echo "</tr>";

   if (count($PM_ALIGNAK_ELEMENTS) > 0) {
      echo "<tr>";
      echo "<th colspan='". count($PM_ALIGNAK_ELEMENTS) ."'>";
      echo "Alignak Backend elements tables";
      echo "</th>";
      echo "</tr>";

      echo "<tr class='tab_bg_1' height='30'>";
      foreach ($PM_ALIGNAK_ELEMENTS as $element => $label) {
         if ($element == '' ) {
            echo "<td align='center'>";
            echo "|";
            echo "</td>";
         } else {
            echo "<td align='center'>";
            echo "<a href='".$CFG_GLPI['root_doc']
                 ."/plugins/monitoring/front/alignak_element.php"
                 ."?widget_type=table&element=". $element ."&label=". $label ."'>".$label."</a>";
            echo "</td>";
         }
      }
      echo "</tr>";
   }
   echo "</table>";

   echo "<br/>";
}
*/

/*
if (Session::haveRight("plugin_monitoring_displayview", READ)) {

   echo "<table class='tab_cadre' width='90%'>";
   echo "<tr class='tab_bg_1'>";
   if (Session::haveRight("plugin_monitoring_displayview", READ)) {
      $toDisplayArea++;
      echo "<th align='center' height='40' width='34%'>";
      echo "<a href='".$CFG_GLPI['root_doc']."/plugins/monitoring/front/displayview.php'>".__('Views', 'monitoring')."</a>";
      echo "</th>";
   }
   echo "</tr>";
   echo "</table>";
   echo "<br/>";
}
*/

/*
if (Session::haveRight("plugin_monitoring_weathermap", READ)
      || Session::haveRight("plugin_monitoring_displayview", READ)) {
   echo "<table class='tab_cadre' width='90%'>";
   echo "<tr class='tab_bg_1'>";

   if (Session::haveRight("plugin_monitoring_weathermap", READ)) {
      $toDisplayArea++;
      echo "<th align='center' height='30' width='33%'>";
      echo "<a href='".$CFG_GLPI['root_doc']."/plugins/monitoring/front/weathermap.php'>".__('Weathermaps', 'monitoring')."</a>";
      echo "</th>";
   }
   echo "</tr>";
   echo "</table>";
   echo "<br/>";
}
*/

if (Session::haveRight("config", READ)) {
    $toDisplayArea++;
    echo "<table class='tab_cadre' width='950'>";
    echo "<tr class='tab_bg_1'>";
    echo "<th colspan='3' height='30' width='50%'>";
    echo "<a href='" . $CFG_GLPI['root_doc'] . "/plugins/monitoring/front/component.php'>" . __('Components', 'monitoring') . "</a>";
    echo "</th>";

    echo "<th width='15%'>";
    echo "<a href='" . $CFG_GLPI['root_doc'] . "/plugins/monitoring/front/contacttemplate.php'>" . __('Contact templates', 'monitoring') . "</a>";
    echo "</th>";

    echo "<th width='15%'>";
    echo "<a href='" . $CFG_GLPI['root_doc'] . "/plugins/monitoring/front/hostnotificationtemplate.php'>" . __('Hosts notifications templates', 'monitoring') . "</a>";
    echo "</th>";

    echo "<th width='15%'>";
    echo "<a href='" . $CFG_GLPI['root_doc'] . "/plugins/monitoring/front/servicenotificationtemplate.php'>" . __('Services notifications templates', 'monitoring') . "</a>";
    echo "</th>";
    echo "</tr>";


    echo "<tr class='tab_bg_1'>";
    echo "<th width='15%' height='30'>";
    echo "<a href='" . $CFG_GLPI['root_doc'] . "/plugins/monitoring/front/command.php'>" . __('Commands', 'monitoring') . "</a>";
    echo "</th>";

    echo "<th width='15%'>";
    echo "<a href='" . $CFG_GLPI['root_doc'] . "/plugins/monitoring/front/eventhandler.php'>" . __('Event handlers', 'monitoring') . "</a>";
    echo "</th>";

    echo "<th width='15%'>";
    echo "<a href='" . $CFG_GLPI['root_doc'] . "/plugins/monitoring/front/notificationcommand.php'>" . __('Notification commands', 'monitoring') . "</a>";
    echo "</th>";

    echo "<th colspan='3' height='30' width='50%'>";
    echo "</th>";
    echo "</tr>";



    echo "<tr class='tab_bg_1'>";
    echo "<th width='15%' height='30'>";
    echo "<a href='" . $CFG_GLPI['root_doc'] . "/plugins/monitoring/front/check.php'>" . __('Check strategies', 'monitoring') . "</a>";
    echo "</th>";

    echo "<th width='15%'>";
    if (Session::haveRight('calendar', READ)) {
        echo "<a href='" . $CFG_GLPI['root_doc'] . "/front/calendar.php'>" . __('Check periods') . "</a>";
    }
    echo "</th>";

    echo "<th width='15%'>";
    echo "<a href='" . $CFG_GLPI['root_doc'] . "/plugins/monitoring/front/perfdata.php'>" . __('Graph templates', 'monitoring') . "</a>";
    echo "</th>";

    echo "<th>";
    echo "</th>";

    echo "<th width='15%'>";
    echo "<a href='" . $CFG_GLPI['root_doc'] . "/plugins/monitoring/front/realm.php'>" . __('Realms', 'monitoring') . "</a>";
    echo "</th>";

    echo "<th width='15%'>";
    echo "<a href='" . $CFG_GLPI['root_doc'] . "/plugins/monitoring/front/tag.php'>" . __('Tags', 'monitoring') . "</a>";
    echo "</th>";
    echo "</tr>";

    echo "</table>";


}

if ($toDisplayArea <= 0) {
    echo "<table class='tab_cadre' width='950'>";
    echo "<tr class='tab_bg_1'>";
    echo "<th height='80'>";
    echo __('Sorry, your profile does not allow any views in the Monitoring', 'monitoring');
    echo "</th>";
    echo "</tr>";
    echo "</table>";
}

Html::footer();
