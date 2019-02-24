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

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginMonitoringDisplayview_User extends CommonDBRelation {

   // From CommonDBRelation
   static public $itemtype_1          = 'PluginMonitoringDisplayview';
   static public $items_id_1          = 'pluginmonitoringdisplayviews_id';
   static public $itemtype_2          = 'User';
   static public $items_id_2          = 'users_id';

   static public $checkItem_2_Rights  = self::DONT_CHECK_ITEM_RIGHTS;
   static public $logs_for_item_2     = false;


   /**
    * Get users for a reminder
    *
    * @param integer $reminders_id ID of the reminder
    *
    * @return array of users linked to a reminder
   **/
   static function getUsers($reminders_id) {
      global $DB;

      $users = array();
      $query = "SELECT `glpi_plugin_monitoring_displayviews_users`.*
                FROM `glpi_plugin_monitoring_displayviews_users`
                WHERE `pluginmonitoringdisplayviews_id` = '$reminders_id'";

      foreach ($DB->request($query) as $data) {
         $users[$data['users_id']][] = $data;
      }
      return $users;
   }

}
