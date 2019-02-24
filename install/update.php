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

/**
 * @param $version
 * @return string
 */
function pluginMonitoringGetCurrentVersion()
{
    global $DB;

    $version = '';
    if (!$DB->tableExists("glpi_plugin_monitoring_configs")) {
        // Not yet installed !
        $version = '0';
    } else if ($DB->fieldExists("glpi_plugin_monitoring_configs", "version")) {
        $query = "SELECT `version` FROM `glpi_plugin_monitoring_configs` WHERE `id` = '1'";
        $result = $DB->query($query);
        if ($DB->numrows($result) > 0) {
            $data = $DB->fetch_assoc($result);
            if (!is_null($data['version']) and !empty($data['version'])) {
                $version = $data['version'];
            }
        }
    }
    return $version;
}
