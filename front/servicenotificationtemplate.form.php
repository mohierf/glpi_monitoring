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

Session::checkRight("plugin_monitoring_componentscatalog", READ);

Html::header(__('Monitoring - services notifications templates', 'monitoring'), $_SERVER["PHP_SELF"], "plugins",
    "PluginMonitoringDashboard", "servicenotificationtemplate");

$pmSN_template = new PluginMonitoringServicenotificationtemplate();
if (isset($_POST["add"])) {
    if (!isset($_POST['users_id'])
        OR $_POST['users_id'] != "0") {
        $pmSN_template->add($_POST);
    }
    Html::back();
} else if (isset ($_POST["update"])) {
    $pmSN_template->update($_POST);
    Html::back();
} else if (isset ($_POST["delete"])) {
    $pmSN_template->delete($_POST);
    Html::back();
}


if (isset($_GET["id"])) {
    $pmSN_template->showForm($_GET["id"]);
} else {
    $pmSN_template->showForm("");
}

Html::footer();
