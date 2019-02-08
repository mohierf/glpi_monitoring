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

include ("../../../inc/includes.php");

if ((! isset ($_REQUEST["widget_type"])) || (! isset ($_REQUEST["element"]))) {
   echo "<table class='tab_cadre' width='90%'>";
   echo "<tr class='tab_bg_1'>";
   echo "<th height='80'>";
   echo __('Sorry, this page needs to specify an element type to view.', 'monitoring');
   echo "</th>";
   echo "</tr>";
   echo "</table>";
} else {
   Html::header($_REQUEST["label"], $_SERVER["PHP_SELF"], "plugins",
                "PluginMonitoringDashboard", $_REQUEST["element"]);

   PluginMonitoringToolbox::logIfExtradebug(
      "Request Alignak WebUI for: ". $_REQUEST["widget_type"]
      . ", element: " . $_REQUEST["element"] . "\n"
   );

   $abc = new Alignak_Backend_Client($PM_CONFIG['alignak_backend_url']);
   $token = PluginMonitoringUser::myToken($abc);
   if (!empty($token)) {
      $pmWebui = new PluginMonitoringWebui();
      $pmWebui->authentication($token);

      $page = $PM_CONFIG['alignak_webui_url']
              ."/external/". $_REQUEST["widget_type"] ."/"
              . $_REQUEST["element"] ."s_". $_REQUEST["widget_type"]
              ."?widget_id=". $_REQUEST["element"] ."s_". $_REQUEST["widget_type"] ."_1"
              ."&links=/glpi090/plugins/monitoring/front/test.php";
      $pmWebui->load_page($page);
   } else {
      echo "<table class='tab_cadre' width='90%'>";
      echo "<tr class='tab_bg_1'>";
      echo "<th height='80'>";
      echo __('Sorry, Alignak backend authentication failed. Please check the current credentials associated with the current Glpi user account.', 'monitoring');
      echo "</th>";
      echo "</tr>";
      echo "</table>";
   }
}

Html::footer();
?>
