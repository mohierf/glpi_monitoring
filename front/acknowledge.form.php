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

Session::checkRight("plugin_monitoring_acknowledge", READ);

Html::header(__('Monitoring - acknowledges', 'monitoring'),'', "plugins",
        "PluginMonitoringDashboard", "acknowledge");

$pmAcknowledge = new PluginMonitoringAcknowledge();

if (isset ($_POST["add"])) {
   $pmAcknowledge->add($_POST);
   $pmAcknowledge->redirectToList();
} else if (isset ($_POST["update"])) {
   $pmAcknowledge->update($_POST);
   $pmAcknowledge->redirectToList();
} else if (isset ($_POST["purge"])) {
   $pmAcknowledge->delete($_POST);
   $pmAcknowledge->redirectToList();
}

// Read or edit acknowledge ...
if (isset($_GET['id'])) {
   // If ack id is defined, use it ...
   $pmAcknowledge->display(array('id' => $_GET["id"]));
} else if (! isset($_GET['id']) && isset($_GET['itemtype']) && isset($_GET['items_id'])) {
   // If host is defined, use it ...
   $pmAcknowledge->showForm(-1, $_GET['itemtype'], $_GET['items_id']);
}

Html::footer();
