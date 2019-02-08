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

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginMonitoringNetworkport extends CommonDBTM {

   static $rightname = 'plugin_monitoring_componentscatalog';

    /**
     * Get name of this type
     *
     * @param int $nb
     * @return string name of this type by language of the user connected
     *
     */
   static function getTypeName($nb=0) {
      return __('Network ports of networking devices', 'monitoring');
   }



   function getSearchOptions() {
      $tab = array();

      $tab['common'] = __('Network ports of networking devices', 'monitoring');

      $tab[1]['table']         = $this->getTable();
      $tab[1]['field']         = 'id';
      $tab[1]['name']          = __('ID');
      $tab[1]['massiveaction'] = false; // implicit field is id

      $tab[2]['table'] = 'glpi_networkports';
      $tab[2]['field'] = 'name';
      $tab[2]['name']  = __('Network port');

      $tab['networkequipment'] = __('Networking device');

      $tab[3]['table']         = 'glpi_networkequipments';
      $tab[3]['field']         = 'name';
      $tab[3]['name']          = __('Name');
      $tab[3]['forcegroupby']  = true;

      $tab[4]['table']         = 'glpi_states';
      $tab[4]['field']         = 'name';
      $tab[4]['name']          = __('Status');
      $tab[4]['forcegroupby']  = true;

      return $tab;
   }



   static function isMonitoredNetworkport($networkports_id) {
      $nb = countElementsInTable("glpi_plugin_monitoring_networkports",
              "`networkports_id` = '".$networkports_id."'");
      if ($nb > 0) {
         return true;
      }
      return false;
   }



   function updateNetworkports() {
      global $DB;

      // Get all networkports in DB
      $networkportInDB = array();
      $query = "SELECT * FROM `".$this->getTable()."`
         WHERE `itemtype`='".$_POST['itemtype']."'
            AND `items_id`='".$_POST['items_id']."'";
      $result = $DB->query($query);
      while ($data=$DB->fetch_array($result)) {
         $networkportInDB[$data['networkports_id']] = $data['id'];
      }

      if (isset($_POST['networkports_id'])) {
         foreach ($_POST['networkports_id'] as $networkports_id) {
            if (isset($networkportInDB[$networkports_id])) {
               unset($networkportInDB[$networkports_id]);
            } else {
               $input = array();
               $input['itemtype'] = $_POST['itemtype'];
               $input['items_id'] = $_POST['items_id'];
               $input['networkports_id'] = $networkports_id;
               $this->add($input);
            }
         }
      }
      // Remove old
      foreach ($networkportInDB as $id) {
         $this->delete(array('id'=>$id));
      }
   }



   static function deleteNetworkPort($parm) {
      global $DB;

      if ($parm->fields['itemtype'] == 'NetworkEquipment') {
         $query = "SELECT * FROM `glpi_plugin_monitoring_networkports`
            WHERE `networkports_id`='".$parm->fields['id']."'";
         $result = $DB->query($query);
         while ($data=$DB->fetch_array($result)) {
            $pmNetworkPort = new PluginMonitoringNetworkport();
            $pmNetworkPort->delete($data);
         }
      }
   }
}

