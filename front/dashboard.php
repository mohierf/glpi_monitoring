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

include("../../../inc/includes.php");

$title = __('Monitoring - dashboard', 'monitoring');
if ($_SESSION["glpiactiveprofile"]["interface"] == "central") {
    Session::checkCentralAccess();
    Html::header($title, "", "config", "pluginmonitoringmenu", "dashboard");
} else {
    Session::checkHelpdeskAccess();
    Html::helpHeader($title, $_SERVER['PHP_SELF']);
}

Session::checkRight("plugin_monitoring_dashboard", READ);

$pmD = new PluginMonitoringDashboard();
$pmD->showMenu(false);


if ($_SESSION["glpiactiveprofile"]["interface"] == "central") {
    Html::footer();
} else {
    Html::helpFooter();
}
