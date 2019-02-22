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

Session::checkCentralAccess();

Html::header(__('Monitoring', 'monitoring'),$_SERVER["PHP_SELF"], "plugins",
             "monitoring", "host");

$pmHost_Host = new PluginMonitoringHost_Host();
if (isset($_POST['parent_add'])) {
   // Add host in dependencies/parent of host

   $input = array();
   $input['plugin_monitoring_hosts_id_1'] = $_POST['id'];
   $input['plugin_monitoring_hosts_id_2'] = $_POST['parent_to_add'];
   $pmHost_Host->add($input);

   Html::back();
} else if (isset($_POST['parent_delete'])) {
   // Delete host in dependencies/parent of host

   foreach ($_POST['parent_to_delete'] as $delete_id) {
      $query = "DELETE FROM ".$pmHost_Host->getTable()."
         WHERE `plugin_monitoring_hosts_id_1`='".$_POST['id']."'
            AND `plugin_monitoring_hosts_id_2`='".$delete_id."'";
      $DB->query($query);
   }
   Html::back();
}

Html::footer();
