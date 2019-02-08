<?php

/*
   ------------------------------------------------------------------------
   Plugin Monitoring for GLPI
   Copyright (C) 2011-2016 by the Plugin Monitoring for GLPI Development Team.

   https://forge.indepnet.net/projects/monitoring/
   ------------------------------------------------------------------------

   LICENSE

   This file is part of Plugin Monitoring project.

   Plugin Monitoring for GLPI is free software: you can redistribute it and/or modify
   it under the terms of the GNU Affero General Public License as published by
   the Free Software Foundation, either version 3 of the License, or
   (at your option) any later version.

   Plugin Monitoring for GLPI is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
   GNU Affero General Public License for more details.

   You should have received a copy of the GNU Affero General Public License
   along with Monitoring. If not, see <http://www.gnu.org/licenses/>.

   ------------------------------------------------------------------------

   @package   Plugin Monitoring for GLPI
   @author    David Durieux
   @co-author
   @comment
   @copyright Copyright (c) 2011-2016 Plugin Monitoring for GLPI team
   @license   AGPL License 3.0 or (at your option) any later version
              http://www.gnu.org/licenses/agpl-3.0-standalone.html
   @link      https://forge.indepnet.net/projects/monitoring/
   @since     2014

   ------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginMonitoringDashboard extends CommonGLPI {

   static $rightname = 'plugin_monitoring_dashboard';

    /**
     * Get name of this type
     *
     * @param int $nb
     * @return string name of this type by language of the user connected
     *
     */
   static function getTypeName($nb=0) {
      return 'Monitoring';
   }



   static function getAdditionalMenuOptions() {
      global $CFG_GLPI;

      $main_menu = '<img src="'.$CFG_GLPI["root_doc"].'/plugins/monitoring/pics/main_menu.png" alt="'.
                                     __('Main menu', 'monitoring').'" title="'.__('Main menu', 'monitoring').'"\>';

      return array(
         'componentscatalog' => array(
              'title' => PluginMonitoringComponentscatalog::getTypeName(),
              'page'  => PluginMonitoringComponentscatalog::getSearchURL(false),
              'links' => array(
                  $main_menu => '/plugins/monitoring/front/menu.php',
                  'search' => '/plugins/monitoring/front/componentscatalog.php',
                  'add'    => '/plugins/monitoring/front/componentscatalog.form.php'
              )),
         'perfdata' => array(
              'title' => PluginMonitoringPerfdata::getTypeName(),
              'page'  => PluginMonitoringPerfdata::getSearchURL(false),
              'links' => array(
                  $main_menu => '/plugins/monitoring/front/menu.php',
                  'search' => '/plugins/monitoring/front/perfdata.php',
                  'add'    => '/plugins/monitoring/front/perfdata.form.php'
              )),
         'realm' => array(
              'title' => PluginMonitoringRealm::getTypeName(),
              'page'  => PluginMonitoringRealm::getSearchURL(false),
              'links' => array(
                  $main_menu => '/plugins/monitoring/front/menu.php',
                  'search' => '/plugins/monitoring/front/realm.php',
                  'add'    => '/plugins/monitoring/front/realm.form.php'
              )),
         'tag' => array(
              'title' => PluginMonitoringTag::getTypeName(),
              'page'  => PluginMonitoringTag::getSearchURL(false),
              'links' => array(
                  $main_menu => '/plugins/monitoring/front/menu.php',
                  'search' => '/plugins/monitoring/front/tag.php'
              )),
         'weathermap' => array(
              'title' => PluginMonitoringWeathermap::getTypeName(),
              'page'  => PluginMonitoringWeathermap::getSearchURL(false),
              'links' => array(
                  $main_menu => '/plugins/monitoring/front/menu.php',
                  'search' => '/plugins/monitoring/front/weathermap.php',
                  'add'    => '/plugins/monitoring/front/weathermap.form.php'
              )),
         'displayview' => array(
              'title' => PluginMonitoringDisplayview::getTypeName(),
              'page'  => PluginMonitoringDisplayview::getSearchURL(false),
              'links' => array(
                  'search' => '/plugins/monitoring/front/displayview.php',
                  'add'    => '/plugins/monitoring/front/displayview.form.php'
              )),
         'menu' => array(
              'title' => PluginMonitoringMenu::getTypeName(),
              'links' => array(
                  $main_menu => '/plugins/monitoring/front/menu.php',
                  'config' => '/plugins/monitoring/front/config.form.php'
              )),
         'dashboard' => array(
              'title' => PluginMonitoringDisplay::getTypeName(),
              'links' => array(
                  $main_menu => '/plugins/monitoring/front/menu.php',
                  'config'   => '/plugins/monitoring/front/config.form.php'
              )),
         'config' => array(
              'title' => PluginMonitoringConfig::getTypeName(),
              'links' => array(
                  $main_menu => '/plugins/monitoring/front/menu.php'
              ))

         );
   }

}