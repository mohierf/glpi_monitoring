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

// ----------------------------------------------------------------------
// Original Author of file: Frédéric MOHIER
// Purpose of file:
// ----------------------------------------------------------------------

$USEDBREPLICATE = 1;

// Direct access to file
if (strpos($_SERVER['PHP_SELF'],"updatePerfdata.php")) {
   include ("../../../inc/includes.php");
   header("Content-Type: text/html; charset=UTF-8");
   Html::header_nocache();
}
session_write_close();

if (!defined('GLPI_ROOT')) {
   die("Can not acces directly to this file");
}

Session::checkLoginUser();

// Get component graph configuration ...
if(!isset($_SESSION['glpi_plugin_monitoring']['perfname'][$_POST['components_id']])) {
   PluginMonitoringToolbox::loadPreferences($_POST['components_id']);
}

$pmServiceevent = new PluginMonitoringServiceevent();
$counters = array();

$counter_types = array (
   'first'        => __('First value', 'monitoring'),
   'last'         => __('Last value', 'monitoring')
);

$counter_operations = array (
   'difference'   => __('Difference', 'monitoring')
);

foreach ($counter_types as $type => $type_title) {
   if (isset($_POST['debug'])) echo "<pre>Type : $type</pre>";

   $counters[$type] = array();
   $a_ret = $pmServiceevent->getSpecificData($_POST['rrdtool_template'], $_POST['items_id'], $type);
   if (isset($_POST['debug'])) echo "<pre>".print_r($a_ret)."</pre>";
   foreach ($a_ret as $counter) {
      if (isset($_POST['debug'])) echo "<pre>".print_r($a_ret)."</pre>";
      if (isset($_POST['debug'])) echo "<pre>".$counter['id']." (".$counter['name'].") =".$counter['value']."</pre>";
      if (! isset($_SESSION['glpi_plugin_monitoring']['perfname'][$_POST['components_id']][$counter['name']])) continue;

      $counters[$type][$counter['id']] = $counter;
   }
}

foreach ($counter_operations as $type => $type_title) {
   if (isset($_POST['debug'])) echo "<br/><pre>Operation : $type</pre>";

   $counters[$type] = array();

   switch ($type) {
      case 'difference':
         foreach ($counters['first'] as $id => $data) {
            if (isset($_POST['debug'])) echo "<pre>Id : $id - ".$data['name']." = ".$data['value']."</pre>";;

            $counter = array();
            $counter['id'] = $id;
            $counter['name'] = $data['name'];
            $counter['value'] = $counters['last'][$id]['value'] - $counters['first'][$id]['value'];
            $counters[$type][$id] = $counter;
         }
         break;
      default :
         break;
   }
}

if (isset($_POST['debug'])) echo "<pre>Found counters : ".print_r($counters)."</pre>";

if (isset($_POST['counter_id']) && (! empty($_POST['counter_id']))) {
   $hdr_types = "";
   $row_counter = "";
   foreach ($counters as $type => $typeCounters) {
      if (isset($_POST['debug'])) echo "<pre>Counter type '$type' : ".print_r($typeCounters)."</pre>";

      if (array_key_exists($type, $counter_types)) $hdr_types .= "<th class='center'>".$counter_types[$type]."</th>";
      if (array_key_exists($type, $counter_operations)) $hdr_types .= "<th class='center'>".$counter_operations[$type]."</th>";

      foreach ($typeCounters as $id => $data) {
         if (isset($_POST['debug'])) echo "<pre>Id : $id - ".$data['name']." = ".$data['value']."</pre>";;
         if ($id != $_POST['counter_id']) continue;

         $hdr_counter = $data['name'];
         $row_counter .= "<td counter='".$_POST['counter_id']."' counterType='".$type."' class='localCounter center'>".$data['value']."</th>";
      }
   }
   if (isset($hdr_counter)) {
      echo "<table class='tab_cadrehov'>";
      echo "<tr class='tab_bg_1'><th colspan='".count($counters)."' counterId='".$_POST['counter_id']."' counterName='".$hdr_counter."' class='counterId center'>$hdr_counter</th></tr>";
      echo "<tr class='tab_bg_1'>$hdr_types</tr>";
      echo "<tr class='tab_bg_2'>$row_counter</tr>";
      echo "</table>";
   } else {
      echo "&nbsp;";
   }
} else {
   if (isset($_POST['json']) && ($_POST['json']=='1')) {
      echo json_encode($counters);
   } else {
      echo "<table class='tab_cadrehov'>";

      echo "<tr class='tab_bg_1'><th colspan='2'>".__('Counters : registered values', 'monitoring')."</th></tr>";
      foreach ($counters as $type => $typeCounters) {
         if (isset($_POST['debug'])) echo "<pre>Counter type '$type' : ".print_r($typeCounters)."</pre>";

         if (array_key_exists($type, $counter_types))
            echo "<tr class='tab_bg_1'><th colspan='2' class='left'>".$counter_types[$type]."</th></tr>";
         if (array_key_exists($type, $counter_operations))
            echo "<tr class='tab_bg_1'><th colspan='2' class='left'>".$counter_operations[$type]."</th></tr>";

         foreach ($typeCounters as $id => $data) {
            if (isset($_POST['debug'])) echo "<pre>Id : $id - ".$data['name']." = ".$data['value']."</pre>";;

            echo "<tr class='tab_bg_3'><td class='left'>".$data['name']."</td><td class='center'>".$data['value']."</td></tr>";
         }
      }

      echo "</table>";
   }
}
?>