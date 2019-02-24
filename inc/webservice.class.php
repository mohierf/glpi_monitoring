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

class PluginMonitoringWebservice
{
    static function _manageConnection($params)
    {
        $fmwk_instance = (isset($params['name']) and !empty($params['name'])) ? $params['name'] : '';
        if (empty($fmwk_instance)) {
            if (isset($params['tag']) and !empty($params['tag'])) {
                $fmwk_instance = $params['tag'];
            } else {
                $fmwk_instance = $_SERVER['REMOTE_ADDR'];
            }
        }

        // Update server with connection parameters
        $pmTag = new PluginMonitoringTag();
        $pmTag->setIP($_SERVER['REMOTE_ADDR'], isset($params['tag']) ? $params['tag'] : '', isset($params['name']) ? $params['name'] : '');

        PluginMonitoringToolbox::logIfDebug("Manage connection from $fmwk_instance: ". print_r($params, true));

        return $fmwk_instance;
    }


    /**
     * Parameters are:
     * - tag, for the server unique identification tag
     * - name, for the server friendly name
     * -
     * @param $params
     * @return array
     */
    static function methodgetConfig($params)
    {
        PluginMonitoringToolbox::logIfDebug("methodgetConfig, " . print_r($params, true));

        if (isset ($params['help'])) {
            return [
                'file' => "string, mandatory. The config filename to get : commands.cfg, hosts.cfg, ... use 'all' to get all files.",
                'file_output' => "format the output as a Nagios legacy configuration file",
                'tag' => "string, optional. The server unique identification tag",
                'name' => "string, optional. The server friendly name. Alignak sends its alignak_name property",
                'entity' => "string, optional. The required entity tag. If none, get all entities.",
                'help' => "bool, optional",
                'format' => "string, optional. 'boolean' (true, false) or 'integer' (0, 1) for boolean values. Default boolean"
            ];
        }

        if (!isset($_SESSION['plugin_monitoring'])) {
            $_SESSION['plugin_monitoring'] = [];
        }

        $fmwk_instance = self::_manageConnection($params);

        $entity = (isset($params['entity']) and !empty($params['entity'])) ? $params['entity'] : '';
        /*
         * If file_output is set, the output of the called functions will be formated according to the
         * Nagios legacy file format: define host{...}, else the output will be a standard PHP mapped array!
         */
        $file_output = (isset($params['file_output']) and !empty($params['file_output'])) ? true : false;
        /*
         * If file is not set, then it is considerd that all available data are built and returned. Same behavior
         * as if file were set to 'all'
         */
        $file = (isset($params['file']) and !empty($params['file'])) ? $params['file'] : 'all';

        ini_set("max_execution_time", "0");
        ini_set("memory_limit", "-1");

        // Get entities concerned by the provided tag and get the definition order of the highest entty
        $pmEntity = new PluginMonitoringEntity();
        $a_entities_allowed = $pmEntity->getEntitiesByTag($entity, true);
        if (!isset($_SESSION['plugin_monitoring']['allowed_entities'])) {
            $_SESSION['plugin_monitoring']['allowed_entities'] = $a_entities_allowed;
            $_SESSION['plugin_monitoring']['entities'] = [];
            foreach ($a_entities_allowed as $entity_id) {
                $pmEntity = PluginMonitoringEntity::getForEntity($entity_id);
                if (! $pmEntity) {
                    // This should not happen thanks to the default configuration
                    continue;
                }

                // Get main entity information: tag, jet lag, definitiuon order, graphite prefix
                $_SESSION['plugin_monitoring']['entities'][$entity_id] = $pmEntity->fields;
            }
        }

        if (!isset($_SESSION['plugin_monitoring']['default_contact_template'])) {
            $_SESSION['plugin_monitoring']['default_contact_template'] = null;
            $pmContactTemplate = new PluginMonitoringContacttemplate();
            $pmDefaultContacttemplate = null;
            if ($pmContactTemplate->getFromDBByCrit(['is_default' => '1'])) {
                $_SESSION['plugin_monitoring']['default_contact_template'] = $pmContactTemplate->fields;
            } else {
                PluginMonitoringToolbox::log("[ERROR] No default contact template, configuration problem!");
            }
        }


        $output = [];
        $pmShinken = new PluginMonitoringShinken();
        switch ($file) {
            case 'commands.cfg':
                $output = $pmShinken->generateCommandsCfg($entity, $file_output);
                break;

            case 'hosts.cfg':
                // Log monitoring framework restart event ...
                PluginMonitoringLog::logRestart($fmwk_instance);

                $output = $pmShinken->generateHostsCfg($entity, $file_output);
                break;

            case 'realms.cfg':
                $output = $pmShinken->generateRealmsCfg($entity, $file_output);
                break;

            case 'hostgroups.cfg':
                $output = $pmShinken->generateHostgroupsCfg($entity, $file_output);
                break;

            case 'contacts.cfg':
                $output = $pmShinken->generateContactsCfg($entity, $file_output);
                break;

            case 'timeperiods.cfg':
                $output = $pmShinken->generateTimeperiodsCfg($entity, $file_output);
                break;

            case 'services.cfg':
                $output = $pmShinken->generateServicesCfg($entity, $file_output);
                break;

            case 'services_templates.cfg':
                $output = $pmShinken->generateServicesTemplatesCfg($entity, $file_output);
                break;

            case 'all':
                // Log monitoring framework restart event ...
                PluginMonitoringLog::logRestart($fmwk_instance);
                $output = [
                    'commands.cfg' => $pmShinken->generateCommandsCfg($entity, $file_output),
                    'hosts.cfg' => $pmShinken->generateHostsCfg($entity, $file_output),
                    'hostgroups.cfg' => $pmShinken->generateHostgroupsCfg($entity, $file_output),
                    'contacts.cfg' => $pmShinken->generateContactsCfg($entity, $file_output),
                    'servicetemplates.cfg' => $pmShinken->generateServicesTemplatesCfg($entity, $file_output),
                    'services.cfg' => $pmShinken->generateServicesCfg($entity, $file_output),
                    'timeperiods.cfg' => $pmShinken->generateTimeperiodsCfg($entity, $file_output),
                    'realms.cfg' => $pmShinken->generateRealmsCfg($entity, $file_output)
                ];
                break;
        }

        return $output;
    }


    /**
     * Parameters are:
     * - entity, for the required entity tag. If not set or empty, then get all the configured entities
     *
     * Returns the available entities.
     *
     * @param $params
     * @return array
     */
    static function methodgetMonitoredEntities($params)
    {
        self::_manageConnection($params);

        if (isset ($params['help'])) {
            return [
                'entity' => "string, optional. The required entity tag. If none, get all entities that are monitored.",
                'sons' => "string, optional. Set to get the required entity with its sons.",
                'id' => "string, optional. Set to get the identifiers rather than names.",
                'help' => "bool, optional",
            ];
        }

        // Get list of entities tagged with the tag ...
        $pmEntity = new PluginMonitoringEntity();
        $a_entities = $pmEntity->getMonitoredEntities(isset($params['entity']) ? $params['entity'] : '');
//        $a_entities = $pmEntity->getEntitiesByTag(isset($params['entity']) ? $params['entity'] : '',
//            isset($params['sons']) ? true : false, isset($params['id']) ? false : true);

        PluginMonitoringToolbox::log("Monitored entities: " . print_r($a_entities, true));

        return $a_entities;
    }


    static function methodgetConfigCommands($params)
    {
        $params['file'] = 'commands.cfg';
        return self::methodgetConfig($params);
    }


    static function methodgetConfigRealms($params)
    {
        $params['file'] = 'realms.cfg';
        return self::methodgetConfig($params);
    }


    static function methodgetConfigHosts($params)
    {
        $params['file'] = 'hosts.cfg';
        return self::methodgetConfig($params);
    }


    static function methodgetConfigHostgroups($params)
    {
        $params['file'] = 'hostgroups.cfg';
        return self::methodgetConfig($params);
    }


    static function methodgetConfigServices($params)
    {
        $params['file'] = 'services.cfg';
        return self::methodgetConfig($params);
    }


    static function methodgetConfigServicesTemplates($params)
    {
        $params['file'] = 'services_templates.cfg';
        return self::methodgetConfig($params);
    }


    static function methodgetConfigContacts($params)
    {
        $params['file'] = 'contacts.cfg';
        return self::methodgetConfig($params);
    }


    static function methodgetConfigTimeperiods($params)
    {
        $params['file'] = 'timeperiods.cfg';
        return self::methodgetConfig($params);
    }


    /**
     * @param $params
     * @return array
     */
    static function methodDashboard($params)
    {
        self::_manageConnection($params);

        $response = array();

        if (!isset($params['view'])) {
            return $response;
        }

        $pm = new PluginMonitoringDisplay();
        if ($params['view'] == 'Hosts') {
            // Return counters
            return $pm->displayHostsCounters(false);
        } else {
            return $pm->displayCounters($params['view'], 0);
        }
    }


    /**
     * @param $params
     * @return array
     */
    static function methodGetServicesList($params)
    {
        self::_manageConnection($params);

        return PluginMonitoringWebservice::getServicesList($params['statetype'], $params['view']);
    }


    /**
     * @param $statetype
     * @param $view
     * @return array
     */
    static function getServicesList($statetype, $view)
    {
        global $DB;

        $services = array();

        if ($view == 'Ressources') {

            switch ($statetype) {

                case "ok":
                    $query = "SELECT * FROM `glpi_plugin_monitoring_services`
                  LEFT JOIN `glpi_plugin_monitoring_componentscatalogs_hosts`
                     ON `plugin_monitoring_componentscatalogs_hosts_id`=
                        `glpi_plugin_monitoring_componentscatalogs_hosts`.`id`
                  WHERE (`state`='OK' OR `state`='UP') AND `state_type`='HARD'";
                    $result = $DB->query($query);
                    while ($data = $DB->fetch_array($result)) {
                        /* @var CommonDBTM $item */
                        $itemtype = $data['itemtype'];
                        $item = new $itemtype();
                        $item->getFromDB($data['items_id']);

                        $services[] = "(" . $itemtype . ") " . $item->getName() . "\n=> " . $data['name'];
                    }
                    break;

                case "warning":
                    $query = "SELECT * FROM `glpi_plugin_monitoring_services`
                  LEFT JOIN `glpi_plugin_monitoring_componentscatalogs_hosts`
                     ON `plugin_monitoring_componentscatalogs_hosts_id`=
                        `glpi_plugin_monitoring_componentscatalogs_hosts`.`id`
                  WHERE (`state`='WARNING' OR `state`='UNKNOWN' OR `state`='RECOVERY' OR `state`='FLAPPING' OR `state` IS NULL)
                    AND `state_type`='HARD'";
                    $result = $DB->query($query);
                    while ($data = $DB->fetch_array($result)) {
                        $itemtype = $data['itemtype'];
                        $item = new $itemtype();
                        $item->getFromDB($data['items_id']);

                        $services[] = "(" . $itemtype . ") " . $item->getName() . "\n=> " . $data['name'];
                    }
                    break;

                case "critical":
                    $query = "SELECT * FROM `glpi_plugin_monitoring_services`
                  LEFT JOIN `glpi_plugin_monitoring_componentscatalogs_hosts`
                     ON `plugin_monitoring_componentscatalogs_hosts_id`=
                        `glpi_plugin_monitoring_componentscatalogs_hosts`.`id`
                  WHERE (`state`='DOWN' OR `state`='UNREACHABLE' OR `state`='CRITICAL' OR `state`='DOWNTIME')
                    AND `state_type`='HARD'";
                    $result = $DB->query($query);
                    while ($data = $DB->fetch_array($result)) {
                        /* @var CommonDBTM $item */
                        $itemtype = $data['itemtype'];
                        $item = new $itemtype();
                        $item->getFromDB($data['items_id']);

                        $services[] = "(" . $itemtype . ") " . $item->getName() . "\n=> " . $data['name'];
                    }
                    break;
            }

        } else if ($view == 'Componentscatalog') {
            $pmComponentscatalog_Host = new PluginMonitoringComponentscatalog_Host();
            $queryCat = "SELECT * FROM `glpi_plugin_monitoring_componentscatalogs`";
            $resultCat = $DB->query($queryCat);
            while ($data = $DB->fetch_array($resultCat)) {

                $query = "SELECT * FROM `" . $pmComponentscatalog_Host->getTable() . "`
               WHERE `plugin_monitoring_componentscatalogs_id`='" . $data['id'] . "'";
                $result = $DB->query($query);
                $state = array();
                $state['ok'] = 0;
                $state['warning'] = 0;
                $state['critical'] = 0;
                while ($dataComponentscatalog_Host = $DB->fetch_array($result)) {

                    $state['ok'] += countElementsInTable("glpi_plugin_monitoring_services",
                        "(`state`='OK' OR `state`='UP') AND `state_type`='HARD'
                          AND `plugin_monitoring_componentscatalogs_hosts_id`='" . $dataComponentscatalog_Host['id'] . "'");


                    $state['warning'] += countElementsInTable("glpi_plugin_monitoring_services",
                        "(`state`='WARNING' OR `state`='UNKNOWN' OR `state`='RECOVERY' OR `state`='FLAPPING' OR `state` IS NULL)
                          AND `state_type`='HARD'
                          AND `plugin_monitoring_componentscatalogs_hosts_id`='" . $dataComponentscatalog_Host['id'] . "'");

                    $state['critical'] += countElementsInTable("glpi_plugin_monitoring_services",
                        "(`state`='DOWN' OR `state`='UNREACHABLE' OR `state`='CRITICAL' OR `state`='DOWNTIME')
                          AND `state_type`='HARD'
                          AND `plugin_monitoring_componentscatalogs_hosts_id`='" . $dataComponentscatalog_Host['id'] . "'");

                }
                if ($state['critical'] > 0) {
                    if ($statetype == 'critical') {
                        $services[] = "(Catalog) " . $data['name'];
                    }
                } else if ($state['warning'] > 0) {
                    if ($statetype == 'warning') {
                        $services[] = "(Catalog) " . $data['name'];
                    }
                } else if ($state['ok'] > 0) {
                    if ($statetype == 'ok') {
                        $services[] = "(Catalog) " . $data['name'];
                    }
                }
            }
        }
        return $services;
    }


    /**
     * @param $params
     * @return array
     */
    static function methodGetHostsStates($params)
    {
        self::_manageConnection($params);

        return PluginMonitoringWebservice::getHostsStates($params);
    }


    /**
     * @param $params
     * @return array
     */
    static function getHostsStates($params)
    {
        global $DB, $CFG_GLPI;

        $where = $join = $fields = '';
        $join .= "
         INNER JOIN `glpi_computers`
            ON `glpi_plugin_monitoring_hosts`.`items_id` = `glpi_computers`.`id` AND `glpi_plugin_monitoring_hosts`.`itemtype`='Computer'
         INNER JOIN `glpi_entities`
            ON `glpi_computers`.`entities_id` = `glpi_entities`.`id`
         ";

        // Start / limit
        $start = 0;
        $limit = $CFG_GLPI["list_limit_max"];
        if (isset($params['limit']) && is_numeric($params['limit'])) {
            $limit = $params['limit'];
        }
        if (isset($params['start']) && is_numeric($params['start'])) {
            $start = $params['start'];
        }

        // Entities
        if (isset($params['entitiesList'])) {
            if (!Session::haveAccessToAllOfEntities($params['entitiesList'])) {
                return PluginWebservicesMethodCommon::Error(PluginWebservicesMethodCommon::WEBSERVICES_ERROR_NOTALLOWED, '', 'entity');
            }
            $where = getEntitiesRestrictRequest("WHERE", "glpi_computers", '', $params['entitiesList']) .
                $where;
        } else {
            $where = getEntitiesRestrictRequest("WHERE", "glpi_computers") .
                $where;
        }

        // Hosts filter
        if (isset($params['hostsFilter'])) {
            if (is_array($params['hostsFilter'])) {
                $where .= " AND `glpi_computers`.`name` IN ('" . implode("','", $params['hostsFilter']) . "')";
            } else {
                $where .= " AND `glpi_computers`.`name` = " . $params['hostsFilter'];
            }
        }

        // Filter
        if (isset($params['filter']) && !empty($params['filter'])) {
            $where .= " AND " . $params['filter'];
        }
        // Order
        $order = "FIELD(`glpi_plugin_monitoring_hosts`.`state`,'DOWN','PENDING','UNKNOWN','UNREACHABLE','UP'), entity_name ASC";
        if (isset($params['order'])) {
            $order = $params['order'];
        }

        $query = "SELECT
            `glpi_entities`.`name` AS entity_name,
            `glpi_computers`.`id`,
            `glpi_computers`.`name`,
            `glpi_plugin_monitoring_hosts`.`state`,
            `glpi_plugin_monitoring_hosts`.`state_type`,
            `glpi_plugin_monitoring_hosts`.`event`,
            `glpi_plugin_monitoring_hosts`.`last_check`,
            `glpi_plugin_monitoring_hosts`.`perf_data`,
            `glpi_plugin_monitoring_hosts`.`is_acknowledged`,
            `glpi_plugin_monitoring_hosts`.`acknowledge_comment`
         FROM `glpi_plugin_monitoring_hosts`
         $join
         $where
         ORDER BY $order
         LIMIT $start,$limit;
      ";
        // PluginMonitoringToolbox::log("pm-ws", "getHostsStates, query : $query\n");
        $rows = array();
        $result = $DB->query($query);
        while ($data = $DB->fetch_array($result)) {
            $row = array();
            foreach ($data as $key => $value) {
                if (is_string($key)) {
                    $row[$key] = $value;
                }
            }
            $rows[] = $row;
        }

        return $rows;
    }


    /**
     * @param $params
     * @return array
     */
    static function methodGetHostsLocations($params)
    {
        self::_manageConnection($params);

        return PluginMonitoringWebservice::getHostsLocations($params);
    }


    /**
     * @param $params
     * @return array
     */
    static function getHostsLocations($params)
    {
        global $DB, $CFG_GLPI;

        $where = $join = $fields = '';
        $join .= "
         LEFT JOIN `glpi_plugin_monitoring_hosts`
            ON `glpi_plugin_monitoring_hosts`.`items_id` = `glpi_computers`.`id` AND `glpi_plugin_monitoring_hosts`.`itemtype`='Computer'
         LEFT JOIN `glpi_entities`
            ON `glpi_computers`.`entities_id` = `glpi_entities`.`id`
         LEFT JOIN `glpi_locations`
            ON `glpi_locations`.`id` = `glpi_computers`.`locations_id`
         ";

        // Start / limit
        $start = 0;
        $limit = $CFG_GLPI["list_limit_max"];
        if (isset($params['limit']) && is_numeric($params['limit'])) {
            $limit = $params['limit'];
        }
        if (isset($params['start']) && is_numeric($params['start'])) {
            $start = $params['start'];
        }

        // Entities
        if (isset($params['entitiesList'])) {
            if (!Session::haveAccessToAllOfEntities($params['entitiesList'])) {
                return PluginWebservicesMethodCommon::Error(PluginWebservicesMethodCommon::WEBSERVICES_ERROR_NOTALLOWED, '', 'entity');
            }
            $where = getEntitiesRestrictRequest("WHERE", "glpi_computers", '', $params['entitiesList']) .
                $where;
        } else {
            $where = getEntitiesRestrictRequest("WHERE", "glpi_computers") .
                $where;
        }

        // Hosts filter
        if (isset($params['hostsFilter'])) {
            if (is_array($params['hostsFilter'])) {
                $where .= " AND `glpi_computers`.`name` IN ('" . implode("','", $params['hostsFilter']) . "')";
            } else {
                $where .= " AND `glpi_computers`.`name` = '" . $params['hostsFilter'] . "'";
            }
        }

        // Filter
        if (isset($params['filter'])) {
            $where .= " AND " . $params['filter'];
        }

        // Order
        $order = "entity_name ASC, location ASC, FIELD(`glpi_plugin_monitoring_hosts`.`state`,'DOWN','PENDING','UNKNOWN','UNREACHABLE','UP')";
        if (isset($params['order'])) {
            $order = $params['order'];
        }

        $query = "
         SELECT
            `glpi_computers`.`id` AS id,
            `glpi_computers`.`name` AS name,
            `glpi_computers`.`serial` AS serial,
            `glpi_computers`.`otherserial` AS inventory,
            `glpi_computers`.`comment` AS comment,
            `glpi_entities`.`id` AS entity_id,
            `glpi_entities`.`name` AS entity_name,
            `glpi_entities`.`completename` AS entity_completename,
            `glpi_locations`.`building` AS gps,
            `glpi_locations`.`name` AS short_location,
            `glpi_locations`.`completename` AS location,
            `glpi_plugin_monitoring_hosts`.`id` as monitoring_id,
            `glpi_plugin_monitoring_hosts`.`state`,
            `glpi_plugin_monitoring_hosts`.`state_type`,
            `glpi_plugin_monitoring_hosts`.`event`,
            `glpi_plugin_monitoring_hosts`.`last_check`,
            `glpi_plugin_monitoring_hosts`.`perf_data`,
            `glpi_plugin_monitoring_hosts`.`is_acknowledged`,
            `glpi_plugin_monitoring_hosts`.`is_acknowledgeconfirmed`,
            `glpi_plugin_monitoring_hosts`.`acknowledge_comment`
         FROM `glpi_computers`
         $join
         $where
         ORDER BY $order
         LIMIT $start,$limit;
      ";
        // PluginMonitoringToolbox::log("pm-ws", "getHostsLocations, query : $query\n");
        $rows = array();
        $result = $DB->query($query);
        while ($data = $DB->fetch_array($result)) {
            $row = array();
            foreach ($data as $key => $value) {
                if (is_string($key)) {
                    $row[$key] = $value;
                }
            }
            // Default GPS coordinates ...
            $row['lat'] = 45.054485;
            $row['lng'] = 5.081413;
            if (!empty($row['gps'])) {
                $split = explode(',', $row['gps']);
                if (count($split) > 1) {
                    // At least 2 elements, let us consider as GPS coordinates ...
                    $row['lat'] = $split[0];
                    $row['lng'] = $split[1];
                }
                unset ($row['gps']);
            }

            // Fetch host services
            $services = PluginMonitoringWebservice::getServicesStates(
                array(
                    'start' => 0,
                    'limit' => 100,
                    'entity' => isset($params['entity']) ? $params['entity'] : null,
                    'filter' => "glpi_computers.name='" . $row['name'] . "'",
                    'servicesFilter' => isset($params['servicesFilter']) ? $params['servicesFilter'] : '',
                    'order' => "`glpi_plugin_monitoring_components`.`name` ASC"
                )
            );
            $row['services'] = $services;
            $rows[] = $row;
        }

        return $rows;
    }


    /**
     * @param $params
     * @return array
     */
    static function methodGetServicesStates($params)
    {
        self::_manageConnection($params);

        return PluginMonitoringWebservice::getServicesStates($params);
    }

    /**
     * Request statistics on table with parameters
     * - start / limit
     * - filter
     * - entity
     * - order:
     * 'hostname' : sort by hostname
     * 'day' : sort by day
     */
    static function getServicesStates($params)
    {
        global $DB, $CFG_GLPI;

        $where = $join = $fields = '';
        $join .= "
         INNER JOIN `glpi_plugin_monitoring_services`
            ON (`glpi_plugin_monitoring_services`.`plugin_monitoring_componentscatalogs_hosts_id` = `glpi_plugin_monitoring_componentscatalogs_hosts`.`id`)
         INNER JOIN `glpi_plugin_monitoring_hosts`
            ON `glpi_plugin_monitoring_componentscatalogs_hosts`.`items_id` = `glpi_plugin_monitoring_hosts`.`items_id` AND `glpi_plugin_monitoring_componentscatalogs_hosts`.`itemtype` = `glpi_plugin_monitoring_hosts`.`itemtype`
         INNER JOIN `glpi_plugin_monitoring_componentscatalogs`
            ON `plugin_monitoring_componentscatalogs_id` = `glpi_plugin_monitoring_componentscatalogs`.`id`
         INNER JOIN `glpi_plugin_monitoring_components`
            ON (`glpi_plugin_monitoring_services`.`plugin_monitoring_components_id` = `glpi_plugin_monitoring_components`.`id`)
         LEFT JOIN `glpi_computers`
            ON `glpi_plugin_monitoring_componentscatalogs_hosts`.`items_id` = `glpi_computers`.`id` AND `glpi_plugin_monitoring_componentscatalogs_hosts`.`itemtype`='Computer'
         LEFT JOIN `glpi_printers`
            ON `glpi_plugin_monitoring_componentscatalogs_hosts`.`items_id` = `glpi_printers`.`id` AND `glpi_plugin_monitoring_componentscatalogs_hosts`.`itemtype`='Printer'
         LEFT JOIN `glpi_networkequipments`
            ON `glpi_plugin_monitoring_componentscatalogs_hosts`.`items_id` = `glpi_networkequipments`.`id` AND `glpi_plugin_monitoring_componentscatalogs_hosts`.`itemtype`='NetworkEquipment'
         ";

        // Start / limit
        $start = 0;
        $limit = $CFG_GLPI["list_limit_max"];
        if (isset($params['limit']) && is_numeric($params['limit'])) {
            $limit = $params['limit'];
        }
        if (isset($params['start']) && is_numeric($params['start'])) {
            $start = $params['start'];
        }

        // Entities
        if (isset($params['entitiesList'])) {
            if (!Session::haveAccessToAllOfEntities($params['entitiesList'])) {
                return PluginWebservicesMethodCommon::Error(WEBSERVICES_ERROR_NOTALLOWED, '', 'entity');
            }
            $where = getEntitiesRestrictRequest("WHERE", "glpi_computers", '', $params['entitiesList']) .
                $where;
        } else {
            $where = getEntitiesRestrictRequest("WHERE", "glpi_computers") .
                $where;
        }

        // Services filter
        if (isset($params['servicesFilter']) && !empty($params['servicesFilter'])) {
            if (is_array($params['servicesFilter'])) {
                $where .= " AND `glpi_plugin_monitoring_components`.`name` IN ('" . implode("','", $params['servicesFilter']) . "')";
            } else {
                $where .= " AND `glpi_plugin_monitoring_components`.`name` = '" . $params['servicesFilter'] . "'";
            }
        }

        // Filter
        if (isset($params['filter']) && !empty($params['filter'])) {
            $where .= " AND " . $params['filter'];
        }
        // Order
        $order = "FIELD(`glpi_plugin_monitoring_services`.`state`, 'CRITICAL','PENDING','UNKNOWN','WARNING','OK')";
        if (isset($params['order'])) {
            $order = $params['order'];
        }

        $query = "
         SELECT
            CONCAT_WS('', `glpi_computers`.`name`, `glpi_printers`.`name`, `glpi_networkequipments`.`name`) AS host_name,
            `glpi_plugin_monitoring_components`.`name`,
            `glpi_plugin_monitoring_components`.`description`,
            `glpi_plugin_monitoring_services`.`state`,
            `glpi_plugin_monitoring_services`.`state_type`,
            `glpi_plugin_monitoring_services`.`event`,
            `glpi_plugin_monitoring_services`.`last_check`,
            `glpi_plugin_monitoring_services`.`is_acknowledged`,
            `glpi_plugin_monitoring_services`.`acknowledge_comment`
         FROM `glpi_plugin_monitoring_componentscatalogs_hosts`
         $join
         $where
         ORDER BY $order
         LIMIT $start,$limit;
      ";
        // PluginMonitoringToolbox::log("pm-ws", "getServicesStates, query : $query\n");
        $rows = array();
        $result = $DB->query($query);
        while ($data = $DB->fetch_array($result)) {
            $row = array();
            foreach ($data as $key => $value) {
                if (is_string($key)) {
                    $row[$key] = $value;
                }
            }
            $rows[] = $row;
        }

        return $rows;
    }
}