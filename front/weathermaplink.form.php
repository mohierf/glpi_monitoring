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

Session::checkRight("plugin_monitoring_weathermap", READ);

Html::header(__('Monitoring', 'monitoring'),$_SERVER["PHP_SELF"], "plugins",
             "monitoring", "weathermaplink");


$pmWeathermaplink = new PluginMonitoringWeathermaplink();

if (isset ($_POST["add"])) {
   $split = explode("-", $_POST['linksource']);
   $_POST['plugin_monitoring_weathermapnodes_id_1'] = $split[0];
   $_POST['plugin_monitoring_services_id'] = $split[1];
   $pmWeathermaplink->add($_POST);
   Html::back();
} else if (isset ($_POST["update"])) {
   $_POST['id'] = $_POST['id_update'];
   unset($_POST['plugin_monitoring_weathermapnodes_id_1']);
   unset($_POST['plugin_monitoring_weathermapnodes_id_2']);
   $_POST['bandwidth_in'] = $_POST['up_bandwidth_in'];
   $_POST['bandwidth_out'] = $_POST['up_bandwidth_out'];
   $pmWeathermaplink->update($_POST);
   Html::back();
} else if (isset ($_POST["purge"])) {
   $pmWeathermaplink->delete($_POST);
   Html::back();
}

Html::footer();

?>