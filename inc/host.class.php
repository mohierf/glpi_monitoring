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

class PluginMonitoringHost extends CommonDBTM
{

    const HOMEPAGE = 1024;
    const DASHBOARD = 2048;

    const COLUMN_STATE = 5;
    const COLUMN_STATE_TYPE = 6;

    static $rightname = 'config';

    public $search_columns = [];


    static function getTypeName($nb = 0)
    {
        return __('Host', 'monitoring');
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
     * hosts table is using some fixed index values!
     *
     * @return array
     */
    function rawSearchOptions()
    {

        $tab = [];

        $tab[] = [
            'id' => 'common',
            'name' => __('Monitored hosts', 'monitoring')
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
            'field' => 'name',
            'name' => __('Name'),
            'datatype' => 'itemlink'
        ];

        $tab[] = [
            'id' => $index++,
            'table' => $this->getTable(),
            'field' => 'itemtype',
            'name' => __('Item type'),
        ];

        $tab[] = [
            'id' => $index++,
            'table' => $this->getTable(),
            'field' => 'items_id',
            'name' => __('Item identifier'),
        ];

        $tab[] = [
            'id' => $index++,
            'table' => $this->getTable(),
            'field' => 'host_name',
            'name' => __('Monitoring host name', 'monitoring'),
        ];

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
            'id' => $index++,
            'table' => $this->getTable(),
            'field' => 'is_acknowledged',
            'datatype' => 'bool',
            'name' => __('Acknowledged?', 'monitoring'),
        ];

        /*
         * Include other fields here
         */

        $tab[] = [
            'id' => 99,
            'table' => $this->getTable(),
            'field' => 'id',
            'name' => __('ID'),
            'usehaving' => true,
            'searchtype' => 'equals',
        ];

        return $tab;
    }


    function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        PluginMonitoringToolbox::loadLib();

        if (!$withtemplate) {
            if ($item->getType() == 'Central') {
                if (Session::haveRight("plugin_monitoring_homepage", READ)
                    and Session::haveRight("plugin_monitoring_hoststatus", PluginMonitoringHost::HOMEPAGE)) {
                    return [1 => __('Hosts status', 'monitoring')];
                } else {
                    return '';
                }
            }
            $array_ret = [];
            if ($item->getID() > 0) {
                if (self::canView()) {
                    $array_ret[0] = self::createTabEntry(
                        __('Resources', 'monitoring'), self::countForItem($item));
                }
            }
            return $array_ret;
        }
        return '';
    }


    /**
     * @param CommonDBTM $item
     *
     * @return integer
     */
    static function countForItem(CommonDBTM $item)
    {
        $dbu = new DbUtils();
        return $dbu->countElementsInTableForMyEntities('glpi_plugin_monitoring_services', [
            'host_name' => $item->getField('name')]);
    }


    static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        /* @var CommonDBTM $item */
        switch ($item->getType()) {
            case 'Central' :
                $pmDisplay = new PluginMonitoringDisplay();
                $pmDisplay->showHostsCounters(true, true);
                $params = Search::manageParams("PluginMonitoringHost", []);
                $pmDisplay->showHostsBoard($params);
                return true;

        }
        if ($item->getID() > 0) {
            if ($tabnum == 0) {
                PluginMonitoringToolbox::loadLib();
                $pmService = new PluginMonitoringService();
                $pmService->manageServices(get_class($item), $item->getID());

                $pmHostconfig = new PluginMonitoringHostconfig();
                $pmHostconfig->showForm($item->getID(), get_class($item));
            }
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

        $values = [READ => __('Read')];
        $values[self::HOMEPAGE] = __('See in homepage', 'monitoring');
        $values[self::DASHBOARD] = __('See in dashboard', 'monitoring');

        return $values;
    }


    /**
     * If host does not exist add it
     *
     * @param $item
     */
    static function addHost($item)
    {
        PluginMonitoringToolbox::logIfDebug("PluginMonitoringHost::addHost, item: " . print_r($item, true));

        $pmHost = new self();
        if (!$pmHost->getFromDBByCrit(['itemtype' => $item->fields['itemtype'], 'items_id' => $item->fields['items_id']])) {
            PluginMonitoringToolbox::log("Adding a new monitored host: {$item->fields['itemtype']} - {$item->fields['items_id']}");

            /* @var CommonDBTM $item2 */
            $input = [];
            $input['itemtype'] = $item->fields['itemtype'];
            $input['items_id'] = $item->fields['items_id'];

            $item2 = new $item->fields['itemtype'];
            if ($item2->getFromDB($item->fields['items_id'])) {
                // Set entity identier and item name
                $input['entities_id'] = $item2->fields['entities_id'];
                $input['host_name'] = PluginMonitoringShinken::monitoringFilter($item2->getName());
                $input['name'] = $item2->getName();
            }

            $input['state'] = PluginMonitoringShinken::INITIAL_HOST_STATE;
            $input['state_type'] = PluginMonitoringShinken::INITIAL_HOST_STATE_TYPE;

            $pmHost->add($input);
            PluginMonitoringToolbox::log("Added a new monitored host: " . print_r($input, true));
        } else {
            PluginMonitoringToolbox::log("A PM host is still existing: " . $pmHost->getID());
        }

        // The plugin_monitoring_hosts_id field is not present in the posted data
        $result = $item->update([
            'id' => $item->getID(),
            'plugin_monitoring_hosts_id' => $pmHost->getID()]);
        PluginMonitoringToolbox::logIfDebug("updated CC host with monitoring host id: !" . $result);
    }


    /**
     * Get host name
     *
     * @param bool $monitoring_fmwk
     *
     * @return string|string[]|null
     */
    function getName($monitoring_fmwk = false)
    {
        $hostname = $this->getName();
        if ($monitoring_fmwk) {
            $hostname = $this->fields['host_name'];
        }

        return $hostname;
    }


    /**
     * Get host identifier for a service
     */
    function getServicesID()
    {
        if ($this->getID() == -1) return -1;

        global $DB;

        $query = "SELECT
                  `glpi_plugin_monitoring_services`.`id` as service_id
                  , `glpi_plugin_monitoring_services`.`name` as service_name
                  , `glpi_plugin_monitoring_hosts`.`id` as host_id
                  , `glpi_computers`.`name` as host_name
               FROM
                  `glpi_plugin_monitoring_hosts`
               INNER JOIN `glpi_plugin_monitoring_componentscatalogs_hosts`
                    ON (`glpi_plugin_monitoring_hosts`.`itemtype` = `glpi_plugin_monitoring_componentscatalogs_hosts`.`itemtype`) AND (`glpi_plugin_monitoring_hosts`.`items_id` = `glpi_plugin_monitoring_componentscatalogs_hosts`.`items_id`)
               INNER JOIN `glpi_computers`
                    ON (`glpi_plugin_monitoring_hosts`.`items_id` = `glpi_computers`.`id`)
               INNER JOIN `glpi_plugin_monitoring_services`
                    ON (`glpi_plugin_monitoring_services`.`plugin_monitoring_componentscatalogs_hosts_id` = `glpi_plugin_monitoring_componentscatalogs_hosts`.`id`)
               WHERE (`glpi_plugin_monitoring_hosts`.`id` = '" . $this->getID() . "');";
        $result = $DB->query($query);
        if ($DB->numrows($result) > 0) {
            $a_services = [];
            while ($data = $DB->fetch_array($result)) {
                $a_services[] = $data['service_id'];
            }
            return $a_services;
        } else {
            return false;
        }
    }


    /**
     * Is host in scheduled downtime ?
     */
    function isInScheduledDowntime()
    {
        if ($this->getID() == -1) return false;

        $pmDowntime = new PluginMonitoringDowntime();
        if ($pmDowntime->getFromHost($this->getID()) != -1) {
            return $pmDowntime->isInDowntime();
        }

        // PluginMonitoringToolbox::log("Scheduled downtime ? ".$this->getID()." \n");
        // $pmDowntime->getFromDBByQuery("WHERE `" . $pmDowntime->getTable() . "`.`plugin_monitoring_hosts_id` = '" . $this->getID() . "' ORDER BY end_time DESC LIMIT 1");
        // PluginMonitoringToolbox::log("Scheduled downtime ? ".$pmAcknowledge->getID()." \n");
        // if ($pmDowntime->getID() != -1) {
        // return $pmDowntime->isInDowntime();
        // }

        return false;
    }


    /**
     * Is host currently acknowledged ?
     */
    function isCurrentlyAcknowledged()
    {
        if ($this->getID() == -1) return false;

        $pmAcknowledge = new PluginMonitoringAcknowledge();
        if ($pmAcknowledge->getFromHost($this->getID(), 'Host') != -1) {
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
     * Set host as acknowledged
     *
     * @param string $comment
     *
     * @return bool
     */
    function setAcknowledged($comment = '')
    {
        if ($this->getID() == -1) return false;

        // Do not create a new acknoledge because this function is called from acknoledge creation function !
        // $ackData = array();
        // $ackData['itemtype']       = 'PluginMonitoringHost';
        // $ackData['items_id']       = $this->getID();
        // $ackData["start_time"]     = date('Y-m-d H:i:s', $start_time);
        // $ackData["end_time"]       = date('Y-m-d H:i:s', $end_time);
        // $ackData["comment"]        = $comment;
        // $ackData["sticky"]         = 1;
        // $ackData["persistent"]     = 1;
        // $ackData["notify"]         = 1;
        // $ackData["users_id"]       = $_SESSION['glpiID'];
        // $ackData["notified"]       = 0;
        // $ackData["expired"]        = 0;
        // $pmAcknowledge = new PluginMonitoringAcknowledge();
        // $pmAcknowledge->add($ackData);

        $hostData = [];
        $hostData['id'] = $this->getID();
        $hostData['is_acknowledged'] = '1';
        $this->update($hostData);

        return true;
    }


    function setUnacknowledged($comment = '')
    {
        if ($this->getID() == -1) return false;

        $hostData = [];
        $hostData['id'] = $this->getID();
        $hostData['is_acknowledged'] = '0';
        $this->update($hostData);

        return true;
    }


    /**
     * Get host entity
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
     * Get host link to display
     * options :
     *    'monitoring' to return a link to the monitoring hosts view filtered with the host
     *    else, a link to GLPI computer form
     *
     * @param array $options
     *
     * @return string
     */
    function getLink($options = [])
    {
        global $CFG_GLPI;

        if ($this->getID() == -1) return '';

        if (empty($this->getField("itemtype")) or $this->getField("name") == 'not_set') {
            $computer = new Computer();
            if ($computer->getFromDBByCrit(['name' => $this->getField("host_name")])) {
                return $computer->getLink();
            } else {
                return $this->getField("host_name");
            }
        }

        if (isset($options['monitoring']) and $options['monitoring']) {
            $itemtype = $this->getField("itemtype");
            $item = new $itemtype();
            /* @var CommonDBTM $item */
            $item->getFromDB($this->getField("items_id"));
            $search_id = 1;
            if ($itemtype == 'Computer') {
                $search_id = 20;
            } else if ($itemtype == 'Printer') {
                $search_id = 21;
            } else if ($itemtype == 'NetworkEquipment') {
                $search_id = 22;
            }

            $link = $CFG_GLPI['root_doc'] .
                "/plugins/monitoring/front/host.php?field[0]=" . $search_id . "&searchtype[0]=equals&contains[0]=" . $item->getID() . "&itemtype=PluginMonitoringHost&start=0";
            return $item->getLink() . " [<a href='$link'>" . __('Status', 'monitoring') . "</a>]" . "&nbsp;" . $this->getComments();
        } else {
            /* @var CommonDBTM $item */
            $itemtype = $this->getField("itemtype");
            $item = new $itemtype();
            $item->getFromDB($this->getField("items_id"));
            return $item->getLink() . "&nbsp;" . $this->getComments();
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
     * Get host short state (state + acknowledgement)
     *
     * Return :
     * - green if host is UP
     * - red if host is DOWN, UNREACHABLE or DOWNTIME
     * - redblue if red and acknowledged
     * - orange if host is WARNING, RECOVERY or FLAPPING
     * - orangeblue if orange and acknowledged
     * - yellow for every other state
     * - yellowblue if yellow and acknowledged
     *
     * @param $state
     * @param $state_type
     * @param $event
     * @param int $acknowledge
     *
     * @return string
     */
    static function getState($state, $state_type, $event, $acknowledge = 0)
    {
        $shortstate = '';
        switch ($state) {

            case 'UP':
            case 'OK':
                $shortstate = 'green';
                break;

            case 'DOWN':
            case 'UNREACHABLE':
            case 'CRITICAL':
            case 'DOWNTIME':
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


            // case 'UNKNOWN':
            // case '':
            default:
                if ($acknowledge) {
                    $shortstate = 'yellowblue';
                } else {
                    $shortstate = 'yellow';
                }
                break;

        }
        if ($state == 'WARNING'
            and $event == '') {
            if ($acknowledge) {
                $shortstate = 'yellowblue';
            } else {
                $shortstate = 'yellow';
            }
        }
        if ($state_type == 'SOFT') {
            $shortstate .= '_soft';
        }
        return $shortstate;
    }


    /**
     * Get summarized state for all host services
     * $id, host id
     *    default is current host instance
     *
     * $where, services search criteria
     *    default is not acknowledged faulty services
     *
     * Returns an array containing :
     * 0 : overall services state
     * 1 : text string including date, state, event for each service
     * 2 : array of services id
     *
     * @param int $id
     * @param string $where
     *
     * @return array
     */
    static function getServicesState($id = -1, $where = "`glpi_plugin_monitoring_services`.`state` != 'OK' AND `glpi_plugin_monitoring_services`.`is_acknowledged` = '0'")
    {
        global $DB;

//      if ($id == 0) {
//         $id = $this->getID();
//      }

        if ($id == -1) {
            return [];
        }

        // Get all host services except if state is ok or is already acknowledged ...
        $host_services_ids = [];
        $host_services_state_list = '';
        $host_services_state = 'OK';
        $query = "SELECT `glpi_plugin_monitoring_services`.*
               FROM `glpi_plugin_monitoring_hosts`
                  INNER JOIN `glpi_plugin_monitoring_componentscatalogs_hosts`
                     ON (`glpi_plugin_monitoring_hosts`.`itemtype` = `glpi_plugin_monitoring_componentscatalogs_hosts`.`itemtype`) AND (`glpi_plugin_monitoring_hosts`.`items_id` = `glpi_plugin_monitoring_componentscatalogs_hosts`.`items_id`)
                  INNER JOIN `glpi_plugin_monitoring_services`
                     ON (`glpi_plugin_monitoring_services`.`plugin_monitoring_componentscatalogs_hosts_id` = `glpi_plugin_monitoring_componentscatalogs_hosts`.`id`)
               WHERE ($where )
                  AND (`glpi_plugin_monitoring_hosts`.`id` = '$id')
               ORDER BY `glpi_plugin_monitoring_services`.`service_description` ASC;";
        // PluginMonitoringToolbox::log("Query services for host : $id : $query\n");
        $result = $DB->query($query);
        if ($DB->numrows($result) > 0) {
            $host_services_state_list = '';
            while ($data = $DB->fetch_array($result)) {
                // PluginMonitoringToolbox::log("Service ".$data['name']." is ".$data['state'].", state : ".$data['output']."\n");
                if (!empty($host_services_state_list)) $host_services_state_list .= "\n";
                $host_services_state_list .= $data['last_check'] . " - " . $data['name'] . " : " . $data['state'] . ", event : " . $data['output'];
                $host_services_ids[] = $data['id'];

                switch ($data['state']) {
                    case 'CRITICAL':
                        if ($host_services_state != 'CRITICAL') $host_services_state = $data['state'];
                        break;

                    case 'DOWNTIME':
                        if ($host_services_state != 'DOWNTIME') $host_services_state = $data['state'];
                        break;

                    case 'WARNING':
                    case 'RECOVERY':
                    case 'UNKNOWN':
                        if ($host_services_state == 'OK') $host_services_state = $data['state'];
                        break;

                    case 'FLAPPING':
                        break;
                }
            }
        }

        return ([$host_services_state, $host_services_state_list, $host_services_ids]);
    }


    /**
     * Get comments for host
     * $id, host id
     *    default is current host instance
     *
     * @param int $id
     *
     * @return string nothing
     */
    function getComments($id = -1)
    {
        global $DB;

        if ($id == -1) {
            $pm_Host = $this;
        } else {
            $pm_Host = new PluginMonitoringHost();
            $pm_Host->getFromDB($id);
        }

        // PluginMonitoringToolbox::log("Host getcomments : $id : ".$pm_Host->getID()."\n");
        $comment = "";
        $toadd = [];

        // associated computer ...
        if ($pm_Host->getField('itemtype') == 'Computer') {

            $query = "SELECT "
                . " `glpi_computers`.`serial`,"
                . " `glpi_computers`.`otherserial`,"
                . " `glpi_computertypes`.`name` as typename,"
                . " `glpi_computermodels`.`name` as modelname,"
                . " `glpi_states`.`name` as statename,"
                //. " `glpi_entities`.`name` as entityname,"
                . " `glpi_entities`.`completename` as entityname,"
                . " `glpi_locations`.`completename` as locationname"
                . " FROM `glpi_computers`"
                . " LEFT JOIN `glpi_computertypes` "
                . "    ON  `glpi_computers`.`computertypes_id` = `glpi_computertypes`.`id`"
                . " LEFT JOIN `glpi_computermodels` "
                . "    ON  `glpi_computers`.`computermodels_id` = `glpi_computermodels`.`id`"
                . " LEFT JOIN `glpi_states` "
                . "    ON  `glpi_computers`.`states_id` = `glpi_states`.`id`"
                . " LEFT JOIN `glpi_entities` "
                . "    ON  `glpi_computers`.`entities_id` = `glpi_entities`.`id`"
                . " LEFT JOIN `glpi_locations` "
                . "    ON  `glpi_computers`.`locations_id` = `glpi_locations`.`id`"
                . "WHERE `glpi_computers`.`id`='" . $pm_Host->fields['items_id'] . "'"
                . " LIMIT 1";
            $result = $DB->query($query);
            if ($DB->numrows($result) > 0) {
                $data = $DB->fetch_assoc($result);

                if (!empty($data['typename'])) {
                    $toadd[] = ['name' => __('Type'),
                        'value' => nl2br($data['typename'])];
                }

                if (!empty($data['modelname'])) {
                    $toadd[] = ['name' => __('Model'),
                        'value' => nl2br($data['modelname'])];
                }

                if (!empty($data['statename'])) {
                    $toadd[] = ['name' => __('State'),
                        'value' => nl2br($data['statename'])];
                }

                if (!empty($data['entityname'])) {
                    $toadd[] = ['name' => __('Entity'),
                        'value' => nl2br($data['entityname'])];
                }

                if (!empty($data['locationname'])) {
                    $toadd[] = ['name' => __('Location'),
                        'value' => nl2br($data['locationname'])];
                }

                if (!empty($data["serial"])) {
                    $toadd[] = ['name' => __('Serial'),
                        'value' => nl2br($data["serial"])];
                }
                if (!empty($data["otherserial"])) {
                    $toadd[] = ['name' => __('Inventory number'),
                        'value' => nl2br($data["otherserial"])];
                }

                if (($pm_Host instanceof CommonDropdown)
                    && $pm_Host->isField('comment')) {
                    $toadd[] = ['name' => __('Comments'),
                        'value' => nl2br($pm_Host->getField('comment'))];
                }

                if (count($toadd)) {
                    foreach ($toadd as $dataval) {
                        $comment .= sprintf(__('%1$s: %2$s') . "<br>",
                            "<span class='b'>" . $dataval['name'], "</span>" . $dataval['value']);
                    }
                }
            }
        }
        if (!empty($comment)) {
            return Html::showToolTip($comment, ['display' => false]);
        }

        return "";
    }
}