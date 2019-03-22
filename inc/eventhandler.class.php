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

class PluginMonitoringEventhandler extends CommonDBTM
{

    static $rightname = 'plugin_monitoring_command';


    static function getTypeName($nb = 0)
    {
        return __('Event handler', 'monitoring');
    }


    function rawSearchOptions()
    {

        $tab = [];

        $tab[] = [
            'id' => 'common',
            'name' => __('Event handlers', 'monitoring')
        ];

        $index = 1;
        $tab[] = [
            'id' => $index++,
            'table' => $this->getTable(),
            'field' => 'name',
            'name' => __('Name'),
            'datatype' => 'itemlink'
        ];

        $tab[] = [
            'id' => $index++,
            'table' => $this->getTable(),
            'field' => 'is_active',
            'name' => __('Is active'),
            'datatype' => 'bool'
        ];

        $tab[] = [
            'id' => $index++,
            'table' => $this->getTable(),
            'field' => 'command_name',
            'name' => __('Command name'),
        ];

        $tab[] = [
            'id' => $index,
            'table' => $this->getTable(),
            'field' => 'command_line',
            'name' => __('Command line'),
        ];

        /*
         * Include other fields here
         */

        $tab[] = [
            'id' => '99',
            'table' => $this->getTable(),
            'field' => 'id',
            'name' => __('ID'),
            'usehaving' => true,
            'searchtype' => 'equals',
        ];

        return $tab;
    }


//    function defineTabs($options = array())
//    {
//        $ong = array();
//        $this->addDefaultFormTab($ong);
//        return $ong;
//    }


    function showForm($items_id, $options = array(), $copy = array())
    {

        if (count($copy) > 0) {
            foreach ($copy as $key => $value) {
                $this->fields[$key] = stripslashes($value);
            }
        }

        $this->initForm($items_id, $options);
        if ($this->fields['id'] == 0) {
            $this->fields['command_line'] = '/usr/local/eventhandler/command.sh $SERVICESTATE$ '
                . '$SERVICESTATETYPE$ $SERVICEATTEMPT$ $HOSTADDRESS$';
        }
        $this->showFormHeader($options);


        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Name') . " :</td>";
        echo "<td>";
        echo "<input type='text' name='name' value='" . $this->fields["name"] . "' size='30'/>";
        echo "</td>";
        echo "<td>" . __('Command name', 'monitoring') . "&nbsp;:</td>";
        echo "<td>";
        echo "<input type='text' name='command_name' value='" . $this->fields["command_name"] . "' size='30'/>";
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Active ?', 'monitoring') . "</td>";
        echo "<td>";
        if (self::canCreate()) {
            Dropdown::showYesNo('is_active', $this->fields['is_active']);
        } else {
            echo Dropdown::getYesNo($this->fields['is_active']);
        }
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Comment', 'monitoring') . "</td>";
        echo "<td >";
        echo "<textarea cols='80' rows='4' name='comment' >" . $this->fields['comment'] . "</textarea>";
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Command line', 'monitoring') . "&nbsp;:</td>";
        echo "<td colspan='3'>";
        echo "<input type='text' name='command_line' value='" . $this->fields["command_line"] . "' size='97'/>";
        echo "</td>";
        echo "</tr>";

        $this->showFormButtons($options);

        return true;
    }
}
