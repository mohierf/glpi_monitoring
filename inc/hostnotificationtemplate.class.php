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

class PluginMonitoringHostnotificationtemplate extends CommonDBTM
{
    static $rightname = 'plugin_monitoring_notification';

    /**
     * Initialization called on plugin installation
     * @param Migration $migration
     */
    function initialize($migration)

    {
        $hn_period = -1;
        $calendar = new Calendar();
        if ($calendar->getFromDBByCrit(['name' => "24x7"])) {
            $hn_period = $calendar->getID();
        }

        $input = [];
        $input['name'] = 'Default host notification';
        $input['hn_enabled'] = '0';
        $input['hn_period'] = $hn_period;
        $this->add($input);

        $migration->displayMessage("  created default host notification template");
    }

    static function getTypeName($nb = 0)
    {
        return __('Hosts notification templates', 'monitoring');
    }


    function rawSearchOptions()
    {

        $tab = [];

        $tab[] = [
            'id' => 'common',
            'name' => __('Hosts notification templates', 'monitoring')
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
            'field' => 'hn_enabled',
            'name' => __('Enabled/disabled', 'monitoring'),
            'datatype' => 'bool'
        ];

        $tab[] = [
            'id' => $index++,
            'table' => $this->getTable(),
            'field' => 'hn_period',
            'name' => __('Notification period'),
            'datatype' => 'specific'
        ];

        $tab[] = [
            'id' => $index++,
            'table' => $this->getTable(),
            'field' => 'hn_options_n',
            'name' => __('No notification', 'monitoring'),
            'datatype' => 'bool'
        ];

        $tab[] = [
            'id' => $index++,
            'table' => $this->getTable(),
            'field' => 'hn_options_d',
            'name' => __('Host down', 'monitoring'),
            'datatype' => 'bool'
        ];

        $tab[] = [
            'id' => $index++,
            'table' => $this->getTable(),
            'field' => 'hn_options_u',
            'name' => __('Host unreachable', 'monitoring'),
            'datatype' => 'bool'
        ];

        $tab[] = [
            'id' => $index++,
            'table' => $this->getTable(),
            'field' => 'hn_options_r',
            'name' => __('Host recovery', 'monitoring'),
            'datatype' => 'bool'
        ];

        $tab[] = [
            'id' => $index++,
            'table' => $this->getTable(),
            'field' => 'hn_options_f',
            'name' => __('Host flapping', 'monitoring'),
            'datatype' => 'bool'
        ];

        $tab[] = [
            'id' => $index,
            'table' => $this->getTable(),
            'field' => 'hn_options_s',
            'name' => __('Host acknowledge / downtime', 'monitoring'),
            'datatype' => 'bool'
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


    static function getSpecificValueToDisplay($field, $values, array $options = array())
    {

        if (!is_array($values)) {
            $values = array($field => $values);
        }
        switch ($field) {
            case 'hn_period':
                $calendar = new Calendar();
                $calendar->getFromDB($values[$field]);
                return $calendar->getName(1);
                break;

        }
        return parent::getSpecificValueToDisplay($field, $values, $options);
    }


    function showForm($items_id, $options = array())
    {
        if ($items_id == '') {
            if (isset($_POST['id'])) {
                $a_list = $this->find("`users_id`='" . $_POST['id'] . "'", '', 1);
                if (count($a_list)) {
                    $array = current($a_list);
                    $items_id = $array['id'];
                }
            }
        }

        if ($items_id != '') {
            $this->getFromDB($items_id);
        } else {
            $this->getEmpty();
        }

//      $this->showTabs($options);
        $this->showFormHeader($options);

        $this->getFromDB($items_id);

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Name') . "&nbsp;:</td>";
        echo "<td align='center'>";

        $objectName = autoName($this->fields["name"], "name", false,
            $this->getType());
        Html::autocompletionTextField($this, 'name', array('value' => $objectName));
        echo "</td>";
        echo "<td></td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<th colspan='2'>" . __('Hosts', 'monitoring') . "</th>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Notifications', 'monitoring') . "&nbsp;:</td>";
        echo "<td align='center'>";
        Dropdown::showYesNo('hn_enabled', $this->fields['hn_enabled']);
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Period', 'monitoring') . "&nbsp;:</td>";
        echo "<td align='center'>";
        dropdown::show("Calendar", array('name' => 'hn_period',
            'value' => $this->fields['hn_period']));
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Notify on DOWN host states', 'monitoring') . "&nbsp;:</td>";
        echo "<td align='center'>";
        Dropdown::showYesNo('hn_options_d', $this->fields['hn_options_d']);
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Notify on UNREACHABLE host states', 'monitoring') . "&nbsp;:</td>";
        echo "<td align='center'>";
        Dropdown::showYesNo('hn_options_u', $this->fields['hn_options_u']);
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Notify on host recoveries (UP states)', 'monitoring') . "&nbsp;:</td>";
        echo "<td align='center'>";
        Dropdown::showYesNo('hn_options_r', $this->fields['hn_options_r']);
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Notify when the host starts and stops flapping', 'monitoring') . "&nbsp;:</td>";
        echo "<td align='center'>";
        Dropdown::showYesNo('hn_options_f', $this->fields['hn_options_f']);
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Notify when host scheduled downtime starts and ends', 'monitoring') . "&nbsp;:</td>";
        echo "<td align='center'>";
        Dropdown::showYesNo('hn_options_s', $this->fields['hn_options_s']);
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('The contact will not receive any type of host notifications', 'monitoring') . "&nbsp;:</td>";
        echo "<td align='center'>";
        Dropdown::showYesNo('hn_options_n', $this->fields['hn_options_n']);
        echo "</td>";
        echo "</tr>";

        $this->showFormButtons($options);

        return true;
    }
}