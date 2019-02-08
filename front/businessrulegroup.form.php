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

Session::checkRight("plugin_monitoring_servicescatalog", READ);

Html::header(__('Monitoring', 'monitoring'),$_SERVER["PHP_SELF"], "plugins",
             "monitoring", "businessrules");

$pMonitoringBusinessrulegroup = new PluginMonitoringBusinessrulegroup();

foreach ($_POST as $key=>$value) {
   if (strstr($key, 'deletebusinessrules-')) {
      $split = explode("-", $key);
      $pmBusinessrule = new PluginMonitoringBusinessrule();
      $pmBusinessrule->delete(array('id'=>$split[1]));
      Html::back();
   } else if (strstr($key, 'deletebrcomponents-')) {
      $split = explode("-", $key);
      $pmBusinessrule_component = new PluginMonitoringBusinessrule_component();
      $pmBusinessrule_component->delete(array('id'=>$split[1]));
      $pmBusinessrule_component->replayDynamicServices($_POST['plugin_monitoring_businessrulegroups_id']);
      Html::back();
   }
}


if (isset($_POST['update'])) {
   $pMonitoringBusinessrulegroup->update($_POST);
} else if (isset($_POST['add'])
        AND isset($_POST['id'])) {

   if (isset($_POST['plugin_monitoring_services_id'])) {
      $pmBusinessrule = new PluginMonitoringBusinessrule();
      $pmBusinessrule->add(array('plugin_monitoring_businessrulegroups_id'=>$_POST['plugin_monitoring_businessrulegroups_id'],
                                 'is_generic'=>$_POST['is_generic'],
                                 'plugin_monitoring_services_id'=>$_POST['plugin_monitoring_services_id']));

   }
} else if (isset($_POST['add'])) {
   $pMonitoringBusinessrulegroup->add($_POST);
} else if (isset($_POST['delete'])) {
   $pMonitoringBusinessrulegroup->delete($_POST);
}
Html::back();


Html::footer();
?>