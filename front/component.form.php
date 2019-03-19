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

Session::checkRight("plugin_monitoring_component", READ);

Html::header(__('Monitoring - components', 'monitoring'),
    "", "config", "pluginmonitoringmenu", "component");


$pMonitoringComponent = new PluginMonitoringComponent();
if (isset($_POST["copy"])) {
    $pMonitoringComponent->showForm(0, [], $_POST);
    Html::footer();
    exit;
} else if (isset ($_POST["add"])) {
    if (isset($_POST['arg'])) {
        $_POST['arguments'] = exportArrayToDB($_POST['arg']);
    }
    if (empty($_POST['name'])
// No more mandatory!
        or empty($_POST['plugin_monitoring_checks_id'])
        or empty($_POST['plugin_monitoring_commands_id'])
        or empty($_POST['calendars_id'])) {

        $_SESSION['plugin_monitoring_components'] = $_POST;

        Session::addMessageAfterRedirect("<span class='red'>" . __('Fields with asterisk are required', 'monitoring') . "</span>");
        Html::back();
    }

    $pMonitoringComponent->add($_POST);
    Html::back();
} else if (isset ($_POST["update"])) {
    if (isset($_POST['arg'])) {
        $_POST['arguments'] = exportArrayToDB($_POST['arg']);
    }
    if (empty($_POST['name'])
// No more mandatory!
//        or empty($_POST['plugin_monitoring_checks_id'])
//        or empty($_POST['plugin_monitoring_commands_id'])
        or empty($_POST['calendars_id'])) {

        $_SESSION['plugin_monitoring_components'] = $_POST;

        Session::addMessageAfterRedirect("<span class='red'>" . __('Fields with asterisk are required', 'monitoring') . "</span>");
        Html::back();
    }
    $pMonitoringComponent->update($_POST);
    Html::back();
} else if (isset ($_POST["purge"])) {
    $pMonitoringComponent->delete($_POST);
    $pMonitoringComponent->redirectToList();
} else if (isset($_POST['updateperfdata'])) {
    $a_perfname = [];

    $a_perfnameinvert = [];
    if (isset($_POST['perfnameinvert'])) {
        $_POST['perfnameinvert'] = explode("####", $_POST['perfnameinvert']);
        foreach ($_POST['perfnameinvert'] as $perfname) {
            $a_perfnameinvert[$perfname] = '1';
        }
    }

    $a_perfnamecolor = [];
    if (isset($_POST['perfnamecolor'])) {
        foreach ($_POST['perfnamecolor'] as $perfname => $color) {
            if ($color != '') {
                $a_perfnamecolor[$perfname] = $color;
            }
        }
    }
    $input = [];
    $input['id'] = $_POST['id'];
    // $input['perfnameinvert'] = exportArrayToDB($a_perfnameinvert);
    $input['perfnameinvert'] = serialize($a_perfnameinvert);
    // $input['perfnamecolor'] = exportArrayToDB($a_perfnamecolor);
    $input['perfnamecolor'] = serialize($a_perfnamecolor);

    $pMonitoringComponent->update($input);
    Html::back();
}

if (!isset($_GET["id"])) {
    $_GET["id"] = "";
}

$pMonitoringComponent->display(['id' => $_GET["id"]]);

Html::footer();
