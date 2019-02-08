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
   @since     2011

   ------------------------------------------------------------------------
 */

/**
 * @param $version
 * @return string
 */
function pluginMonitoringGetCurrentVersion($version)
{
    global $DB;

    if (!$DB->tableExists("glpi_plugin_monitoring_configs")) {
        // Not yet installed !
        $version = '0';
    } else if (!$DB->fieldExists("glpi_plugin_monitoring_configs", "timezones")) {
        // Old versions...
        $version = "old";
    } else if (!$DB->fieldExists("glpi_plugin_monitoring_configs", "version")) {
        $version = "0.80+1.0";
    } else if ($DB->fieldExists("glpi_plugin_monitoring_configs", "version")) {
        $query = "SELECT `version` FROM `glpi_plugin_monitoring_configs` WHERE `id` = '1'";
        $result = $DB->query($query);
        if ($DB->numrows($result) > 0) {
            $data = $DB->fetch_assoc($result);
            if (is_null($data['version'])
                || $data['version'] == '') {
                $data['version'] = '0.80+1.0';
            }
            if ($data['version'] != $version) {
                return $data['version'];
            }
        } else {
            return "0.80+1.0";
        }
    } else {
        return $version;
    }
    return $version;
}
