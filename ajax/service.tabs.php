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

header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

if (!isset($_POST["id"])) {
    exit();
}
if (!isset($_POST["sort"])) {
    $_POST["sort"] = "";
}
if (!isset($_POST["order"])) {
    $_POST["order"] = "";
}
if (!isset($_POST["withtemplate"])) {
    $_POST["withtemplate"] = "";
}


$pmBusinessrule = new PluginMonitoringBusinessrule();

//show computer form to add
//if ($_POST["id"]>0 && $pmBusinessrule->can($_POST["id"],'r')) {

switch ($_POST['glpi_tab']) {
    case -1 :

        break;

    case 2 :
        $pmBusinessrule->showForm();
        break;

    default :

}

//}

Html::ajaxFooter();
