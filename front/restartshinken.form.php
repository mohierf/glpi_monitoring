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

Session::checkRight("plugin_monitoring_restartshinken", CREATE);

$pmShinkenwebservice = new PluginMonitoringShinkenwebservice();
if (isset($_GET["tag"])) {
    Toolbox::logInFile("pm-restart", "call sendRestartArbiter, tag: " . $_GET["tag"] . "\n");
    $pmShinkenwebservice->sendRestartArbiter(1, $_GET["tag"], isset($_GET["action"]) ? $_GET["action"] : 'restart');
} else {
    Toolbox::logInFile("pm-restart", "call sendRestartArbiter, no tag\n");
    $pmShinkenwebservice->sendRestartArbiter(1);
}
Html::back();
