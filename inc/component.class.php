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

class PluginMonitoringComponent extends CommonDBTM
{

    static $rightname = 'plugin_monitoring_component';


    /**
     * Initialization called on plugin installation
     *
     * @param Migration $migration
     */
    function initialize($migration)
    {
        $check_period = -1;
        $calendar = new Calendar();
        if ($calendar->getFromDBByCrit(['name' => "monitoring-default"])) {
            $check_period = $calendar->getID();
        }

        $check_strategy = -1;
        $strategy = new PluginMonitoringCheck();
        if ($strategy->getFromDBByCrit(['name' => "15 minutes / 3 retries"])) {
            $check_strategy = $strategy->getID();
        }

        $check_command = -1;
        $command = new PluginMonitoringCommand();
        if ($command->getFromDBByCrit(['command_name' => "check_host_alive"])) {
            $check_command = $command->getID();
        }

        $input = [];
        $input['name'] = 'Host check (ICMP)';
        $input['description'] = 'host_check';
        $input['active_checks_enabled'] = '1';
        $input['passive_checks_enabled'] = '0';
        $input['plugin_monitoring_commands_id'] = $check_command;
        $input['plugin_monitoring_checks_id'] = $check_strategy;
        $input['calendars_id'] = $check_period;
        $this->add($input);

        $check_command = -1;
        $command = new PluginMonitoringCommand();
        if ($command->getFromDBByCrit(['command_name' => "check_ping"])) {
            $check_command = $command->getID();
        }

        $input = [];
        $input['name'] = 'Host check (ping)';
        $input['description'] = 'host_check';
        $input['active_checks_enabled'] = '1';
        $input['passive_checks_enabled'] = '0';
        $input['plugin_monitoring_commands_id'] = $check_command;
        $input['plugin_monitoring_checks_id'] = $check_strategy;
        $input['calendars_id'] = $check_period;
        $this->add($input);

        $migration->displayMessage("  created default components");
    }


    static function getTypeName($nb = 1)
    {
        return __('Components', 'monitoring');
    }


    function defineTabs($options = [])
    {
        $ong = [];
        $this->addDefaultFormTab($ong);
        $this->addStandardTab("PluginMonitoringComponent", $ong, $options);
        return $ong;
    }


    /**
     * Display tab
     *
     * @param CommonGLPI $item
     * @param integer $withtemplate
     *
     * @return array names of the tab(s) to display
     */
    function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        /* @var CommonDBTM $item */
        if ($item->getID() > 0
            and $item->getField('graph_template') != 0) {

            return [
                __('Copy'),
                __('Components catalog', 'monitoring'),
                __('Graph configuration', 'monitoring')
            ];
        } else if ($item->getID() > 0) {
            return [
                __('Copy'),
                __('Components catalog', 'monitoring')
            ];
        }
        return [];
    }


    /**
     * Display content of tab
     *
     * @param CommonGLPI $item
     * @param integer $tabnum
     * @param integer $withtemplate
     *
     * @return boolean true
     */
    static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        if ($item->getType() == 'PluginMonitoringComponent') {
            /* @var PluginMonitoringComponent $item */
            if ($tabnum == '0') {
                $item->copyItem($item->getID());
            } else if ($tabnum == '1') {
                PluginMonitoringComponentscatalog_Component::listForComponents($item->getID());
            }
        }
        return true;
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
            'name' => __('Components', 'monitoring')
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
            'field' => 'description',
            'name' => __('Description'),
        ];

        $tab[] = [
            'id' => $index++,
            'table' => $this->getTable(),
            'field' => 'active_checks_enabled',
            'datatype' => 'bool',
            'name' => __('Active check', 'monitoring'),
        ];

        $tab[] = [
            'id' => $index++,
            'table' => $this->getTable(),
            'field' => 'passive_checks_enabled',
            'datatype' => 'bool',
            'name' => __('Passive check', 'monitoring'),
        ];

        $tab[] = [
            'id' => $index++,
            'table' => 'glpi_plugin_monitoring_commands',
            'field' => 'name',
            'datatype' => 'itemlink',
            'linkfield' => 'plugin_monitoring_commands_id',
            'name' => __('Related command', 'monitoring'),
        ];

        $tab[] = [
            'id' => $index++,
            'table' => 'glpi_plugin_monitoring_eventhandlers',
            'field' => 'name',
            'datatype' => 'itemlink',
            'linkfield' => 'plugin_monitoring_eventhandlers_id',
            'name' => __('Related event handler', 'monitoring'),
        ];

        $tab[] = [
            'id' => $index++,
            'table' => 'glpi_plugin_monitoring_checks',
            'field' => 'name',
            'datatype' => 'itemlink',
            'linkfield' => 'plugin_monitoring_checks_id',
            'name' => __('Related check frequency', 'monitoring'),
        ];

        $tab[] = [
            'id' => $index++,
            'table' => 'glpi_calendars',
            'field' => 'name',
            'datatype' => 'specific',
            'name' => __('Related check period', 'monitoring'),
        ];

        $tab[] = [
            'id' => $index++,
            'table' => $this->getTable(),
            'field' => 'business_impact',
            'datatype' => 'integer',
            'name' => __('Business impact', 'monitoring'),
        ];

        $tab[] = [
            'id' => $index++,
            'table' => $this->getTable(),
            'field' => 'freshness_type',
            'datatype' => 'specific',
            'name' => __('Freshness type', 'monitoring'),
        ];

        $tab[] = [
            'id' => $index,
            'table' => $this->getTable(),
            'field' => 'freshness_count',
            'datatype' => 'integer',
            'name' => __('Freshness count', 'monitoring'),
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


    static function getSpecificValueToDisplay($field, $values, array $options = [])
    {

        if (!is_array($values)) {
            $values = [$field => $values];
        }
        switch ($field) {
            case 'calendars_id':
                $calendar = new Calendar();
                $calendar->getFromDB($values[$field]);
                return $calendar->getName(1);
                break;

            case 'freshness_type':
                $a_freshness_type = [];
                $a_freshness_type['seconds'] = __('Second(s)', 'monitoring');
                $a_freshness_type['minutes'] = __('Minute(s)', 'monitoring');
                $a_freshness_type['hours'] = __('Hour(s)', 'monitoring');
                $a_freshness_type['days'] = __('Day(s)', 'monitoring');
                return $a_freshness_type[$values[$field]];
                break;
        }
        return parent::getSpecificValueToDisplay($field, $values, $options);
    }


    function showForm($items_id, $options = [], $copy = [])
    {
        $pMonitoringCommand = new PluginMonitoringCommand();

        $this->initForm($items_id, $options);
        if ($this->fields['id'] == 0) {
            $this->fields['active_checks_enabled'] = 1;
            $this->fields['passive_checks_enabled'] = 1;
            $this->fields['business_impact'] = 3;
        }

        if (count($copy) > 0) {
            foreach ($copy as $key => $value) {
                $this->fields[$key] = stripslashes($value);
            }
        }
        $this->showFormHeader($options);

        if (isset($_SESSION['plugin_monitoring_components'])) {
            $this->fields = $_SESSION['plugin_monitoring_components'];
            if (!isset($this->fields["id"])) {
                $this->fields["id"] = '';
            }
            if (!isset($this->fields["arguments"])) {
                $this->fields["arguments"] = '';
            }
            unset($_SESSION['plugin_monitoring_components']);
        }

        echo "<tr>";
        echo "<td>";
        echo __('Name') . "<span class='red'>*</span>&nbsp;:";
        echo "</td>";
        echo "<td>";
        echo "<input type='hidden' name='is_template' value='1' />";
        $objectName = autoName($this->fields["name"], "name", 1,
            $this->getType());
        Html::autocompletionTextField($this, 'name', ['value' => $objectName]);
        echo "</td>";
        // * checks
        echo "<td>" . __('Check strategy', 'monitoring') . "<span class='red'>*</span>&nbsp;:</td>";
        echo "<td>";
        Dropdown::show("PluginMonitoringCheck",
            ['name' => 'plugin_monitoring_checks_id',
                'value' => $this->fields['plugin_monitoring_checks_id']]);
        echo "</td>";
        echo "</tr>";

        // * Link
        echo "<tr>";
        echo "<td>";
        echo __('Monitoring service_description', 'monitoring') . "&nbsp;:";
        echo "</td>";
        echo "<td>";
        $objectDescription = autoName($this->fields["description"],
            "name", 1, $this->getType());
        Html::autocompletionTextField($this, 'description', ['value' => $objectDescription]);
        echo "</td>";
        /*
              echo "<td>";
        //      echo "Type of template&nbsp;:";
              echo "</td>";
              echo "<td>";
        //      $a_types = array();
        //      $a_types[''] = Dropdown::EMPTY_VALUE;
        //      $a_types['partition'] = "Partition";
        //      $a_types['processor'] = "Processor";
        //      Dropdown::showFromArray("link", $a_types, array('value'=>$this->fields['link']));
              echo "</td>";
        */
        // * active check
        echo "<td>";
        echo __('Active check', 'monitoring') . "<span class='red'>*</span>&nbsp;:";
        echo "</td>";
        echo "<td>";
        Dropdown::showYesNo("active_checks_enabled", $this->fields['active_checks_enabled']);
        echo "</td>";
        echo "</tr>";

        // * command
        echo "<tr>";
        echo "<td>";
        echo __('Command', 'monitoring') . "<span class='red'>*</span>&nbsp;:";
        echo "</td>";
        echo "<td>";
        $pMonitoringCommand->getFromDB($this->fields['plugin_monitoring_commands_id']);
        Dropdown::show("PluginMonitoringCommand", [
            'name' => 'plugin_monitoring_commands_id',
            'value' => $this->fields['plugin_monitoring_commands_id']
        ]);
        echo "</td>";
        // * passive check
        echo "<td>";
        echo __('Passive check', 'monitoring') . "<span class='red'>*</span>&nbsp;:";
        echo "</td>";
        echo "<td>";
        Dropdown::showYesNo("passive_checks_enabled", $this->fields['passive_checks_enabled']);
        echo "</td>";
        echo "</tr>";

        echo "<tr>";
        // * freshness
        echo "<td>" . __('Freshness (for passive mode)', 'monitoring') . "&nbsp;:</td>";
        echo "<td>";
        if ($this->fields['freshness_count'] == '') {
            $this->fields['freshness_count'] = 0;
        }
        Dropdown::showNumber("freshness_count", [
                'value' => $this->fields['freshness_count'],
                'min' => 0,
                'max' => 300]
        );
        $a_time = [];
        $a_time['seconds'] = __('Second(s)', 'monitoring');
        $a_time['minutes'] = __('Minute(s)', 'monitoring');
        $a_time['hours'] = __('Hour(s)', 'monitoring');
        $a_time['days'] = __('Day(s)', 'monitoring');

        Dropdown::showFromArray("freshness_type", $a_time, ['value' => $this->fields['freshness_type']]);
        echo "</td>";
        echo "<td colspan='2'></td>";
        echo "</tr>";

        echo "<tr>";
        echo "<td>";
        echo __('Event handler', 'monitoring') . "&nbsp;:";
        echo "</td>";
        echo "<td>";
        dropdown::show("PluginMonitoringEventhandler",
            ['name' => 'plugin_monitoring_eventhandlers_id',
                'value' => $this->fields['plugin_monitoring_eventhandlers_id']]);
        echo "</td>";
        // * calendar
        echo "<td>" . __('Check period', 'monitoring') . "<span class='red'>*</span>&nbsp;:</td>";
        echo "<td>";
        dropdown::show("Calendar", ['name' => 'calendars_id',
            'value' => $this->fields['calendars_id']]);
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Business priority level', 'monitoring') . "&nbsp;:</td>";
        echo "<td>";
        Dropdown::showNumber('business_impact', [
                'value' => $this->fields['business_impact'],
                'min' => 0,
                'max' => 5]
        );
        echo "</td>";
        echo "<td colspan='2'></td>";
        echo "</tr>";

        echo "<tr>";
        echo "<th colspan='4'>" . __('Remote check', 'monitoring') . "</th>";
        echo "</tr>";

        echo "<tr>";
        // * remotesystem
        echo "<td>";
        echo __('Utility used for remote check', 'monitoring') . "&nbsp;:";
        echo "</td>";
        echo "<td>";
        $input = [];
        $input[''] = '------';
//        $input['byssh'] = 'byssh';
        $input['nrpe'] = 'nrpe';
        $input['nsca'] = 'nsca';
        Dropdown::showFromArray("remotesystem", $input, ['value' => $this->fields['remotesystem']]);
        echo "</td>";
        // * is_argument
        echo "<td>";
        echo __('Use arguments (NRPE only)', 'monitoring') . "&nbsp;:";
        echo "</td>";
        echo "<td>";
        Dropdown::showYesNo("is_arguments", $this->fields['is_arguments']);
        echo "</td>";
        echo "</tr>";

        echo "<tr>";
        // alias command
        echo "<td>";
        echo __('Alias command if required (NRPE only)', 'monitoring') . "&nbsp;:";
        echo "</td>";
        echo "<td>";
        echo "<input type='text' name='alias_command' value='" . $this->fields['alias_command'] . "' size='35' />";
        echo "</td>";
        echo "<td colspan='2'></td>";
        echo "</tr>";

        // * Manage arguments
        $array = [];
        $a_displayarg = [];
        if (isset($pMonitoringCommand->fields['command_line'])) {
            preg_match_all("/\\$(ARG\d+)\\$/", $pMonitoringCommand->fields['command_line'], $array);
            $a_arguments = importArrayFromDB($this->fields['arguments']);
            foreach ($array[0] as $arg) {
                if (strstr($arg, "ARG")) {
                    $arg = str_replace('$', '', $arg);
                    if (!isset($a_arguments[$arg])) {
                        $a_arguments[$arg] = '';
                    }
                    $a_displayarg[$arg] = $a_arguments[$arg];
                }
            }
        }
        if (count($a_displayarg) > 0) {
            $a_tags = $this->tagsAvailable();
            $a_argtext = importArrayFromDB($pMonitoringCommand->fields['arguments']);
            echo "<tr>";
            echo "<th colspan='4'>" . __('Arguments', 'monitoring') . "&nbsp;</th>";
            echo "</tr>";

            foreach ($a_displayarg as $key => $value) {
                echo "<tr>";
                echo "<td>";
                if (isset($a_argtext[$key])
                    AND $a_argtext[$key] != '') {
                    echo nl2br($a_argtext[$key]) . "&nbsp;:";
                } else {
                    echo __('Argument', 'monitoring') . " (" . $key . ")&nbsp;:";
                }
                echo "</td>";
                echo "<td>";
                echo "<input type='text' name='arg[" . $key . "]' value='" . $value . "' size='35' /><br/>";
                echo "</td>";
                if (count($a_tags) > 0) {
                    foreach ($a_tags as $key1 => $value1) {
                        echo "<td class='tab_bg_3'>";
                        echo "<strong>" . $key . "</strong>&nbsp;:";
                        echo "</td>";
                        echo "<td class='tab_bg_3'>";
                        echo $value1;
                        echo "</td>";
                        unset($a_tags[$key1]);
                        break;
                    }
                } else {
                    echo "<td colspan='2'></td>";
                }
                echo "</tr>";
            }
            foreach ($a_tags as $key => $value) {
                echo "<tr>";
                echo "<td colspan='2'></td>";
                echo "<td class='tab_bg_3'>";
                echo "<strong>" . $key . "</strong>&nbsp;:";
                echo "</td>";
                echo "<td class='tab_bg_3'>";
                echo $value;
                echo "</td>";
                echo "</tr>";
            }
        }

        $this->showFormButtons($options);

        return true;
    }


    function copyItem($items_id)
    {

        if (!Session::haveRight("config", UPDATE)) return;

        // Add form for copy item

        $this->getFromDB($items_id);
        $this->fields['id'] = 0;
        $this->showFormHeader([]);

        echo "<tr class='tab_bg_1'>";
        echo "<td colspan='4' class='center'>";
        foreach ($this->fields as $key => $value) {
            if ($key != 'id') {
                echo "<input type='hidden' name='" . $key . "' value='" . $value . "'/>";
            }
        }
        echo "<input type='submit' name='copy' value=\"" . __('copy', 'monitoring') . "\" class='submit'>";
        echo "</td>";
        echo "</tr>";

        echo "</table>";
        Html::closeForm();
    }


    function tagsAvailable()
    {

        $elements = [];
        $elements[__('List of available tags', 'monitoring')] = '';
        $elements["[[HOSTNAME]]"] = __('Hostname of the device', 'monitoring');
        $elements["[[IP]]"] = __('IP of the device', 'monitoring');
        /* mohierf: disable this feature
        $elements["[[NETWORKPORTNUM]]"] = __('Network port number', 'monitoring');
        $elements["[[NETWORKPORTNAME]]"] = __('Network port name', 'monitoring');
        if (class_exists("PluginFusioninventoryNetworkPort")) {
            $elements["[[NETWORKPORTDESCR]]"] = __('Network port ifDescr of networking devices', 'monitoring');
            $elements["[SNMP:version]"] = __('SNMP version of network equipment or printer', 'monitoring');
            $elements["[SNMP:authentication]"] = __('SNMP community of network equipment or printer', 'monitoring');
        }
        */
        return $elements;
    }


    static function getTimeBetween2Checks($components_id)
    {
        $pmComponent = new PluginMonitoringComponent();
        $pmCheck = new PluginMonitoringCheck();

        $pmComponent->getFromDB($components_id);
        $pmCheck->getFromDB($pmComponent->fields['plugin_monitoring_checks_id']);

        $timeMinutes = $pmCheck->fields['check_interval'];
        $timeSeconds = $timeMinutes * 60;
        return $timeSeconds;
    }


    function hasPerfdata($incremental = false)
    {
        // For former source code compatibility!
        return false;
    }


    function hasCounters()
    {
        // For former source code compatibility!
        return false;
    }
}
