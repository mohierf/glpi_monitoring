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

include ("../../../inc/includes.php");

Session::checkRight("plugin_monitoring_componentscatalog", READ);

$pmComponentscatalog_Component = new PluginMonitoringComponentscatalog_Component();

if (isset ($_POST["add"])) {
   $pmComponentscatalog_Component->add($_POST);
   $pmComponentscatalog_Component->addComponentToItems($_POST['plugin_monitoring_componentscatalogs_id'],
                                                       $_POST['plugin_monitoring_components_id']);
   Html::back();
} else if (isset($_POST["deleteitem"])) {
   foreach ($_POST["item"] as $id=>$num) {
      $fields = array();
      $pmComponentscatalog_Component->getFromDB($id);
      $fields = $pmComponentscatalog_Component->fields;

      $pmComponentscatalog_Component->delete(array('id'=>$id));
      $pmComponentscatalog_Component->removeComponentToItems($fields['plugin_monitoring_componentscatalogs_id'],
                                                          $fields['plugin_monitoring_components_id']);

   }
   Html::back();
}

Html::footer();
