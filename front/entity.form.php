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

include ("../../../inc/includes.php");

Session::checkRight('entity', READ);

Html::header(__('Monitoring', 'monitoring'),$_SERVER["PHP_SELF"], "plugins",
             "monitoring", "entity");

$object = new PluginMonitoringEntity();
if (isset($_POST['add'])) {
    // Check CREATE ACL
    Session::checkRight('entity', CREATE);
    $object->add($_POST);
    Html::back();
//    $object->redirectToList();
} else if (isset($_POST['update'])) {
    // Check UPDATE ACL
    Session::checkRight('entity', UPDATE);
    $object->update($_POST);
    Html::back();
//} else if (isset($_POST['delete'])) {
//    // Check DELETE ACL
//    Session::checkRight('entity', DELETE);
//    $object->delete($_POST);
//    $object->redirectToList();
//} else if (isset($_POST['purge'])) {
//    // Check PURGE ACL
//    Session::checkRight('entity', PURGE);
//    $object->delete($_POST, 1);
//    $object->redirectToList();
}

//Html::header(
//    __('Alignak - dashboards', 'alignak'),
//    $_SERVER['PHP_SELF'],
//    'admin',
//    'pluginalignakmenu', 'alignak');
//
// Default is to display the object
$with_template = (isset($_GET['withtemplate']) ? $_GET['withtemplate'] : 0);

if (isset($_GET["id"])) {
    $object->display([
        'id' => $_GET['id'],
        'canedit' => PluginMonitoringEntity::canUpdate(),
        'withtemplate' => $with_template]);
} else {
    $object->showForm(-1);
}

Html::footer();
