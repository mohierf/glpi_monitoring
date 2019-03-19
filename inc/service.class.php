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

class PluginMonitoringService extends CommonDBTM
{

    const HOMEPAGE = 1024;
    const DASHBOARD = 2048;

    const COLUMN_HOST_NAME = 1;
    const COLUMN_STATE = 5;
    const COLUMN_STATE_TYPE = 6;

    static $rightname = 'plugin_monitoring_component';


    static function getTypeName($nb = 0)
    {
        return __('Resources', 'monitoring');
    }


    /*
     * Search options, see: https://glpi-developer-documentation.readthedocs.io/en/master/devapi/search.html#search-options
     */
    public function getSearchOptionsNew()
    {
        return $this->rawSearchOptions();
    }

    /**
     * WARNING: change the index order with very much care ... the display of the
     * services table is using some fixed index values!
     *
     * @return array
     */
    function rawSearchOptions()
    {

        $tab = [];

        $tab[] = [
            'id' => 'common',
            'name' => __('Resources', 'monitoring')
        ];

        $index = 1;
        $tab[] = [
            'id' => $index++,
            'table' => 'glpi_entities',
            'field' => 'name',
            'name' => __('Entity'),
            'datatype' => 'string'
        ];

        $tab[] = [
            'id' => $index++,
            'table' => $this->getTable(),
            'field' => 'host_name',
            'datatype' => 'string',
            'name' => __('Host name', 'monitoring'),
        ];

        $tab[] = [
            'id' => $index++,
            'table' => $this->getTable(),
            'field' => 'service_description',
            'name' => __('Name'),
            'datatype' => 'itemlink'
        ];

        $tab[] = [
            'id' => $index++,
            'table' => "glpi_plugin_monitoring_components",
            'field' => 'name',
            'datatype' => 'itemlink',
            'linkfield' => 'plugin_monitoring_components_id',
            'name' => __('Component', 'monitoring'),
        ];

//        $tab[] = [
//            'id' => $index++,
//            'table' => "glpi_plugin_monitoring_componentscatalogs_hosts",
//            'field' => 'itemtype',
//            'datatype' => 'itemlink',
//            'linkfield' => 'id',
//            'name' => __('Item type', 'monitoring'),
//        ];
//
//        $tab[] = [
//            'id' => $index++,
//            'table' => "glpi_plugin_monitoring_componentscatalogs_hosts",
//            'field' => 'items_id',
//            'datatype' => 'itemlink',
//            'linkfield' => 'id',
//            'name' => __('Item identifier', 'monitoring'),
//        ];
//
//            $tab[2]['table'] = "glpi_plugin_monitoring_components";
//            $tab[2]['field'] = 'name';
//            $tab[2]['linkfield'] = 'plugin_monitoring_components_id';
//            $tab[2]['name'] = __('Component', 'monitoring');
//            $tab[2]['datatype'] = 'itemlink';
//            $tab[2]['itemlink_type'] = 'PluginMonitoringComponent';

        $tab[] = [
            'id' => $index++,
            'table' => $this->getTable(),
            'field' => 'source',
            'name' => __('Source', 'monitoring'),
        ];

        $tab[] = [
            'id' => $index++,
            'table' => $this->getTable(),
            'field' => 'state',
            'datatype' => 'string',
            'name' => __('State', 'monitoring'),
        ];

        $tab[] = [
            'id' => $index++,
            'table' => $this->getTable(),
            'field' => 'state_type',
            'datatype' => 'string',
            'name' => __('State type', 'monitoring'),
        ];

        $tab[] = [
            'id' => $index++,
            'table' => $this->getTable(),
            'field' => 'last_check',
            'datatype' => 'date',
            'name' => __('Last check result', 'monitoring'),
        ];

        $tab[] = [
            'id' => $index++,
            'table' => $this->getTable(),
            'field' => 'output',
            'datatype' => 'string',
            'name' => __('Last check output', 'monitoring'),
        ];

        $tab[] = [
            'id' => $index++,
            'table' => $this->getTable(),
            'field' => 'perf_data',
            'datatype' => 'string',
            'name' => __('Last check performance data', 'monitoring'),
        ];

        $tab[] = [
            'id' => $index++,
            'table' => $this->getTable(),
            'field' => 'latency',
            'datatype' => 'string',
            'name' => __('Last check latency', 'monitoring'),
        ];

        $tab[] = [
            'id' => $index++,
            'table' => $this->getTable(),
            'field' => 'execution_time',
            'datatype' => 'string',
            'name' => __('Last check execution time', 'monitoring'),
        ];

        $tab[] = [
            'id' => $index,
            'table' => $this->getTable(),
            'field' => 'is_acknowledged',
            'datatype' => 'bool',
            'name' => __('Acknowledged?', 'monitoring'),
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
            case 'link':
                $pmService = new PluginMonitoringService();
                $pmService->getFromDB($values[$field]);
                return $pmService->getLink();
                break;
        }
        return parent::getSpecificValueToDisplay($field, $values, $options);
    }


    function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        if (!$withtemplate) {
            switch ($item->getType()) {
                case 'Central' :
                    if (Session::haveRight("plugin_monitoring_central", READ)
                        and Session::haveRight("plugin_monitoring_service_status", self::HOMEPAGE)) {
                        return [1 => __('Monitored services', 'monitoring')];
                    }
                    break;
            }
        }
        return '';
    }


    static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {

        switch ($item->getType()) {
            case 'Central' :
                $pmDisplay = new PluginMonitoringDisplay();
                $pmDisplay->showServicesCounters(true);
                $params = Search::manageParams("PluginMonitoringService", []);
                $pmDisplay->showResourcesBoard('', true, $params);
                break;
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
     * function getRights($interface = 'central')
     * {
     *
     * $values = [];
     * $values[self::HOMEPAGE] = __('See in homepage', 'monitoring');
     * $values[self::DASHBOARD] = __('See in dashboard', 'monitoring');
     *
     * return $values;
     * }
     */


    /**
     * Get service name
     *
     * @param array $options
     *
     * @return string|string[]|null
     */
    function getName($options = [])
    {
        if ($this->getID() == -1) return '';

        $pmComponent = new PluginMonitoringComponent();
        $a_component = current($pmComponent->find("`id`='" . $this->fields['plugin_monitoring_components_id'] . "'", "", 1));

        $service_description = $a_component['name'];
        if (isset($options) && isset($options['shinken'])) {
            $service_description = preg_replace("/[^A-Za-z0-9\-_]/", "", $a_component['description']);
            if (empty($service_description)) $service_description = preg_replace("/[^A-Za-z0-9\-_]/", "", $a_component['name']);
        }

        if (isset($options) && isset($options['hostname'])) {
            $service_description .= ' ' . __('on', 'monitoring') . ' ' . $this->getHostName();
        }

        return $service_description;
    }


    /**
     * Get service link
     *
     * @param array $options
     *
     * @return string
     */
    function getLink($options = [])
    {
        global $CFG_GLPI;

        if ($this->getID() == -1) return '';

        $link = $CFG_GLPI['root_doc'] .
            "/plugins/monitoring/front/service.php?hidesearch=1"
            . "&criteria[0][field]=20"
            . "&criteria[0][searchtype]=equals"
            . "&criteria[0][value]=" . $this->getComputerID() . ""

            . "&itemtype=PluginMonitoringService"
            . "&start=0'";
        if (isset($options['monitoring']) && $options['monitoring']) {
            return "<a href='$link'>" . $this->getName(['shinken' => true, 'hostname' => true]) . "</a>" . "&nbsp;" . $this->getComments();
        } else {
            return "<a href='$link'>" . $this->getName(['hostname' => true]) . "</a>" . "&nbsp;" . $this->getComments();
        }
    }


    function getClass($row = false, $state_type = false)
    {
        if ($this->getID() == -1) return '';

        // Not yet known...
        $class_state = 'greyed';
        if (!empty($this->fields['state']) and !empty($this->fields['last_check'])) {
            $class_state = strtolower($this->fields['state']);
        }
        $class_state = ($row ? 'background-' : 'font-') . $class_state;

        if ($state_type) {
            if ($this->fields['state_type'] == 'SOFT') {
                $class_state .= ' state-type-soft';
            } else {
                $class_state .= ' state-type-hard';
            }
        }

        return $class_state;
    }


    /**
     * Get service entity
     * If $update is true, get the original host entites_id and update ours.
     *
     * @param bool $update
     *
     * @return int
     */
    function getEntityID($update = false)
    {
        if ($update) {
            /* @var CommonDBTM $item */
            $itemtype = $this->getField("itemtype");
            $item = new $itemtype();
            $item->getFromDB($this->getField("items_id"));

            $input = [];
            $input['id'] = $this->getID();
            $input['entities_id'] = $item->getEntityID();
            $this->update($input);

            return $item->getEntityID();
        }

        return $this->getField("entities_id");
    }


    /**
     * Get computer identifier for a service
     */
    function getComputerID()
    {
        $pmCC_Host = new PluginMonitoringComponentscatalog_Host();
        $pmCC_Host->getFromDB($this->fields['plugin_monitoring_componentscatalogs_hosts_id']);

        /* @var CommonDBTM $item */
        $itemtype = $pmCC_Host->fields['itemtype'];
        $item = new $itemtype();
        if ($item->getFromDB($pmCC_Host->fields['items_id'])) {
            return $item->getID();
        }

        return -1;
    }


    /**
     * Get the monitoring host for a service (compute, printer, ...)
     */
    function getHost()
    {
        $pmCC_Host = new PluginMonitoringComponentscatalog_Host();
        $pmCC_Host->getFromDB($this->fields['plugin_monitoring_componentscatalogs_hosts_id']);

        /* @var CommonDBTM $item */
        $itemtype = $pmCC_Host->fields['itemtype'];
        $item = new $itemtype();
        if ($item->getFromDB($pmCC_Host->fields['items_id'])) {
            return $item;
        }

        return null;
    }


    /**
     * Get the monitoring host for a service (monitoring state)
     */
    function getMonitoringHost()
    {
        $pmCC_Host = new PluginMonitoringComponentscatalog_Host();
        if ($pmCC_Host->getFromDB($this->fields['plugin_monitoring_componentscatalogs_hosts_id'])) {


            $pmHost = new PluginMonitoringHost();
            if ($pmHost->getFromDBByCrit([
                'plugin_monitoring_componentscatalogs_id' => $this->fields['plugin_monitoring_componentscatalogs_hosts_id'],
                'itemtype' => $pmCC_Host->fields['itemtype'],
                'items_id' => $pmCC_Host->fields['items_id']])) {
                return $pmHost;
            }
            // fixme: fail because plugin_monitoring_hosts_id is always 0!
//            $pmHost = new PluginMonitoringHost();
//            if ($pmHost->getFromDB($pmComponentscatalog_Host->fields['plugin_monitoring_hosts_id'])) {
//                return $pmHost;
//            }
        }

        return null;
    }


    /**
     * Get host name for a service
     */
    function getHostName()
    {
        global $PM_CONFIG;

        $item = $this->getHost();
        if ($item) {
            if ($PM_CONFIG['append_id_hostname'] == 1) {
                return $item->getField('name') . "-" . $item->getID();
            }
            return $item->getField('name');
        }

        return '';
    }


    /**
     * Get host overall state
     */
    function getHostState()
    {
        $item = $this->getMonitoringHost();
        if ($item) {
            return $item->getField('state');
        }

        return null;
    }


    /**
     * Is currently acknowledged ?
     */
    function isCurrentlyAcknowledged()
    {
        if ($this->getID() == -1) return false;

        $pmAcknowledge = new PluginMonitoringAcknowledge();
        if ($pmAcknowledge->getFromHost($this->getID(), 'Service') != -1) {
            // PluginMonitoringToolbox::log("isCurrentlyAcknowledged ? ".$this->getID()." : ".(! $pmAcknowledge->isExpired())." \n");
            return (!$pmAcknowledge->isExpired());
        }

        return false;
    }


    function getAcknowledge()
    {
        if ($this->getID() == -1) return false;

        $pmAcknowledge = new PluginMonitoringAcknowledge();
        if ($pmAcknowledge->getFromHost($this->getID(), 'Service') != -1) {
            return ($pmAcknowledge->getComments());
        }

        return '';
    }


    /**
     * Get service state
     *
     * Return :
     * - OK if service is OK
     * - CRITICAL if service is CRITICAL
     * - WARNING if host is WARNING, RECOVERY or FLAPPING
     * - UNKNOWN else
     */
    function getState()
    {
        // PluginMonitoringToolbox::log("getShortState - ".$this->getID()."\n");
        if ($this->getID() == -1) return '';

        $state = $this->getField('state');

        switch ($state) {
            case 'OK':
                $returned_state = 'OK';
                break;

            case 'CRITICAL':
                $returned_state = 'CRITICAL';
                break;

            case 'WARNING':
            case 'RECOVERY':
            case 'FLAPPING':
                $returned_state = 'WARNING';
                break;

            default:
                $returned_state = 'UNKNOWN';
                break;

        }

        return $returned_state;
    }


    /**
     * Get service short state (state + acknowledgement)
     * options :
     * - image, if exists, returns URL to a state image
     *
     * Return :
     * - green if service is OK
     * - red if service is CRITICAL
     * - redblue if red and acknowledged
     * - orange if host is WARNING, RECOVERY or FLAPPING
     * - orangeblue if orange and acknowledged
     * - yellow for every other state
     * - yellowblue if yellow and acknowledged
     *
     * append '_soft' if service is in soft statetype
     *
     * @param array $options
     *
     * @return string
     */
    function getShortState($options = [])
    {
        global $CFG_GLPI;

        // PluginMonitoringToolbox::log("getShortState - ".$this->getID()."\n");
        if ($this->getID() == -1) return '';

        $acknowledge = $this->getField('is_acknowledged');
        $state_type = $this->getField('state_type');
        $state = $this->getField('state');
        $event = $this->getField('output');


        $shortstate = '';
        switch ($state) {
            case 'OK':
                $shortstate = 'green';
                break;

            case 'CRITICAL':
                if ($acknowledge) {
                    $shortstate = 'redblue';
                } else {
                    $shortstate = 'red';
                }
                break;

            case 'WARNING':
            case 'RECOVERY':
            case 'FLAPPING':
                if ($acknowledge) {
                    $shortstate = 'orangeblue';
                } else {
                    $shortstate = 'orange';
                }
                break;

            default:
                if ($acknowledge) {
                    $shortstate = 'yellowblue';
                } else {
                    $shortstate = 'yellow';
                }
                break;

        }
        if ($state == 'WARNING'
            && $event == '') {
            if ($acknowledge) {
                $shortstate = 'yellowblue';
            } else {
                $shortstate = 'yellow';
            }
        }
        if ($state_type == 'SOFT') {
            $shortstate .= '_soft';
        }

        if (isset($options) && isset($options['image'])) {
            return $CFG_GLPI['root_doc'] . "/plugins/monitoring/pics/box_" . $shortstate . "_" . $options['image'] . ".png";
        }
        return $shortstate;
    }


    /**
     * Get a list of services associated with host
     *
     * @param string $itemtype  type of item (eg. Computer)
     * @param integer $items_id id of the object
     *
     */
    function getListByHost($itemtype, $items_id)
    {
        /* @var CommonDBTM $item */
        $item = new $itemtype();
        $item->getFromDB($items_id);

        $pmServices = new PluginMonitoringService();
        $a_services = $pmServices->find("`host_name`='". $item->getName() ."'");
        foreach ($a_services as $index => $data) {
            PluginMonitoringToolbox::logIfDebug("service, " . print_r($data, true));

            if (!empty($host_services_state_list)) {
                $host_services_state_list .= "\n";
            }
        }
    }


    /**
     * Display services associated with host
     *
     * @param string $itemtype  type of item (eg. Computer)
     * @param integer $items_id id of the object
     *
     */
    function displayListByHost($itemtype, $items_id)
    {
        /* @var CommonDBTM $item */
        $item = new $itemtype();
        $item->getFromDB($items_id);

        $params = [
            "criteria" => [
                [
                    "field" => 2,
                    "searchtype" => "contains",
                    "value" => $item->getName()
                ]
            ],
            "itemtype" => "PluginMonitoringService"
        ];

        $extra_query = ["host_name" => $item->getName()];
        $pmDisplay = new PluginMonitoringDisplay();
        PluginMonitoringToolbox::log("Extra query: " . print_r($extra_query, true));

        // Reduced mode with an extra query
        $pmDisplay->displayServicesCounters(true, true, $extra_query);
        $pmDisplay->showResourcesBoard('', true, $params);
    }


    function showForm($items_id, $options = [], $services_id = '')
    {
        $pMonitoringCommand = new PluginMonitoringCommand();
        $pMonitoringServicedef = new PluginMonitoringServicedef();

        if (isset($_GET['withtemplate']) AND ($_GET['withtemplate'] == '1')) {
            $options['withtemplate'] = 1;
        } else {
            $options['withtemplate'] = 0;
        }

        if ($services_id != '') {
            $this->getEmpty();
        } else {
            $this->getFromDB($items_id);
        }
        $this->showTabs($options);
        $this->showFormHeader($options);
        if (!isset($this->fields['plugin_monitoring_servicedefs_id'])
            OR empty($this->fields['plugin_monitoring_servicedefs_id'])) {
            $pMonitoringServicedef->getEmpty();
        } else {
            $pMonitoringServicedef->getFromDB($this->fields['plugin_monitoring_servicedefs_id']);
        }
        $template = false;


        echo "<tr>";
        echo "<td>";
        if ($services_id != '') {
            echo "<input type='hidden' name='plugin_monitoring_services_id' value='" . $services_id . "' />";
        }
        echo __('Name') . "&nbsp;:";
        echo "</td>";
        echo "<td>";
        $objectName = autoName($this->fields["name"], "name", ($template === "newcomp"),
            $this->getType());
        Html::autocompletionTextField($this, 'name', ['value' => $objectName]);
        echo "</td>";
        echo "<td>";
        echo __('Template') . "&nbsp;:";
        echo "</td>";
        echo "<td>";
        if ($items_id != '0') {
            echo "<input type='hidden' name='update' value='update'>\n";
        }
        echo "<input type='hidden' name='plugin_monitoring_servicedefs_id_s' value='" . $this->fields['plugin_monitoring_servicedefs_id'] . "'>\n";
        if ($pMonitoringServicedef->fields['is_template'] == '0') {
            $this->fields['plugin_monitoring_servicedefs_id'] = 0;
        }
        Dropdown::show("PluginMonitoringServicetemplate", [
            'name' => 'plugin_monitoring_servicetemplates_id',
            'value' => $this->fields['plugin_monitoring_servicetemplates_id'],
            'auto_submit' => true
        ]);
        echo "</td>";
        echo "<td>";
        if ($this->fields["items_id"] == '') {

        } else {
            echo "<input type='hidden' name='items_id' value='" . $this->fields["items_id"] . "'>\n";
            echo "<input type='hidden' name='itemtype' value='" . $this->fields["itemtype"] . "'>\n";
        }
        echo "</td>";
        echo "</tr>";

        echo "<tr>";
        echo "<th colspan='4'>&nbsp;</th>";
        echo "</tr>";

        echo "<tr>";
        // * itemtype link
        if ($this->fields['itemtype'] != '') {
            $itemtype = $this->fields['itemtype'];
            $item = new $itemtype();
            $item->getFromDB($this->fields['items_id']);
            echo "<td>";
            echo __('Item Type') . " <i>" . $item->getTypeName() . "</i>";
            echo "&nbsp;:</td>";
            echo "<td>";
            echo $item->getLink(1);
            echo "</td>";
        } else {
            echo "<td colspan='2' align='center'>";
            echo __('No type associated', 'monitoring');
            echo "</td>";
        }
        // * command
        echo "<td>";
        echo __('Command', 'monitoring') . "&nbsp;:";
        echo "</td>";
        echo "<td align='center'>";
        if ($this->fields['plugin_monitoring_servicetemplates_id'] > 0) {
            $pMonitoringServicetemplate = new PluginMonitoringServicetemplate();
            $pMonitoringServicetemplate->getFromDB($this->fields['plugin_monitoring_servicetemplates_id']);
            $pMonitoringCommand->getFromDB($pMonitoringServicetemplate->fields['plugin_monitoring_commands_id']);
            echo $pMonitoringCommand->getLink(1);
        } else {
            $pMonitoringCommand->getFromDB($pMonitoringServicedef->fields['plugin_monitoring_commands_id']);
            Dropdown::show("PluginMonitoringCommand", [
                'name' => 'plugin_monitoring_commands_id',
                'value' => $pMonitoringServicedef->fields['plugin_monitoring_commands_id']
            ]);
        }
        echo "</td>";
        echo "</tr>";

        echo "<tr>";
        // * checks
        echo "<td>" . __('Check definition', 'monitoring') . "&nbsp;:</td>";
        echo "<td align='center'>";
        if ($this->fields['plugin_monitoring_servicetemplates_id'] > 0) {
            $pMonitoringCheck = new PluginMonitoringCheck();
            $pMonitoringCheck->getFromDB($pMonitoringServicetemplate->fields['plugin_monitoring_checks_id']);
            echo $pMonitoringCheck->getLink(1);
        } else {
            Dropdown::show("PluginMonitoringCheck",
                ['name' => 'plugin_monitoring_checks_id',
                    'value' => $pMonitoringServicedef->fields['plugin_monitoring_checks_id']]);
        }
        echo "</td>";
        // * active check
        echo "<td>";
        echo __('Active check', 'monitoring') . "&nbsp;:";
        echo "</td>";
        echo "<td align='center'>";
        if ($this->fields['plugin_monitoring_servicetemplates_id'] > 0) {
            echo Dropdown::getYesNo($pMonitoringServicetemplate->fields['active_checks_enabled']);
        } else {
            Dropdown::showYesNo("active_checks_enabled", $pMonitoringServicedef->fields['active_checks_enabled']);
        }
        echo "</td>";
        echo "</tr>";

        echo "<tr>";
        // * passive check
        echo "<td>";
        echo __('Passive check', 'monitoring') . "&nbsp;:";
        echo "</td>";
        echo "<td align='center'>";
        if ($this->fields['plugin_monitoring_servicetemplates_id'] > 0) {
            echo Dropdown::getYesNo($pMonitoringServicetemplate->fields['passive_checks_enabled']);
        } else {
            Dropdown::showYesNo("passive_checks_enabled", $pMonitoringServicedef->fields['passive_checks_enabled']);
        }
        echo "</td>";
        // * calendar
        echo "<td>" . __('Check period', 'monitoring') . "&nbsp;:</td>";
        echo "<td align='center'>";
        if ($this->fields['plugin_monitoring_servicetemplates_id'] > 0) {
            $calendar = new Calendar();
            $calendar->getFromDB($pMonitoringServicetemplate->fields['calendars_id']);
            echo $calendar->getLink(1);
        } else {
            dropdown::show("Calendar", ['name' => 'calendars_id',
                'value' => $pMonitoringServicedef->fields['calendars_id']]);
        }
        echo "</td>";
        echo "</tr>";

        if (!($this->fields['plugin_monitoring_servicetemplates_id'] > 0
            AND $pMonitoringServicetemplate->fields['remotesystem'] == '')) {

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
//            $input['byssh'] = 'byssh';
            $input['nrpe'] = 'nrpe';
            $input['nsca'] = 'nsca';
            if ($this->fields['plugin_monitoring_servicetemplates_id'] > 0) {
                echo $input[$pMonitoringServicetemplate->fields['remotesystem']];
            } else {
                Dropdown::showFromArray("remotesystem",
                    $input,
                    ['value' => $pMonitoringServicedef->fields['remotesystem']]);
            }
            echo "</td>";
            // * is_argument
            echo "<td>";
            echo __('Use arguments (NRPE only)', 'monitoring') . "&nbsp;:";
            echo "</td>";
            echo "<td>";
            if ($this->fields['plugin_monitoring_servicetemplates_id'] > 0) {
                echo Dropdown::getYesNo($pMonitoringServicetemplate->fields['is_arguments']);
            } else {
                Dropdown::showYesNo("is_arguments", $pMonitoringServicedef->fields['is_arguments']);
            }
            echo "</td>";
            echo "</tr>";

            echo "<tr>";
            // alias command
            echo "<td>";
            echo __('Alias command if required (NRPE only)', 'monitoring') . "&nbsp;:";
            echo "</td>";
            echo "<td>";
            if ($this->fields['plugin_monitoring_servicetemplates_id'] > 0) {
                echo "<input type='text' name='alias_commandservice' value='" . $this->fields['alias_command'] . "' />";
            } else {
                echo "<input type='text' name='alias_command' value='" . $pMonitoringServicedef->fields['alias_command'] . "' />";
            }
            echo "</td>";

            echo "<td>";
            echo __('Template (for graphs generation)', 'monitoring') . "&nbsp;:GHJKL";
            echo "</td>";
            echo "<td>";
            if ($this->fields['plugin_monitoring_servicetemplates_id'] > 0) {
                $pMonitoringCommand->getEmpty();
                $pMonitoringCommand->getFromDB($pMonitoringServicetemplate->fields['aliasperfdata_commands_id']);
                echo $pMonitoringCommand->getLink(1);
            } else {
                $pMonitoringCommand->getFromDB($pMonitoringServicedef->fields['aliasperfdata_commands_id']);
                Dropdown::show("PluginMonitoringCommand", [
                    'name' => 'aliasperfdata_commands_id',
                    'value' => $pMonitoringServicedef->fields['aliasperfdata_commands_id']
                ]);
            }
            echo "</td>";
            echo "</tr>";
        }

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
            $a_argtext = importArrayFromDB($pMonitoringCommand->fields['arguments']);
            echo "<tr>";
            echo "<th colspan='4'>" . __('Argument ([text:text] is used to get values dynamically)', 'monitoring') . "&nbsp;</th>";
            echo "</tr>";

            foreach ($a_displayarg as $key => $value) {
                echo "<tr>";
                echo "<th>" . $key . "</th>";
                echo "<td colspan='2'>";
                if (isset($a_argtext[$key])) {
                    echo nl2br($a_argtext[$key]) . "&nbsp;:";
                } else {
                    echo __('Argument', 'monitoring') . "&nbsp;:";
                }

                if ($value == '') {
                    $matches = [];
                    preg_match('/(\[\w+\:\w+\])/',
                        nl2br($a_argtext[$key]), $matches);
                    if (isset($matches[0])) {
                        $value = $matches[0];
                    }
                }

                echo "</td>";
                echo "<td>";
                echo "<input type='text' name='arg[" . $key . "]' value='" . $value . "'/><br/>";
                echo "</td>";
                echo "</tr>";
            }
        }

        $this->showFormButtons($options);
        return true;
    }


    static function convertArgument($services_id, $argument)
    {
        $pmService = new PluginMonitoringService();
        $pmCC_Host = new PluginMonitoringComponentscatalog_Host();

        $pmService->getFromDB($services_id);

        $pmCC_Host->getFromDB($pmService->fields['plugin_monitoring_componentscatalogs_hosts_id']);

        /* @var CommonDBTM $item */
        $itemtype = $pmCC_Host->fields['itemtype'];
        $item = new $itemtype();
        $item->getFromDB($pmCC_Host->fields['items_id']);

        $argument = str_replace("[", "", $argument);
        $argument = str_replace("]", "", $argument);
        $a_arg = explode(":", $argument);

        $devicetype = '';
        $devicedata = [];
        if ($itemtype == "NetworkPort") {
            /* @var CommonDBTM $item2 */
            $itemtype2 = $item->fields['itemtype'];
            $item2 = new $itemtype2();
            $item2->getFromDB($item->fields['items_id']);
            $devicetype = $itemtype2;
            $devicedata = $item2->fields;
        } else {
            $devicetype = $itemtype;
            $devicedata = $item->fields;
        }

        return $argument;
    }


    function showCustomArguments($services_id)
    {

        $pmComponent = new PluginMonitoringComponent();
        $pmCommand = new PluginMonitoringCommand();
        $pmCC_Host = new PluginMonitoringComponentscatalog_Host();

        $this->getFromDB($services_id);

        $options = [];
        $options['target'] = str_replace("service.form.php", "servicearg.form.php", $this->getFormURL());

        $this->showFormHeader($options);

        $pmCC_Host->getFromDB($this->fields['plugin_monitoring_componentscatalogs_hosts_id']);
        $itemtype = $pmCC_Host->fields['itemtype'];
        $item = new $itemtype();
        /* @var CommonDBTM $item */
        $item->getFromDB($pmCC_Host->fields['items_id']);
        echo "<tr>";
        echo "<td>";
        echo $item->getTypeName() . " :";
        echo "</td>";
        echo "<td>";
        echo $item->getLink();
        echo "</td>";
        echo "<td colspan='2'></td>";
        echo "</tr>";

        $pmComponent->getFromDB($this->fields['plugin_monitoring_components_id']);
        $pmCommand->getFromDB($pmComponent->fields['plugin_monitoring_commands_id']);

        $array = [];
        $a_displayarg = [];
        if (isset($pmCommand->fields['command_line'])) {
            preg_match_all("/\\$(ARG\d+)\\$/", $pmCommand->fields['command_line'], $array);
            $a_arguments = importArrayFromDB($pmComponent->fields['arguments']);
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
            $a_tags = $pmComponent->tagsAvailable();
            array_shift($a_tags);
            $a_argtext = importArrayFromDB($pmCommand->fields['arguments']);
            echo "<tr>";
            echo "<th colspan='2'>" . __('Component arguments', 'monitoring') . "</th>";
            echo "<th colspan='2'>" . __('List of tags available', 'monitoring') . "&nbsp;</th>";
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
                echo $value . "<br/>";
                echo "</td>";
                if (count($a_tags) > 0) {
                    foreach ($a_tags as $key1 => $value1) {
                        echo "<td class='tab_bg_3'>";
                        echo "<strong>" . $key1 . "</strong>&nbsp;:";
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

        // customized arguments
        echo "<tr>";
        echo "<th colspan='4'>" . __('Custom arguments for this resource (empty : inherit)', 'monitoring') . "</th>";
        echo "</tr>";
        $array = [];
        $a_displayarg = [];
        if (isset($pmCommand->fields['command_line'])) {
            preg_match_all("/\\$(ARG\d+)\\$/", $pmCommand->fields['command_line'], $array);
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
        $a_argtext = importArrayFromDB($pmCommand->fields['arguments']);
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
            echo "<input type='text' name='arg[" . $key . "]' value='" . $value . "'/><br/>";
            echo "</td>";
            echo "<td colspan='2'></td>";
            echo "</tr>";
        }

        $this->showFormButtons($options);

    }


    function post_addItem()
    {
        global $DB;

        PluginMonitoringToolbox::logIfDebug("post_addItem: " . print_r($this->fields, true));

        $my_host = $this->getHost();

        PluginMonitoringLog::logEvent(
            'add',
            $DB->escape("Added the service '{$this->fields['service_description']}'' for {$my_host->getTypeName()} '{$my_host->getName()}''"),
            "",
            "PluginMonitoringService",
            $this->getID());
    }


    function post_purgeItem()
    {
        global $DB;

        PluginMonitoringToolbox::log("post_purgeItem: " . print_r($this->fields, true));

        // Find the service related host in the session (see PluginMonitoringComponentscatalog_Host::unlinkComponents)
        if (isset($_SESSION['plugin_monitoring']['cc_host'])
            and isset($_SESSION['plugin_monitoring']['cc_host']['itemtype'])) {
            /* @var CommonDBTM $my_host */
            $itemtype = $_SESSION['plugin_monitoring']['cc_host']['itemtype'];
            $my_host = new $itemtype();
            $my_host->getFromDB($_SESSION['plugin_monitoring']['cc_host']['items_id']);

            PluginMonitoringLog::logEvent(
                'delete',
                $DB->escape("Deleted the service '{$this->fields['service_description']}'' for {$my_host->getTypeName()} '{$my_host->getName()}''"),
                "",
                "PluginMonitoringService",
                $this->getID());
            unset($_SESSION['plugin_monitoring']['cc_host']);
        }
    }
}

