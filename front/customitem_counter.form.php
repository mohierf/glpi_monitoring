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

include ("../../../inc/includes.php");

Session::checkRight("plugin_monitoring_displayview", READ);

Html::header(__('Monitoring - counter', 'monitoring'),$_SERVER["PHP_SELF"], "plugins",
             "monitoring", "customitem_counter");

$pmCustomitem_Counter = new PluginMonitoringCustomitem_Counter();

if (isset($_POST['add_item'])) {
   if (isset($_POST['item'])) {
      $pmCustomitem_Counter->getFromDB($_POST['id']);
      $input = array();
      $input['id'] = $_POST['id'];

      if ($pmCustomitem_Counter->fields['aggregate_items'] == '') {
         $aggregate_items = array();
      } else {
         $aggregate_items = importArrayFromDB($pmCustomitem_Counter->fields['aggregate_items']);
      }
      if (isset($_POST['plugin_monitoring_componentscatalogs_id'])) {
         $aggregate_items_add = array();
         $a = 'PluginMonitoringComponentscatalog';
         $b = $_POST['plugin_monitoring_componentscatalogs_id'];
         $c = 'PluginMonitoringComponent';
         $d = $_POST['PluginMonitoringComponent'];
         $item_split = explode('/', $_POST['item']);

         $aggregate_items_add[$a]["id".$b][$c]["id".$d] = array(
             array(
                 'perfdatadetails_id' => $item_split[0],
                 'perfdatadetails_dsname' => $item_split[1]
             )
         );
         $aggregate_items = array_merge_recursive($aggregate_items, $aggregate_items_add);
      }
      $input['aggregate_items'] = exportArrayToDB($aggregate_items);

/*
      if (isset($_POST['warn_other_value'])) {
         $input['aggregate_warn'] = $_POST['warn_other_value'];
      } else {
         if (is_numeric($input['aggregate_warn'])) {

         }
         // It's an array
      }

      if (isset($_POST['crit_other_value'])) {
         $input['aggregate_crit'] = $_POST['crit_other_value'];
      } else {
         // It's an array
      }

      if (isset($_POST['limit_other_value'])) {
         $input['aggregate_limit'] = $_POST['limit_other_value'];
      } else {
         // It's an array
      }
*/
      $pmCustomitem_Counter->update($input);
      Html::back();
   }
   Html::back();
} else if (isset($_POST['delete_item'])) {
   $pmCustomitem_Counter->deleteCounterItems($_POST);
   Html::back();
} else if (isset ($_POST["add"])) {
   $pmCustomitem_Counter->add($_POST);
   Html::back();
} else if (isset ($_POST["update"])) {
   $pmCustomitem_Counter->update($_POST);
   Html::back();
} else if (isset ($_POST["delete"])) {
   $pmCustomitem_Counter->delete($_POST);
   $pmCustomitem_Counter->redirectToList();
}


if (isset($_GET["id"])) {
   $pmCustomitem_Counter->showForm($_GET["id"], array('canedit' => Session::haveRight("config", UPDATE)));
} else {
   $pmCustomitem_Counter->showForm("", array('canedit' => Session::haveRight("config", UPDATE)));
}

Html::footer();
