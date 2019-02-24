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

Session::checkCentralAccess();

Html::header(__('Monitoring', 'monitoring'), $_SERVER["PHP_SELF"], "plugins",
    "monitoring", "servicedef");

$pmServicedef = new PluginMonitoringServicedef();
if (isset($_POST["add"])) {
    $pmServicedef->add($_POST);
    Html::back();
} else if (isset ($_POST["update"])) {
    $pmServicedef->update($_POST);
    Html::back();
} else if (isset ($_POST["delete"])) {
    $pmServicedef->delete($_POST);
    Html::back();
}


if (isset($_GET["id"])) {
    $pmServicedef->showForm($_GET["id"]);
} else {
    $pmServicedef->showForm(0);
}

Html::footer();
