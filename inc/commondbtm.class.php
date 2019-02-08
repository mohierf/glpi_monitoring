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
   @since     2016

   ------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginMonitoringCommonDBTM extends CommonDBTM {


   /** Display item with tabs
    *
    * @since version 0.85
    *
    * @param $options   array
   **/
   function display($options=array()) {
      if (PLUGIN_MONITORING_SYSTEM == 'shinken') {
         parent::display($options);
      } else {
         if (isset($options['id'])) {
            $pma = new PluginMonitoringAlignak();
            $data = $pma->getID($options['id']);
            if (!$data['_id']) {
               Html::displayNotFoundError();
            }
            $this->fields = $data;
            $this->fields['id'] = 0;
         }

         $this->showNavigationHeader($options);
         if (!self::isLayoutExcludedPage() && self::isLayoutWithMain()) {

            if (!isset($options['id'])) {
               $options['id'] = 0;
            }
            $this->showPrimaryForm($options);
         }

         $this->showTabsContent($options);

      }
   }


}

