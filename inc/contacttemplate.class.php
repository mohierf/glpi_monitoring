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

class PluginMonitoringContacttemplate extends CommonDBTM
{
    static $rightname = 'plugin_monitoring_notification';

    // Prefix to use when commands are built for Shinken configuration
    public static $default_template = 'Default notifications';

    /**
     * Initialization called on plugin installation
     * @param Migration $migration
     */
    function initialize($migration)
    {
        $notification_period = -1;
        $calendar = new Calendar();
        if ($calendar->getFromDBByCrit(['name' => "24x7"])) {
            $notification_period = $calendar->getID();
        }

        $cmd_host_notification = -1;
        $pmCommand = new PluginMonitoringNotificationcommand();
        if ($pmCommand->getFromDBByCrit(["command_name" => "notify-host-by-log"])) {
            $cmd_host_notification = $pmCommand->getID();
        }

        $cmd_service_notification = -1;
        $pmCommand = new PluginMonitoringNotificationcommand();
        if ($pmCommand->getFromDBByCrit(["command_name" => "notify-service-by-log"])) {
            $cmd_service_notification = $pmCommand->getID();
        }

        $input = [];
        $input['name'] = self::$default_template;
        $input['is_default'] = '1';

        // Default is not administrator but allowed to raise commands
        $input['ui_administrator'] = '0';
        $input['ui_can_submit_commands'] = '1';

        // Default are disabled
        $input['hn_enabled'] = '0';
        $input['hn_period'] = $notification_period;
        $input['hn_commands'] = $cmd_host_notification;

        $input['sn_enabled'] = '0';
        $input['sn_period'] = $notification_period;
        $input['sn_commands'] = $cmd_service_notification;
        $this->add($input);

        $migration->displayMessage("  created default contact template");
    }

    static function getTypeName($nb = 0)
    {
        return __('Contact templates', 'monitoring');
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
            'name' => __('Contact templates', 'monitoring')
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
            'id' => $index,
            'table' => $this->getTable(),
            'field' => 'is_default',
            'name' => __('Is default'),
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

        $this->initForm($items_id, $options);
        $this->showFormHeader($options);

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Name') . "&nbsp;:</td>";
        echo "<td align='center'>";

        $objectName = autoName($this->fields["name"], "name", false,
            $this->getType());
        Html::autocompletionTextField($this, 'name', array('value' => $objectName));
        echo "</td>";
        echo "<td>" . __('Default template', 'monitoring') . "&nbsp;:</td>";
        echo "<td align='center'>";
        Dropdown::showYesNo("is_default", $this->fields['is_default']);
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<th colspan='4'>" . __('Contact configuration for Shinken WebUI', 'monitoring') . "</th>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Contact has Shinken administrator rights', 'monitoring') . "&nbsp;:</td>";
        echo "<td align='center'>";
        Dropdown::showYesNo('ui_administrator', $this->fields['ui_administrator']);
        echo "</td>";
        echo "<td>" . __('Contact can submit Shinken commands', 'monitoring') . "&nbsp;:</td>";
        echo "<td align='center'>";
        Dropdown::showYesNo('ui_can_submit_commands', $this->fields['ui_can_submit_commands']);
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<th colspan='2'>" . __('Hosts', 'monitoring') . "</th>";
        echo "<th colspan='2'>" . __('Services', 'monitoring') . "</th>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Notifications', 'monitoring') . "&nbsp;:</td>";
        echo "<td align='center'>";
        Dropdown::showYesNo('hn_enabled', $this->fields['hn_enabled']);
        echo "</td>";
        echo "<td>" . __('Notifications', 'monitoring') . "&nbsp;:</td>";
        echo "<td align='center'>";
        Dropdown::showYesNo('sn_enabled', $this->fields['sn_enabled']);
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Notification command', 'monitoring') . "&nbsp;:</td>";
        echo "<td align='center'>";
        dropdown::show("PluginMonitoringNotificationcommand", array('name' => 'hn_commands',
            'value' => $this->fields['hn_commands']));
        echo "</td>";
        echo "<td>" . __('Notification command', 'monitoring') . "&nbsp;:</td>";
        echo "<td align='center'>";
        dropdown::show("PluginMonitoringNotificationcommand", array('name' => 'sn_commands',
            'value' => $this->fields['sn_commands']));
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Period', 'monitoring') . "&nbsp;:</td>";
        echo "<td align='center'>";
        dropdown::show("Calendar", array('name' => 'hn_period',
            'value' => $this->fields['hn_period']));
        echo "</td>";
        echo "<td>" . __('Period', 'monitoring') . "&nbsp;:</td>";
        echo "<td align='center'>";
        dropdown::show("Calendar", array('name' => 'sn_period',
            'value' => $this->fields['sn_period']));
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Notify on DOWN host states', 'monitoring') . "&nbsp;:</td>";
        echo "<td align='center'>";
        Dropdown::showYesNo('hn_options_d', $this->fields['hn_options_d']);
        echo "</td>";
        echo "<td>" . __('Notify on WARNING service states', 'monitoring') . "&nbsp;:</td>";
        echo "<td align='center'>";
        Dropdown::showYesNo('sn_options_w', $this->fields['sn_options_w']);
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Notify on UNREACHABLE host states', 'monitoring') . "&nbsp;:</td>";
        echo "<td align='center'>";
        Dropdown::showYesNo('hn_options_u', $this->fields['hn_options_u']);
        echo "</td>";
        echo "<td>" . __('Notify on UNKNOWN service states', 'monitoring') . "&nbsp;:</td>";
        echo "<td align='center'>";
        Dropdown::showYesNo('sn_options_u', $this->fields['sn_options_u']);
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Notify on host recoveries (UP states)', 'monitoring') . "&nbsp;:</td>";
        echo "<td align='center'>";
        Dropdown::showYesNo('hn_options_r', $this->fields['hn_options_r']);
        echo "</td>";
        echo "<td>" . __('Notify on CRITICAL service states', 'monitoring') . "&nbsp;:</td>";
        echo "<td align='center'>";
        Dropdown::showYesNo('sn_options_c', $this->fields['sn_options_c']);
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Notify when the host starts and stops flapping', 'monitoring') . "&nbsp;:</td>";
        echo "<td align='center'>";
        Dropdown::showYesNo('hn_options_f', $this->fields['hn_options_f']);
        echo "</td>";
        echo "<td>" . __('Notify on service recoveries (OK states)', 'monitoring') . "&nbsp;:</td>";
        echo "<td align='center'>";
        Dropdown::showYesNo('sn_options_r', $this->fields['sn_options_r']);
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Send notifications when host or service scheduled downtime starts and ends', 'monitoring') . "&nbsp;:</td>";
        echo "<td align='center'>";
        Dropdown::showYesNo('hn_options_s', $this->fields['hn_options_s']);
        echo "</td>";
        echo "<td>" . __('Notify when the service starts and stops flapping', 'monitoring') . "&nbsp;:</td>";
        echo "<td align='center'>";
        Dropdown::showYesNo('sn_options_f', $this->fields['sn_options_f']);
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td></td>";
        echo "<td align='center'>";
        echo "</td>";
        echo "<td>" . __('Notify when service scheduled downtime starts and ends', 'monitoring') . "&nbsp;:</td>";
        echo "<td align='center'>";
        Dropdown::showYesNo('sn_options_s', $this->fields['sn_options_s']);
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('The contact will not receive any type of host notifications', 'monitoring') . "&nbsp;:</td>";
        echo "<td align='center'>";
        Dropdown::showYesNo('hn_options_n', $this->fields['hn_options_n']);
        echo "</td>";
        echo "<td>" . __('The contact will not receive any type of service notifications', 'monitoring') . "&nbsp;:</td>";
        echo "<td align='center'>";
        Dropdown::showYesNo('sn_options_n', $this->fields['sn_options_n']);
        echo "</td>";
        echo "</tr>";

        $this->showFormButtons($options);

        return true;
    }
}
