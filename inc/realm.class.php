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

class PluginMonitoringRealm extends CommonDropdown
{
    public $display_dropdowntitle  = false;

    public $first_level_menu = "plugins";
    public $second_level_menu = "pluginmonitoringmenu";
    public $third_level_menu = "realm";

    static $rightname = 'plugin_monitoring_realm';


    /**
     * Initialization called on plugin installation
     * @param Migration $migration
     */
    function initialize($migration)
    {
        // Default realm All
        $input = [];
        $input['name'] = "All";
        $input['comment'] = __("Default realm", 'monitoring');
        $input['is_default'] = "1";
        $this->add($input);
        $migration->displayMessage("  created realm All");
    }


    static function getTypeName($nb = 0)
    {
        return _n('Realm', 'Realms', $nb, 'monitoring');
    }


    function getAdditionalFields()
    {
        return [
            [
                'name' => 'is_default',
                'label' => __('Default realm', 'monitoring'),
                'type' => 'bool'
            ]
        ];
    }


    function prepareInputForAdd($input)
    {
        $input['name'] = preg_replace("/[^A-Za-z0-9]/", "", $input['name']);
        return $input;
    }


    function prepareInputForUpdate($input)
    {
        $input['name'] = preg_replace("/[^A-Za-z0-9]/", "", $input['name']);
        return $input;
    }
}
