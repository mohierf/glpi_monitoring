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

Session::checkRight("plugin_monitoring_contact", UPDATE);

Html::header(__('Monitoring - contacts', 'monitoring'),
    "", "config", "pluginmonitoringmenu", "contact");

$pmContact = new PluginMonitoringContact();
if (isset($_POST["add"])) {
   if ($_POST['users_id'] != "0") {
       // Get default contact template
      $pmContacttemplate = new PluginMonitoringContacttemplate();
      $a_template = current($pmContacttemplate->find("`is_default`='1'", '', 1));
      if (isset($a_template['id'])) {
         $_POST['plugin_monitoring_contacttemplates_id'] = $a_template['id'];
      }
      $pmContact->add($_POST);
   }
   Html::back();
} else if (isset ($_POST["update"])) {
   $pmContact->update($_POST);
   Html::back();
} else if (isset ($_POST["delete"])) {
   $pmContact->delete($_POST);
   Html::back();
}

Html::footer();
