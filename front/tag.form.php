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

Session::checkRight("plugin_monitoring_tag", READ);

Html::header(__('Monitoring', 'monitoring'),$_SERVER["PHP_SELF"], "plugins",
             "PluginMonitoringDashboard", "tag");

$pmTag = new PluginMonitoringTag();

if (isset($_POST["add"])) {
   $pmTag->add($_POST);
   Html::back();
} else if (isset($_POST["update"])) {
   $pmTag->update($_POST);
   Html::back();
} else if (isset($_POST["purge"])) {
   $pmTag->getFromDB($_POST['id']);
   if ($pmTag->fields['tag'] != '') {
      $pmTag->delete($_POST);
      $pmTag->redirectToList();
   }
   Html::back();
}

if (!isset($_GET["id"])) {
   $_GET["id"] = "";
}

$pmTag->display(array('id' => $_GET["id"]));

Html::footer();

?>