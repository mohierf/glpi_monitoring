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
             "monitoring", "weathermapnode");


$pmWeathermapnode = new PluginMonitoringWeathermapnode();
if (isset ($_POST["add"])) {
   if ($_POST['x'] == '') {
      Html::back();
      exit;
   }
   $pmWeathermapnode->add($_POST);
   Html::back();
} else if (isset ($_POST["update"])) {
   if ($_POST['x'] == '') {
      Html::back();
      exit;
   }
   unset($_POST['name']);
   $_POST['name'] = $_POST['nameupdate'];
   unset($_POST['itemtype']);
   $_POST['id'] = $_POST['id_update'];
   $pmWeathermapnode->update($_POST);
   Html::back();
} else if (isset ($_POST["purge"])) {
   $pmWeathermaplink = new PluginMonitoringWeathermaplink();
   $a_links = $pmWeathermaplink->find("`plugin_monitoring_weathermapnodes_id_1`='".$_POST['id']."'
      OR `plugin_monitoring_weathermapnodes_id_2`='".$_POST['id']."'");
   foreach ($a_links as $data) {
      $pmWeathermaplink->delete($data);
   }
   $pmWeathermapnode->delete($_POST);
   Html::back();
}

Html::footer();

?>