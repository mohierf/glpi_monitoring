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

class PluginMonitoringServicenotificationtemplate extends CommonDBTM
{
    static $rightname = 'plugin_monitoring_notification';

    /**
     * Initialization called on plugin installation
     */
    function initialize()
    {
        $check_period = -1;
        $calendar = new Calendar();
        if ($calendar->getFromDBByCrit(['name' => "24x7"])) {
            $check_period = $calendar->getID();
        }

        $input = [];
        $input['name'] = 'Default service notification';
        $input['sn_enabled'] = '0';
        $input['sn_period'] = $check_period;
        $this->add($input);
    }


    static function getTypeName($nb = 0)
    {
        return __('Services notifications templates', 'monitoring');
    }


    /*
     * Search options, see: https://glpi-developer-documentation.readthedocs.io/en/master/devapi/search.html#search-options
     */
    public function getSearchOptionsNew()
    {
        return $this->rawSearchOptions();
    }

    function rawSearchOptions()
    {

        $tab = [];

        $tab[] = [
            'id' => 'common',
            'name' => __('Services notification templates', 'monitoring')
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
            'field' => 'sn_enabled',
            'name' => __('Enabled/disabled', 'monitoring'),
            'datatype' => 'bool'
        ];

        $tab[] = [
            'id' => $index++,
            'table' => $this->getTable(),
            'field' => 'sn_period',
            'name' => __('Notification period'),
            'datatype' => 'specific'
        ];

        $tab[] = [
            'id' => $index++,
            'table' => $this->getTable(),
            'field' => 'sn_options_n',
            'name' => __('No notification', 'monitoring'),
            'datatype' => 'bool'
        ];

        $tab[] = [
            'id' => $index++,
            'table' => $this->getTable(),
            'field' => 'sn_options_c',
            'name' => __('Service critical', 'monitoring'),
            'datatype' => 'bool'
        ];

        $tab[] = [
            'id' => $index++,
            'table' => $this->getTable(),
            'field' => 'sn_options_w',
            'name' => __('Service warning', 'monitoring'),
            'datatype' => 'bool'
        ];

        $tab[] = [
            'id' => $index++,
            'table' => $this->getTable(),
            'field' => 'sn_options_u',
            'name' => __('Service unknown', 'monitoring'),
            'datatype' => 'bool'
        ];

        $tab[] = [
            'id' => $index++,
            'table' => $this->getTable(),
            'field' => 'sn_options_x',
            'name' => __('Service unreachable', 'monitoring'),
            'datatype' => 'bool'
        ];

        $tab[] = [
            'id' => $index++,
            'table' => $this->getTable(),
            'field' => 'sn_options_r',
            'name' => __('Service recovery', 'monitoring'),
            'datatype' => 'bool'
        ];

        $tab[] = [
            'id' => $index++,
            'table' => $this->getTable(),
            'field' => 'sn_options_f',
            'name' => __('Service flapping', 'monitoring'),
            'datatype' => 'bool'
        ];

        $tab[] = [
            'id' => $index,
            'table' => $this->getTable(),
            'field' => 'sn_options_s',
            'name' => __('Service acknowledge / downtime', 'monitoring'),
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
            case 'sn_period':
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

//        $this->showTabs($options);
        $this->showFormHeader($options);

        $this->getFromDB($items_id);

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Name') . "&nbsp;:</td>";
        echo "<td align='center'>";

        $objectName = autoName($this->fields["name"], "name", false,
            $this->getType());
        Html::autocompletionTextField($this, 'name', array('value' => $objectName));
        echo "</td>";
        echo "<td>" . __('Default template', 'monitoring') . "&nbsp;:</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<th colspan='2'>" . __('Services', 'monitoring') . "</th>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Notifications', 'monitoring') . "&nbsp;:</td>";
        echo "<td align='center'>";
        Dropdown::showYesNo('sn_enabled', $this->fields['sn_enabled']);
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Period', 'monitoring') . "&nbsp;:</td>";
        echo "<td align='center'>";
        dropdown::show("Calendar", array('name' => 'sn_period',
            'value' => $this->fields['sn_period']));
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Notify on WARNING service states', 'monitoring') . "&nbsp;:</td>";
        echo "<td align='center'>";
        Dropdown::showYesNo('sn_options_w', $this->fields['sn_options_w']);
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Notify on UNKNOWN service states', 'monitoring') . "&nbsp;:</td>";
        echo "<td align='center'>";
        Dropdown::showYesNo('sn_options_u', $this->fields['sn_options_u']);
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Notify on CRITICAL service states', 'monitoring') . "&nbsp;:</td>";
        echo "<td align='center'>";
        Dropdown::showYesNo('sn_options_c', $this->fields['sn_options_c']);
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Notify on service recoveries (OK states)', 'monitoring') . "&nbsp;:</td>";
        echo "<td align='center'>";
        Dropdown::showYesNo('sn_options_r', $this->fields['sn_options_r']);
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Notify when the service starts and stops flapping', 'monitoring') . "&nbsp;:</td>";
        echo "<td align='center'>";
        Dropdown::showYesNo('sn_options_f', $this->fields['sn_options_f']);
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Notify when service scheduled downtime starts and ends', 'monitoring') . "&nbsp;:</td>";
        echo "<td align='center'>";
        Dropdown::showYesNo('sn_options_s', $this->fields['sn_options_s']);
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('The contact will not receive any type of service notifications', 'monitoring') . "&nbsp;:</td>";
        echo "<td align='center'>";
        Dropdown::showYesNo('sn_options_n', $this->fields['sn_options_n']);
        echo "</td>";
        echo "</tr>";

        $this->showFormButtons($options);

        return true;
    }
}
