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

$pmBusinessrule_component = new PluginMonitoringBusinessrule_component();
$pmComponentscatalog_Component = new PluginMonitoringComponentscatalog_Component();

if (isset($_POST['add'])) {
   if (!isset($_POST['plugin_monitoring_components_id'])
           || $_POST['plugin_monitoring_components_id'] < 1) {
      Html::back();
   }

   $a_data = current($pmComponentscatalog_Component->find(
           "`plugin_monitoring_componentscalalog_id`='".$_POST['plugin_monitoring_componentscatalogs_id']."'
            AND `plugin_monitoring_components_id`='".$_POST['plugin_monitoring_components_id']."'",
           '',
           1));
   $_POST['plugin_monitoring_componentscatalogs_components_id'] = $a_data['id'];
   $pmBusinessrule_component->add($_POST);
   $pmBusinessrule_component->replayDynamicServices($_POST['plugin_monitoring_businessrulegroups_id']);
}
Html::back();

Html::footer();
?>