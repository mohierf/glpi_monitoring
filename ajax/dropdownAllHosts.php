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

header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

Session::checkCentralAccess();

// Make a select box
if ($_POST["idtable"] && class_exists($_POST["idtable"])) {
   $table = getTableForItemType($_POST["idtable"]);

   // Link to user for search only > normal users
   $link = "dropdownValue.php";

   $rand     = mt_rand();
   $use_ajax = false;

   if ($CFG_GLPI["use_ajax"] && countElementsInTable($table)>$CFG_GLPI["ajax_limit_count"]) {
      $use_ajax = true;
   }

   $paramsallitems = array('searchText'          => '__VALUE__',
                           'table'               => $table,
                           'itemtype'            => $_POST["idtable"],
                           'rand'                => $rand,
                           'myname'              => $_POST["myname"],
                           'displaywith'         => array('otherserial', 'serial'),
                           'display_emptychoice' => true);

   if (isset($_POST['value'])) {
      $paramsallitems['value'] = $_POST['value'];
   }
   if (isset($_POST['entity_restrict'])) {
      $paramsallitems['entity_restrict'] = $_POST['entity_restrict'];
   }
   if (isset($_POST['condition'])) {
      $paramsallitems['condition'] = stripslashes($_POST['condition']);
   }

   $pmHost = new PluginMonitoringHost();
   $classname = $_POST['idtable'];
   $class = new $classname;

   $a_list = $pmHost->find("`itemtype`='".$classname."'");
   $a_elements = array();
   foreach ($a_list as $data) {
      $class->getFromDB($data['items_id']);
      $a_elements[$data['id']] = $class->getName();
   }
   asort($a_elements);
   Dropdown::showFromArray($_POST['myname'], $a_elements);

}

?>