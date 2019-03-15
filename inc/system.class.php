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

class PluginMonitoringSystem extends CommonDBTM
{
    const HOMEPAGE = 1024;
    const DASHBOARD = 2048;

    static $rightname = 'plugin_monitoring_system_status';


    function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {

        if (!$withtemplate) {
            switch ($item->getType()) {
                case 'Central' :
                    if (Session::haveRight("plugin_monitoring_central", READ)
                        and Session::haveRight("plugin_monitoring_system_status", PluginMonitoringProfile::HOMEPAGE)) {
                        return [1 => __('System status', 'monitoring')];
                    } else {
                        return '';
                    }
            }
        }
        return '';
    }


    static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {

        switch ($item->getType()) {
            case 'Central' :
                echo "<table class='tab_cadre' width='950'>";
                echo "<tr class='tab_bg_1'>";
                echo "<th height='80'>";
                PluginMonitoringTag::getServersStatus(true);

                echo "</th>";
                echo "</tr>";
                echo "</table>";

                return true;

        }
        return true;
    }


    /**
     * @since version 0.85
     *
     * @see   commonDBTM::getRights()
     *
     * @param string $interface
     *
     * @return array
     */
    function getRights($interface = 'central')
    {

        $values = [];
        $values[self::HOMEPAGE] = __('See in homepage', 'monitoring');
        $values[self::DASHBOARD] = __('See in dashboard', 'monitoring');

        return $values;
    }
}
