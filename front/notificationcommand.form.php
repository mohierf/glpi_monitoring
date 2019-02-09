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

include("../../../inc/includes.php");

Session::checkRight("plugin_monitoring_command", READ);

Html::header(__('Monitoring - notification commands', 'monitoring'),
    "", "config", "pluginmonitoringmenu", "notificationcommand");

$pmNotificationcommand = new PluginMonitoringNotificationcommand();

if (isset($_POST["copy"])) {
    $pmNotificationcommand->showForm("", array(), $_POST);
    Html::footer();
    exit;
} else if (isset ($_POST["add"])) {
    $pmNotificationcommand->add($_POST);
    Html::back();
} else if (isset ($_POST["update"])) {
    $pmNotificationcommand->update($_POST);
    Html::back();
} else if (isset ($_POST["purge"])) {
    $pmNotificationcommand->delete($_POST);
    $pmNotificationcommand->redirectToList();
}

if (!isset($_GET["id"])) {
    $_GET["id"] = "";
}

$pmNotificationcommand->display(array('id' => $_GET["id"]));

Html::footer();
