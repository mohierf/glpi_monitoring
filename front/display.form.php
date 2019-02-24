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

Session::checkCentralAccess();

Html::header(__('Monitoring', 'monitoring'), $_SERVER["PHP_SELF"], "plugins",
    "monitoring", "display");

if (isset($_POST['sessionupdate'])) {
    $_SESSION['plugin_monitoring']['_refresh'] = $_POST['_refresh'];
    Html::back();
    exit;
}

if (isset($_POST["plugin_monitoring_timezone"])) {
    $_SESSION['plugin_monitoring_timezone'] = $_POST["plugin_monitoring_timezone"];
    Html::back();
}

if (isset($_POST['updateperfdata'])) {
    $pmComponent = new PluginMonitoringComponent();

    if (isset($_POST["perfnameinvert"])) {
        /* @var CommonDBTM $item */
        $itemtype = $_GET['itemtype'];
        $items_id = $_GET['items_id'];
        $item = new $itemtype();
        $item->getFromDB($items_id);
        $pmComponent->getFromDB($item->fields['plugin_monitoring_components_id']);
        $_SESSION['plugin_monitoring']['perfnameinvert'][$pmComponent->fields['id']] = [];
        $_POST['perfnameinvert'] = explode("####", $_POST['perfnameinvert']);
        foreach ($_POST["perfnameinvert"] as $perfname) {
            $_SESSION['plugin_monitoring']['perfnameinvert'][$pmComponent->fields['id']][$perfname] = "checked";
        }
    }

    if (isset($_POST["perfnamecolor"])) {
        /* @var CommonDBTM $item */
        $itemtype = $_GET['itemtype'];
        $items_id = $_GET['items_id'];
        $item = new $itemtype();
        $item->getFromDB($items_id);
        $pmComponent->getFromDB($item->fields['plugin_monitoring_components_id']);
        $_SESSION['plugin_monitoring']['perfnamecolor'][$pmComponent->fields['id']] = [];
        foreach ($_POST["perfnamecolor"] as $perfname => $color) {
            if ($color != '') {
                $_SESSION['plugin_monitoring']['perfnamecolor'][$pmComponent->fields['id']][$perfname] = $color;
            }
        }
    }
    Html::back();
}

if (isset($_GET['itemtype']) AND isset($_GET['items_id'])) {

    PluginMonitoringToolbox::loadLib();

    $pMonitoringDisplay = new PluginMonitoringDisplay();
    $pMonitoringDisplay->displayGraphs($_GET['itemtype'], $_GET['items_id']);
}

Html::footer();
