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

class PluginMonitoringLog extends CommonDBTM {


   static $rightname = 'config';

    /**
     * Get name of this type
     *
     * @param int $nb
     * @return string name of this type by language of the user connected
     *
     */
   static function getTypeName($nb=0) {
      return __('Logs', 'monitoring');
   }



   static function cronInfo($name){

      switch ($name) {
         case 'cleanlogs':
            return array (
               'description' => __('Clean monitoring logs','monitoring'));
            break;
      }
      return array();
   }


   static function cronCleanlogs() {
      global $DB;

      $pmLog      = new PluginMonitoringLog();
      $pmConfig   = new PluginMonitoringConfig();

      $id_restart = 0;
      $a_restarts = $pmLog->find("`action`='restart'", "`id` DESC", 1);
      if (count($a_restarts) > 0) {
         $a_restart = current($a_restarts);
         $id_restart = $a_restart['id'];
      }
      $id_reload = 0;
      $a_reloads = $pmLog->find("`action`='reload'", "`id` DESC", 1);
      if (count($a_reloads) > 0) {
         $a_reload = current($a_reloads);
         $id_reload = $a_reload['id'];
      }

      $pmConfig->getFromDB(1);
      $seconds = $pmConfig->fields['log_retention'] * DAY_TIMESTAMP;
      $query = "DELETE FROM `".$pmLog->getTable()."` WHERE UNIX_TIMESTAMP(date_mod) < UNIX_TIMESTAMP() - $seconds";
      if (($id_restart > 0) || ($id_reload > 0)) {
         // Keep last reload or restart command
         $id_restart = max($id_restart, $id_reload);
         $query .= " AND `id` < '".$id_restart."'";
      }
      $DB->query($query);

      // TODO: Delete serviceevents table content ???
      $query = "DELETE FROM `glpi_plugin_monitoring_serviceevents`
         WHERE UNIX_TIMESTAMP(date) < UNIX_TIMESTAMP()-$seconds";
      $DB->query($query);

      return true;
   }



   function isRestartRecent($delay=1200) {
      $date5min = date("Y-m-d H:i:s", (date("U") - $delay))."\n";
      $a_restarts = $this->find("(`action`='restart' OR `action`='restart_planned') "
              . "AND `date_mod` > '".$date5min."'", "`id` DESC", 1);
      if (count($a_restarts) > 0) {
         return true;
      }
      return false;
   }

   function isReloadRecent($delay=1200) {
      $date5min = date("Y-m-d H:i:s", (date("U") - $delay))."\n";
      $a_restarts = $this->find("(`action`='reload' OR `action`='reload_planned') "
              . "AND `date_mod` > '".$date5min."'", "`id` DESC", 1);
      if (count($a_restarts) > 0) {
         return true;
      }
      return false;
   }

}

