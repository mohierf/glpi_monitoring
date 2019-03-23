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

class PluginMonitoringShinken extends CommonDBTM
{
    const INITIAL_HOST_STATE = 'UNKNOWN';
    const INITIAL_HOST_STATE_TYPE = 'HARD';

    const INITIAL_SERVICE_STATE = 'UNKNOWN';
    const INITIAL_SERVICE_STATE_TYPE = 'HARD';

    const HOSTGROUP_LEVEL = 3;

    // Comment to remove custom variable from host/service configuration
    public static $default = [
        // GLPI root entity name
        'behaviour' => [
            // Use computer model as an host template
            'useModel' => true,
            // Use computer type as an host template
            'useType' => true,
        ],
        // GLPI root entity name
        'glpi' => [
            // Root entity name
            'rootEntity' => '',
            // Host id
            'hostId' => '_HOSTSID',
            // Entity id
            'entityId' => '_ENTITIESID',
            // Entity name
            'entityName' => '_ENTITY',
            // Client, group and site names
            'clientName' => '_client',
            'groupName' => '_group',
            'siteName' => '_site',
            // Entity complete
            'entityComplete' => '_ENTITY_COMPLETE',
            // Item type
            'itemType' => '_ITEMTYPE',
            // Item id
            'itemId' => '_ITEMSID',
            // Not defined - no more used!
//            // Location
//            'location' => '_LOC_NAME',
//            // Latitude
//            'lat' => '_LOC_LAT',
//            // Longitude
//            'lng' => '_LOC_LNG',
//            // Altitude
//            'alt' => '_LOC_ALT',
            // Full GPS
            'gps' => '_GPS',
            // documents
            'documents' => '_DOCUMENTS',
            // some interesting computer fields
            'fields' => [
                'id', 'entities_id', 'name', 'comment', 'serial', 'otherserial',
                'contact', 'contactnum', 'date_creation', 'date_mod'
            ]
        ],
        // Shinken configuration
        'shinken' => [
            'hosts' => [
                // Default check_period
                'check_period' => '24x7',
                // Default values
                // 'use' => 'important',
                'business_impact' => 3,
                'process_perf_data' => '1',
                // Default hosts notifications : none !
                'notifications_enabled' => '0',
                'notification_period' => '24x7',
                'notification_options' => 'd,u,r,f,s',
                'notification_interval' => 86400,
                'first_notification_delay' => 0,
                'flap_detection_enabled' => '0',
                'flap_detection_options' => 'o,d,x',
                'low_flap_threshold' => '25',
                'high_flap_threshold' => '50',

                'stalking_options' => '',

                'failure_prediction_enabled' => '0',
                // Set as 'entity' to use hostgroupname else use the defined value ...
                'parents' => 'entity',

                'notes_url' => '',
                'action_url' => '',
                'icon_image' => '',
                'icon_image_alt' => '',
                'vrml_image' => '',
                'statusmap_image' => '',
            ],
            'services' => [
                // Default check_period - leave empty to use check period defined for the host.
                'check_period' => '',
                // Default values
                'business_impact' => 3,
                'process_perf_data' => 1,
                // Default services notifications : none !
                'notifications_enabled' => 0,
                'notification_period' => '24x7',
                'notification_options' => 'w,u,c,r,f,s',
                'notification_interval' => 86400,
                'first_notification_delay' => 0,
                'flap_detection_enabled' => 0,
                'flap_detection_options' => 'o,w,c,u',
                'low_flap_threshold' => 25,
                'high_flap_threshold' => 50,

                'stalking_options' => '',

                'failure_prediction_enabled' => 0,

                'notes' => '',
                'notes_url' => '',
                'action_url' => '',
                'icon_image' => '',
                'icon_image_alt' => '',
            ],
            'contacts' => [
                // Default user category
                'user_category' => 'glpi',
                // Default user's note : this prefix + monitoring template name
                'note' => 'Monitoring template : ',
                // Default host/service notification period
                'host_notification_period' => '24x7',
                'service_notification_period' => '24x7',
            ]
        ],
        // Graphite configuration
        'graphite' => [
            // Prefix
            'prefix' => [
                'name' => '_GRAPHITE_PRE',
                'value' => '',
                'entity' => true
            ]
        ],
        // WebUI configuration
        'webui' => [
            // Hosts custom view
            'hostView' => [
                'name' => 'custom_views',
                'value' => 'kiosk'
            ],
            // Contacts role
            'contacts' => [
                // Used if not defined in contact template
                'is_admin' => '0',
                'can_submit_commands' => '0',
                // Use this password if user has an empty password
                'password' => 'shinken'
            ],
        ],
    ];

    public static $accentsource = ['À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā', 'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę', 'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĳ', 'ĳ', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ', 'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ', 'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư', 'Ǎ', 'ǎ', 'Ǐ', 'ǐ', 'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ', 'Ǻ', 'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 'ǿ', 'Ά', 'ά', 'Έ', 'έ', 'Ό', 'ό', 'Ώ', 'ώ', 'Ί', 'ί', 'ϊ', 'ΐ', 'Ύ', 'ύ', 'ϋ', 'ΰ', 'Ή', 'ή'];
    public static $accentdestination = ['A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o', 'Α', 'α', 'Ε', 'ε', 'Ο', 'ο', 'Ω', 'ω', 'Ι', 'ι', 'ι', 'ι', 'Υ', 'υ', 'υ', 'υ', 'Η', 'η'];

    function writeFile($object_type, $data)
    {
        $content = '';
        if (isset($data['file_comment'])) {
            $content .= "# " . $data['file_comment'] . "\n";
            unset($data['file_comment']);
        }
        $content .= "define " . $object_type . "{\n";
        foreach ($data as $key => $value) {
            $c = 35;
            $c = $c - strlen($key);
            $content .= "       " . $key;
            for ($t = 0; $t < $c; $t++) {
                $content .= " ";
            }
            $content .= $value . "\n";
        }
        $content .= "}\n";
        $content .= "\n";

        return $content;
    }


    static function monitoringFilter($str, $lowercase = false)
    {
        $str = str_replace(PluginMonitoringShinken::$accentsource,
            PluginMonitoringShinken::$accentdestination, $str);
        if ($lowercase) {
            $str = strtolower($str);
        }
        $str = preg_replace("/\s/", "_", $str);
        return preg_replace("/[^A-Za-z0-9\_-]/", "", $str);
    }


    static function graphiteFilter($str, $lowercase = true)
    {
        $str = str_replace(PluginMonitoringShinken::$accentsource,
            PluginMonitoringShinken::$accentdestination, $str);
        if ($lowercase) {
            $str = strtolower($str);
        }
        $str = preg_replace("/\s/", "_", $str);
        $str = preg_replace("/_-_/", ".", $str);
        return preg_replace("/[^A-Za-z0-9._-]/", "", $str);
    }


    static function graphiteEntity($entityFullName, $entity_prefix)
    {
        // Dynamic setup of a default parameter ...
        if (empty(self::$default['glpi']['rootEntity'])) {
            $entity = new Entity();
            $entity->getFromDB('0');
            self::$default['glpi']['rootEntity'] = $entity->getName();
        }

        $entityFullName = preg_replace("/" . self::$default['glpi']['rootEntity'] . " > /", "", $entityFullName);
        $entityFullName = preg_replace("/ > /", " - ", $entityFullName);
//        $entityFullName = preg_replace("/#/", "_", $entityFullName);
        $entityFullName = preg_replace("/_-_/", ".", $entityFullName);

        // Graphite prefix
        if (isset(self::$default['graphite']['prefix']['name'])) {
            // Get the Graphite prefix defined for the current entity
            $default_prefix = self::$default['graphite']['prefix']['value'];

            if (self::$default['graphite']['prefix']['entity']) {
                if (!empty($default_prefix)) {
                    $entity_prefix = $default_prefix . '.' . $entity_prefix;
                }
                if (!empty($entity_prefix)) {
                    $entity_prefix = $entity_prefix . '.' . $entityFullName;
                } else {
                    $entity_prefix = $entityFullName;
                }
            }
            $entityFullName = $entity_prefix;
        }

        return self::graphiteFilter($entityFullName, false);
    }


    function generateCommandsCfg($tag = '', $file = false)
    {
        global $PM_CONFIG;

        PluginMonitoringToolbox::logIfDebug("Starting generateCommandsCfg ...");
        $pmCommand = new PluginMonitoringCommand();
        $pmNotificationcommand = new PluginMonitoringNotificationcommand();
        $pmEventhandler = new PluginMonitoringEventhandler();

        $a_commands = [];

        // Only active commands and notification commands ...
        $a_list = $pmCommand->find("`is_active`='1'");
        $a_listnotif = $pmNotificationcommand->find("`is_active`='1'");
        $a_list = array_merge($a_list, $a_listnotif);

        foreach ($a_list as $data) {
            if ($data['command_name'] == "bp_rule") continue;

            $my_command = [];

            // For comments ...
            if ($file) {
                $this->set_value($data['name'], 'file_comment', $my_command);
            }

            // For the framework configuration...
            $this->set_value(PluginMonitoringCommand::$command_prefix . $data['command_name'], 'command_name', $my_command);
            $this->set_value($data['command_line'], 'command_line', $my_command);
            if (!empty($data['module_type'])) {
                $this->set_value($data['module_type'], 'module_type', $my_command);
            }
            if (!empty($data['poller_tag'])) {
                $this->set_value($data['poller_tag'], 'poller_tag', $my_command);
            }
            if (!empty($data['reactionner_tag'])) {
                $this->set_value($data['reactionner_tag'], 'reactionner_tag', $my_command);
            }

            PluginMonitoringToolbox::logIfDebug("- command: " . $my_command['command_name']);
            $my_command = $this->properties_list_to_string($my_command);
            $a_commands[] = $my_command;
        }

        // Event handlers
        $a_list = $pmEventhandler->find("`is_active`='1'");
        foreach ($a_list as $data) {
            if ($data['command_name'] == "bp_rule") continue;

            $my_command = [];

            // For comments ...
            if ($file) {
                $this->set_value($data['name'], 'file_comment', $my_command);
            }

            $this->set_value(PluginMonitoringCommand::$command_prefix . $data['command_name'], 'command_name', $my_command);
            $this->set_value($data['command_line'], 'command_line', $my_command);

            PluginMonitoringToolbox::logIfDebug("- event hadler: " . $my_command['command_name'] . " -> " . $my_command['name']);
            $my_command = $this->properties_list_to_string($my_command);
            $a_commands[] = $my_command;
        }


        PluginMonitoringToolbox::log("Found " . count($a_commands) . " commands");
        PluginMonitoringToolbox::logIfDebug("End generateCommandsCfg");

        if ($PM_CONFIG['build_files']) {
            $config = "# Generated by the monitoring plugin for GLPI\n# on " . date("Y-m-d H:i:s") . "\n";
            $config .= "# ---\n";
            $config .= "# Commands\n";
            $config .= "# ---\n";

            foreach ($a_commands as $data) {
                $config .= $this->writeFile("command", $data);
            }
            $filename = PLUGIN_MONITORING_CFG_DIR . '/' . $tag . '-commands.cfg';
            file_put_contents($filename, $config);
            PluginMonitoringToolbox::logIfDebug("Written file: " . $filename);

            if ($file) return $config;
        }

        return $a_commands;
    }


    function generateHostsCfg($tag = '', $file = false)
    {
        global $DB, $PM_CONFIG, $TIMER_DEBUG;

        PluginMonitoringToolbox::logIfDebug("Starting generateHostsCfg ($tag) ...");

        $pmCommand = new PluginMonitoringCommand();
        $pmCheck = new PluginMonitoringCheck();
        $pmComponent = new PluginMonitoringComponent();
        $pmCC = new PluginMonitoringComponentscatalog();
        $pmHostconfig = new PluginMonitoringHostconfig();
        $pmHNTemplate = new PluginMonitoringHostnotificationtemplate();
        $pmHost = new PluginMonitoringHost();
        $calendar = new Calendar();
        $pmRealm = new PluginMonitoringRealm();
        $pmEventhandler = new PluginMonitoringEventhandler();
        $pmContact_Item = new PluginMonitoringContact_Item();
        $user = new User();
        $pmConfig = new PluginMonitoringConfig();

        $default_host = self::$default['shinken']['hosts'];

        $pmConfig->getFromDB(1);

        $a_hosts = [];
        $a_templates = [];
        $a_templates_found = [];
//        $a_documents_found = [];
        $a_hosts_found = [];

        // Get entities concerned by the provided tag and get the definition order of the highest entty
        $where = '';
        if (!empty($_SESSION['plugin_monitoring']['allowed_entities'])) {
            $where = getEntitiesRestrictRequest("WHERE",
                "glpi_entities", '',
                $_SESSION['plugin_monitoring']['allowed_entities']);
        }

        // Huge query to get almost all in one operation!
        $query = "SELECT
         `glpi_plugin_monitoring_componentscatalogs_hosts`.*,
         `glpi_computers`.`id` AS id,
         `glpi_computers`.`comment` AS comment,
         `glpi_entities`.`id` AS entityId, `glpi_entities`.`name` AS entityName,
         `glpi_entities`.`completename` AS entityFullName,
         `glpi_computertypes`.`name` AS typeName,
         `glpi_computertypes`.`comment` AS typeComment,
         `glpi_computermodels`.`name` AS modelName,
         `glpi_computermodels`.`comment` AS modelComment,
         `glpi_locations`.`id`, `glpi_locations`.`completename` AS locationName,
         `glpi_locations`.`comment` AS locationComment, 
         `glpi_locations`.`latitude` AS lat, 
         `glpi_locations`.`longitude` AS lng, 
         `glpi_locations`.`altitude` AS alt
         FROM `glpi_plugin_monitoring_componentscatalogs_hosts`
         LEFT JOIN `glpi_computers`
            ON `glpi_computers`.`id` = `glpi_plugin_monitoring_componentscatalogs_hosts`.`items_id`
               AND `glpi_plugin_monitoring_componentscatalogs_hosts`.`itemtype`='Computer'

         LEFT JOIN `glpi_entities`
            ON `glpi_computers`.`entities_id` = `glpi_entities`.`id` 
         LEFT JOIN `glpi_computertypes` 
            ON `glpi_computertypes`.`id` = `glpi_computers`.`computertypes_id`
         LEFT JOIN `glpi_computermodels` 
            ON `glpi_computermodels`.`id` = `glpi_computers`.`computermodels_id`
         LEFT JOIN `glpi_locations` 
            ON `glpi_locations`.`id` = `glpi_computers`.`locations_id`
         $where GROUP BY `itemtype`, `items_id`";
        PluginMonitoringToolbox::log("generateHostsCfg, query: " . $query);

        if ($result = $DB->query($query)) {
            PluginMonitoringToolbox::log("generateHostsCfg, huge query execution, got " . $DB->numrows($result) . " rows, duration: " . $TIMER_DEBUG->getTime());
            while ($data = $DB->fetch_array($result)) {
                $my_host = [];
                $new_template = null;
                $my_template = [];

                /* @var Computer $my_host_item */
                $my_host_type = $data['itemtype'];
                $my_host_item = new $my_host_type;
                if (!$my_host_item->getFromDB($data['items_id'])) {
                    PluginMonitoringToolbox::log('[ERROR] Host item not found: ' . print_r($data, true));
                    continue;
                }
                PluginMonitoringToolbox::log('[computer]: ' . $my_host_item->getName());
                PluginMonitoringToolbox::logIfDebug('[computer] : ' . print_r($my_host_item, true));

                if (!$pmHost->getFromDBByCrit([
                    'itemtype' => $data['itemtype'], 'items_id' => $data["items_id"]])) {
                    if (!$pmHost->getFromDBByCrit(['host_name' => $my_host_item->getName()])) {
                        PluginMonitoringToolbox::log('[ERROR] Host monitoring item not found: ' . print_r($data, true));
                        continue;
                    }
                }
                // Update monitoring host entity
                if ($pmHost->fields['entities_id'] != $data['entityId']) {
                    $pmHost->update([
                        'id' => $pmHost->getID(),
                        'entities_id' => $data['entityId']
                    ]);
                }

                // Host component catalog
                if (!$pmCC->getFromDB($data['plugin_monitoring_componentscatalogs_id'])) {
                    PluginMonitoringToolbox::log('[ERROR] Host components catalog item not found: ' . print_r($data, true));
                    continue;
                }

                // Fix: if hostname is not defined ...
                if (empty($my_host_item->getName())) {
                    continue;
                }

                if (!in_array($data['plugin_monitoring_componentscatalogs_id'], $a_templates_found)) {
                    // Still got this component catalog as a template
                    PluginMonitoringToolbox::logIfDebug('Got a new template from a CC: ' . $data['plugin_monitoring_componentscatalogs_id']);
                    $new_template = self::monitoringFilter($pmCC->getName());
                    $a_templates_found[] = $data['plugin_monitoring_componentscatalogs_id'];
                }

                // Template is name, not host_name
                if ($new_template) {
                    $this->set_value($new_template, 'name', $my_template);
                    $this->set_value('0', 'register', $my_template);
                    PluginMonitoringToolbox::log("adding host template: " . $my_template['name']);
                }

                //  Set host name with an id if globally configured for this
                $this->set_value(self::monitoringFilter($my_host_item->getName()), 'host_name', $my_host);
                if ($PM_CONFIG['append_id_hostname'] == 1) {
                    $this->set_value(self::monitoringFilter($my_host_item->getName() . '-' . $my_host_item->getID()), 'host_name', $my_host);
                }

                // Set host templates as CC name and its additional templates if any are defined
                $this->set_value(self::monitoringFilter($pmCC->getName()), 'use', $my_host);
                if (!empty($pmCC->getField('additional_templates'))) {
                    $this->set_value($pmCC->getField('additional_templates'), 'use', $my_host);
                }

                if (self::$default['behaviour']['useType']) {
                    $this->set_value($data['typeName'], 'use', $my_host);
                }
                if (self::$default['behaviour']['useModel']) {
                    $this->set_value($data['modelName'], 'use', $my_host);
                }

                // - client, group and site name
                $infos = $pmHost->getClientEntity();
                $client_name = $infos[0];
                $group_name = $infos[1];
                $site_name = $infos[2];
                $this->set_value($client_name, self::$default['glpi']['clientName'], $my_host);
                $this->set_value($group_name, self::$default['glpi']['groupName'], $my_host);
                $this->set_value($site_name, self::$default['glpi']['siteName'], $my_host);

                PluginMonitoringToolbox::log("adding host " . $my_host['host_name'] . " for $client_name, using: " . implode(',', $my_host['use']));

                $a_hosts_found[$my_host_item->getName()] = false;

                /*
                // Host Documents of the CC
                $document_obj = new Document();
                $document_item_obj = new Document_Item();
                $document_items = $document_item_obj->find("itemtype = 'PluginMonitoringComponentscatalog' AND items_id = " . $pmCC->getID());
                foreach ($document_items as $document_item) {
                    $document_obj->getFromDB($document_item['documents_id']);

                    $filepath = GLPI_DOC_DIR . ' / ' . $document_obj->fields['filepath'];
                    if (file_exists($filepath)) {
                        if (!in_array($filepath, $a_documents_found, true)) {
                            $a_documents_found[] = $filepath;
                            PluginMonitoringToolbox::logIfDebug('got a new CC document: ' . print_r($document_obj->fields, true));
                            $this->set_value($document_obj->getID(), self::$default['glpi']['documents'], $my_template);
                        }
                    } else {
                        PluginMonitoringToolbox::logIfDebug('CC document does not exist as a file: ' . $filepath);
                    }
                }
                */

                // Host specific attributes
                // Host customs variables - Glpi interesting fields
                // Extra parameters
                foreach (self::$default['glpi']['fields'] as $parm) {
                    if (isset($my_host_item->fields[$parm])) {
                        $this->set_value($my_host_item->fields[$parm], '_glpi_' . $parm, $my_host);
                    }
                }

                // Host customs variables
                // - monitoring host identifier
                $this->set_value($pmHost->getID(), self::$default['glpi']['hostId'], $my_host);
                // - host type
                $this->set_value($my_host_type, self::$default['glpi']['itemType'], $my_host);
                // - host identifier
                $this->set_value($data['items_id'], self::$default['glpi']['itemId'], $my_host);
                // - entity identifier
                $this->set_value($data['entityId'], self::$default['glpi']['entityId'], $my_host);
                // - entity name
                $this->set_value(strtolower(self::monitoringFilter($data['entityName'])),
                    self::$default['glpi']['entityName'], $my_host);
                // - entity complete name
                $this->set_value(self::monitoringFilter($data['entityFullName']), self::$default['glpi']['entityComplete'], $my_host);

                // - some other Glpi information
                $this->set_value($data['entityId'], '_glpi_entity_id', $my_host);
                $this->set_value($data['locationName'], '_glpi_location_name', $my_host);
                $this->set_value($data['entityFullName'], '_glpi_entity_full_name', $my_host);
                $this->set_value($data['entityName'], '_glpi_entity_name', $my_host);
                $this->set_value($data['typeName'], '_glpi_type_name', $my_host);
                $this->set_value($data['typeComment'], '_glpi_type_comment', $my_host);
                $this->set_value($data['modelName'], '_glpi_model_name', $my_host);
                $this->set_value($data['modelComment'], '_glpi_model_comment', $my_host);

//                // Graphite prefix - from the entity full name
//                if (isset(self::$default['graphite']['prefix']['name'])) {
//                    $data['entityFullName'] = self::graphiteEntity(
//                        $data['entityFullName'],
//                        $_SESSION['plugin_monitoring']['entities'][$data['entityId']]['graphite_prefix']);
//
//                    $this->set_value($data['entityFullName'],
//                        self::$default['graphite']['prefix']['name'], $my_host);
//                }
//
                // Graphite prefix - from the client name
                if (isset(self::$default['graphite']['prefix']['name'])) {
                    $this->set_value(self::graphiteFilter($client_name, true),
                        self::$default['graphite']['prefix']['name'], $my_host);
                }

                // Location and GPS
                if (isset(self::$default['glpi']['location'])) {
                    if (!empty($data['locationName'])) {
                        $string = preg_replace("/[\r\n]/", ".", $data['locationName']);
                        $this->set_value($this->monitoringFilter($string), self::$default['glpi']['location'], $my_host);
                        $data['hostLocation'] = $string;
                    }
                }
                if (isset(self::$default['glpi']['lat']) and !empty($data['lat'])) {
                    $this->set_value($data['lat'], self::$default['glpi']['lat'], $my_host);
                }
                if (isset(self::$default['glpi']['lng']) and !empty($data['lng'])) {
                    $this->set_value($data['lng'], self::$default['glpi']['lng'], $my_host);
                }
                if (isset(self::$default['glpi']['alt']) and !empty($data['alt'])) {
                    $this->set_value($data['alt'], self::$default['glpi']['alt'], $my_host);
                }
                if (isset(self::$default['glpi']['lat']) and !empty($data['lat'])
                    and isset(self::$default['glpi']['lng']) and !empty($data['lng'])) {
                    $this->set_value($data['lat'] . ',' . $data['lng'], self::$default['glpi']['gps'], $my_host);
                }

                // Hostgroup name
                $this->set_value(preg_replace("/[ ]/", "_", self::monitoringFilter($data['entityName'])), 'hostgroups', $my_host);

                // Alias
                $this->set_value($this->monitoringFilter($data['entityName']) . " / " . $my_host['host_name'], 'alias', $my_host);
                if (isset($data['hostLocation'])) {
                    $this->set_value($my_host['alias'] . " (" . $data['hostLocation'] . ")", 'alias', $hn);
                }

                // For comments ...
                if ($file) {
                    $this->set_value($my_host['alias'], 'file_comment', $my_host);
                }

                // IP address
                $ip = PluginMonitoringHostaddress::getIp($data['items_id'], $data['itemtype'], $my_host_item->fields['name']);
                $this->set_value($ip, 'address', $my_host);

                if ($new_template) {
                    // Web UI host view
                    if (isset(self::$default['webui']['hostView']['name'])) {
                        $this->set_value(self::$default['webui']['hostView']['value'],
                            self::$default['webui']['hostView']['name'], $my_template);
                    }

                    // Host check command
                    $cmp_fields = null;
                    $hc_id = $pmHostconfig->getValueAncestor('plugin_monitoring_components_id',
                        $my_host_item->fields['entities_id'], $my_host_type, $my_host_item->getID());
                    if (!$pmComponent->getFromDB($hc_id)) {
                        PluginMonitoringToolbox::log("[ERROR] no monitoring component for " . $my_host['host_name']);
                    } else {
                        $cmp_fields = $pmComponent->fields;
                        PluginMonitoringToolbox::logIfDebug("monitoring component for " . $my_host['host_name']);
                    }

                    // Host check command (it may not exist!)
                    if (isset($cmp_fields['plugin_monitoring_commands_id'])
                        and $pmCommand->getFromDB($cmp_fields['plugin_monitoring_commands_id'])) {
                        // Manage host check_command arguments
                        $array = [];
                        preg_match_all("/\\$(ARG\d+)\\$/", $pmCommand->fields['command_line'], $array);
                        sort($array[0]);
                        $a_arguments = importArrayFromDB($pmCommand->fields['arguments']);
                        $a_argumentscustom = importArrayFromDB($cmp_fields['arguments']);
                        foreach ($a_argumentscustom as $key => $value) {
                            $a_arguments[$key] = $value;
                        }
                        foreach ($a_arguments as $key => $value) {
                            $a_arguments[$key] = str_replace('!', '\!', html_entity_decode($value));
                        }
                        $args = '';
                        $notadddescription = '';
                        // todo: to be tested!

                        foreach ($array[0] as $arg) {
                            if (in_array($arg, ['$PLUGINSDIR$', '$NAGIOSPLUGINSDIR$', '$HOSTADDRESS$', '$MYSQLUSER$', '$MYSQLPASSWORD$'])) {
                                continue;
                            }

                            $arg = str_replace('$', '', $arg);
                            if (!isset($a_arguments[$arg])) {
                                $args .= '!';
                            } else {
                                if (strstr($a_arguments[$arg], "[[HOSTNAME]]")) {
                                    $a_arguments[$arg] = str_replace("[[HOSTNAME]]", $my_host_item->fields['name'], $a_arguments[$arg]);

                                    /* mohierf: disable this feature
                                    } elseif (strstr($a_arguments[$arg], "[[NETWORKPORTDESCR]]")) {
                                        if (class_exists("PluginFusioninventoryNetworkPort")) {
                                            $pfNetworkPort = new PluginFusioninventoryNetworkPort();
                                            $pfNetworkPort->loadNetworkport($data['networkports_id']);
                                            $descr = $pfNetworkPort->getValue("ifdescr");
                                            $a_arguments[$arg] = str_replace("[[NETWORKPORTDESCR]]", $descr, $a_arguments[$arg]);
                                        }
                                    } elseif (strstr($a_arguments[$arg], "[[NETWORKPORTNUM]]")) {
                                        $networkPort = new NetworkPort();
                                        if (isset($data['networkports_id'])
                                            && $data['networkports_id'] > 0) {
                                            $networkPort->getFromDB($data['networkports_id']);
                                        } else if ($my_host_type == 'Computer') {
                                            $networkPort = PluginMonitoringHostaddress::getNetworkport($my_host_item->fields['id'], $my_host_type);
                                        }
                                        if ($networkPort->getID() > 0) {
                                            $logicalnum = $networkPort->fields['logical_number'];
                                            $a_arguments[$arg] = str_replace("[[NETWORKPORTNUM]]", $logicalnum, $a_arguments[$arg]);
                                        }
                                    } elseif (strstr($a_arguments[$arg], "[[NETWORKPORTNAME]]")) {
                                        $networkPort = new NetworkPort();
                                        if (isset($data['networkports_id']) && $data['networkports_id'] > 0) {
                                            $networkPort->getFromDB($data['networkports_id']);
                                        } else if ($my_host_type == 'Computer') {
                                            $networkPort = PluginMonitoringHostaddress::getNetworkport($my_host_item->fields['id'], $my_host_type);
                                        }
                                        if ($networkPort->getID() > 0) {
                                            $portname = $networkPort->fields['name'];
                                            $a_arguments[$arg] = str_replace("[[NETWORKPORTNAME]]", $portname, $a_arguments[$arg]);
                                        }
                                    */
                                } else if (strstr($a_arguments[$arg], '[[IP]]')) {
                                    $ip = PluginMonitoringHostaddress::getIp(
                                        $data['items_id'], $data['itemtype'], '');
                                    $a_arguments[$arg] = str_replace("[[IP]]", $ip, $a_arguments[$arg]);
                                } else if (strstr($a_arguments[$arg], "[")) {
                                    $a_arguments[$arg] = PluginMonitoringService::convertArgument($data['id'], $a_arguments[$arg]);
                                }
                                if (empty($a_arguments)) {
                                    if ($notadddescription != '') {
                                        $notadddescription .= ", ";
                                    }
                                    $notadddescription .= "Argument " . $a_arguments[$arg] . " Not have value";
                                }
                                $args .= '!' . $a_arguments[$arg];
                                if ($a_arguments[$arg] == '' and $cmp_fields['alias_command'] != '') {
                                    $args .= $cmp_fields['alias_command'];
                                }
                            }
                        }
                        if (!empty($pmCommand->fields['command_name'])) {
                            $this->set_value(PluginMonitoringCommand::$command_prefix . $pmCommand->fields['command_name'] . $args, 'check_command', $my_template);
                        }
                    }

                    // Check strategy
                    if (!empty($cmp_fields['active_checks_enabled'])) {
                        $this->set_value($cmp_fields['active_checks_enabled'], 'active_checks_enabled', $my_template);
                    }
                    if (!empty($cmp_fields['passive_checks_enabled'])) {
                        $this->set_value($cmp_fields['passive_checks_enabled'], 'passive_checks_enabled', $my_template);
                    }
                    // Host check strategy (may not be defined!)
                    if ($cmp_fields['plugin_monitoring_checks_id'] != '-1') {
                        if ($pmCheck->getFromDB($cmp_fields['plugin_monitoring_checks_id'])) {
                            $this->set_value($pmCheck->fields['check_interval'], 'check_interval', $my_template);
                            $this->set_value($pmCheck->fields['retry_interval'], 'retry_interval', $my_template);
                            $this->set_value($pmCheck->fields['max_check_attempts'], 'max_check_attempts', $my_template);
                        }
                    }

                    // Manage freshness
                    $freshness_count = $_SESSION['plugin_monitoring']['entities'][$data['entityId']]['freshness_count'];
                    if ($freshness_count == 0) {
                        $this->set_value('0', 'check_freshness', $my_template);
                        $this->set_value('0', 'freshness_threshold', $my_template);
                    } else {
                        $freshness_type = $_SESSION['plugin_monitoring']['entities'][$data['entityId']]['freshness_type'];

                        $multiple = 1;
                        if ($freshness_type == 'seconds') {
                            $multiple = 1;
                        } else if ($freshness_type == 'minutes') {
                            $multiple = 60;
                        } else if ($freshness_type == 'hours') {
                            $multiple = 3600;
                        } else if ($freshness_type == 'days') {
                            $multiple = 86400;
                        }
                        $this->set_value('1', 'check_freshness', $my_template);
                        $this->set_value($freshness_count * $multiple, 'freshness_threshold', $my_template);
                    }

                    // Manage event handler
                    $this->set_value('0', 'event_handler_enabled', $my_template);
                    if ($cmp_fields['plugin_monitoring_eventhandlers_id'] > 0) {
                        if ($pmEventhandler->getFromDB($cmp_fields['plugin_monitoring_eventhandlers_id'])) {
                            $this->set_value(PluginMonitoringCommand::$command_prefix . $pmEventhandler->fields['command_name'], 'event_handler', $my_template);
                        }
                    }

                    // Business impact
                    if (!empty($cmp_fields['business_impact'])) {
                        $this->set_value($cmp_fields['business_impact'], 'business_impact', $my_template);
                    }
                }

                // Check period
                // Get the entity jet lag ...
                $tz_suffix = '';
                if (isset($_SESSION['plugin_monitoring']['entities'][$data['entityId']])) {
                    $tz_suffix = '_' . $_SESSION['plugin_monitoring']['entities'][$data['entityId']]['jet_lag'];
                    if ($tz_suffix == '_0') {
                        $tz_suffix = '';
                    }
                }
                PluginMonitoringToolbox::log("adding host " . $my_host['host_name'] . ", jet lag: " . $tz_suffix);

//                $tz_suffix = '_' . $pmHostconfig->getValueAncestor('jetlag', $data['entityId']);
//                if ($tz_suffix == '_0') {
//                    $tz_suffix = '';
//                }
                // Get the calendar defined for the host entity ...
                $cid = -1;
                if (isset($_SESSION['plugin_monitoring']['entities'][$data['entityId']])) {
                    $cid = $_SESSION['plugin_monitoring']['entities'][$data['entityId']]['calendars_id'];
                }
                PluginMonitoringToolbox::log("adding host " . $my_host['host_name'] . ", calendar: " . $cid);

//                // Use the calendar defined for the host entity ...
//                $cid = Entity::getUsedConfig('calendars_id', $data['entityId'], '', 0);
//                PluginMonitoringToolbox::log("adding host " . $my_host['host_name'] . ", calendar: " . $cid);
//
                if ($calendar->getFromDB($cid) && $this->_addTimeperiod($data['entityId'], $cid)) {
                    $this->set_value(self::monitoringFilter($calendar->fields['name'] . $tz_suffix), 'check_period', $my_host);
                } else {
                    $this->set_value($default_host['check_period'], 'check_period', $my_host);
                }

                // Realm
                $entity_id = $my_host_item->fields['entities_id'];
                $realm_id = $pmHostconfig->getValueAncestor('plugin_monitoring_realms_id', $entity_id, $my_host_type, $my_host_item->getID());
                if ($pmRealm->getFromDB($realm_id)) {
                    $this->set_value($pmRealm->getName(), 'realm', $my_host);
                    // Set the realm definition order from the current entity
                    $pmRealm->fields['definition_order'] = $_SESSION['plugin_monitoring']['entities'][$entity_id]['definition_order'];
                    // Store realm for future use
                    $this->_addRealm($pmRealm);
                }

                // Additional information in host note
                /*
                The notes_url or actions_url fields are containing a simple url or a string in which
                individual url are separated with a | character.
                
                Each url must contain an URI string and may also contain an icon and a title:
                
                action_url URL1,ICON1,ALT1|URL2,ICON2,ALT2|URL3,ICON3,ALT3
                
                As documented in Shinken:
                 * URLx are the url you want to use
                 * ICONx are the images you want to display the link as. It can be either a local
                file, relative to the folder webui/plugins/eltdetail/htdocs/ or an url.
                 * ALTx are the alternative texts you want to display when the ICONx file is missing,
                or not set.
                
                The UI do not use any icon file but the font-awesome icons font. As such, ICONx information
                is the name of an icon in the font awesome icons list.
                
                The ALTx information is the text label used for the hyperlink or button on the page.
                 */
                $notes = [];
                if (isset(self::$default['glpi']['location'])
                    and isset($data['locationName'])
                    and isset($data['locationComment'])) {
                    $comment = str_replace("\r\n", "<br>", $data['locationComment']);
                    $comment = preg_replace(' / [[:cntrl:]]/', '§', $comment);
                    $notes[] = "<strong>{$data['locationName']}</strong><br>{$comment}";
                }
                // Computer comment in notes ...
                if (isset($data['comment']) and !empty($data['comment'])) {
                    $comment = str_replace("\r\n", "<br>", $data['comment']);
                    $comment = preg_replace(' / [[:cntrl:]]/', '§', $comment);
                    $notes[] = "{$comment}";
                }
                if (count($notes) > 0) {
                    $this->set_value(implode("<br>", $notes), 'notes', $my_host);
                }

                if ($new_template) {
                    // Extra parameters
                    $extra_params = [
                        'process_perf_data', 'stalking_options', 'failure_prediction_enabled', 'notes_url', 'action_url',
                        'flap_detection_enabled', 'flap_detection_options', 'low_flap_threshold', 'high_flap_threshold'
                    ];
                    foreach ($extra_params as $parm) {
                        if (isset($default_host[$parm]) and !empty($default_host[$parm])) {
                            $this->set_value($default_host[$parm], $parm, $my_template);
                        }
                    }
                }

                if ($new_template) {
                    // For contacts, check if a component catalog contains the host associated component ...
                    $this->set_value('', 'contacts', $my_template);

                    PluginMonitoringToolbox::logIfDebug("generateHostsCfg - CC, host template: {$my_template['name']} in {$pmCC->getName()}");

                    // Hosts notification
                    if ((!isset ($pmCC->fields['hostnotificationtemplates_id']))
                        or (!$pmHNTemplate->getFromDB($pmCC->fields['hostnotificationtemplates_id']))) {
                        // No notifications defined for host, use defaults ...
                        PluginMonitoringToolbox::logIfDebug("generateHostsCfg - CC, host: {$my_host['host_name']}, no defined notifications.");
                        $extra_params = [
                            'notifications_enabled', 'notification_period', 'notification_options', 'notification_interval', 'first_notification_delay'
                        ];
                        foreach ($extra_params as $parm) {
                            if (isset($default_host[$parm])) {
                                $this->set_value($default_host[$parm], $parm, $my_template);
                            }
                        }
                    } else {
                        $a_HN = $pmHNTemplate->fields;
                        PluginMonitoringToolbox::logIfDebug("generateHostsCfg - CC, host template: {$my_template['name']}, notification template: {$a_HN['name']}.");

                        if ($a_HN['hn_enabled'] == 0 or !isset($a_HN['hn_period'])) {
                            PluginMonitoringToolbox::logIfDebug("generateHostsCfg - CC, host: {$my_template['name']}, no notifications.");
                            // No notifications for host
                            $this->set_value('0', 'notifications_enabled', $my_template);
                        } else {
                            PluginMonitoringToolbox::logIfDebug("generateHostsCfg - CC, host: {$my_template['name']}, notifications enabled.");

                            // Notifications enabled for host
                            $this->set_value('1', 'notifications_enabled', $my_template);

                            // Notification period
                            if ($calendar->getFromDB($a_HN['hn_period']) && $this->_addTimeperiod($my_host_item->fields['entities_id'], $a_HN['hn_period'])) {
                                $this->set_value(self::monitoringFilter($calendar->fields['name'] . $tz_suffix), 'notification_period', $my_template);
                            } else {
                                if (!empty($default_host['notification_period'])) {
                                    $this->set_value($default_host['notification_period'], 'notification_period', $my_template);
                                }
                            }

                            // Notification options
                            if ($a_HN['hn_options_d'] == 1) {
                                $this->set_value("d", 'notification_options', $my_template);
                            }
                            if ($a_HN['hn_options_u'] == 1) {
                                $this->set_value("u", 'notification_options', $my_template);
                            }
                            if ($a_HN['hn_options_r'] == 1) {
                                $this->set_value("r", 'notification_options', $my_template);
                            }
                            if ($a_HN['hn_options_f'] == 1) {
                                $this->set_value("f", 'notification_options', $my_template);
                            }
                            if ($a_HN['hn_options_s'] == 1) {
                                $this->set_value("s", 'notification_options', $my_template);
                            }
                            if ($a_HN['hn_options_n'] == 1) {
                                $this->set_value("n", 'notification_options', $my_template);
                            }
                            if (count($my_template['notification_options']) == 0) {
                                $this->set_value("n", 'notification_options', $my_template);
                            }

                            // Notification interval
                            if (isset ($pmCC->fields['notification_interval'])) {
                                $this->set_value($pmCC->fields['notification_interval'], 'notification_interval', $my_template);
                            } else {
                                if (!empty($default_host['notification_interval'])) {
                                    $this->set_value($default_host['notification_interval'], 'notification_interval', $my_template);
                                }
                            }
                        }
                    }

                    // All the contacts from the CC
                    $my_host['contacts'] = [];
                    $a_list_contact = $pmContact_Item->find("`itemtype`='PluginMonitoringComponentscatalog' AND `items_id`='" . $pmCC->getID() . "'");
                    foreach ($a_list_contact as $data_contact) {
                        if ($data_contact['users_id'] > 0) {
                            $user->getFromDB($data_contact['users_id']);
                            $this->set_value($user->fields['name'], 'contacts', $my_template);
                        } else if ($data_contact['groups_id'] > 0) {
                            // todo: Get contacts from the contact group
                        }
                    }
                }

                if ($new_template) {
                    PluginMonitoringToolbox::logIfDebug("got template: " . print_r($my_template, true));
                    $a_templates[] = $this->properties_list_to_string($my_template);
                }
                PluginMonitoringToolbox::logIfDebug("got host: " . print_r($my_host, true));
                $a_hosts_found[$my_host_item->getName()] = true;
                $a_hosts[] = $this->properties_list_to_string($my_host);
            }
        }

        PluginMonitoringToolbox::log("Found " . count($a_templates) . " hosts templates");
        PluginMonitoringToolbox::log("Found " . count($a_hosts) . " hosts");
        PluginMonitoringToolbox::logIfDebug("End generateHostsCfg");

        if ($PM_CONFIG['build_files']) {
            $config = "";
            if (count($a_templates)) {
                $config .= "# Generated by the monitoring plugin for GLPI\n# on " . date("Y-m-d H:i:s") . "\n";
                $config .= "# ---\n";
                $config .= "# Hosts templates\n";
                $config .= "# ---\n";

                foreach ($a_templates as $data) {
                    $config .= $this->writeFile("host", $data);
                }
            }
            $config .= "# Generated by the monitoring plugin for GLPI\n# on " . date("Y-m-d H:i:s") . "\n";
            $config .= "# ---\n";
            $config .= "# Hosts\n";
            $config .= "# ---\n";

            foreach ($a_hosts as $data) {
                $config .= $this->writeFile("host", $data);
            }
            $filename = PLUGIN_MONITORING_CFG_DIR . '/' . $tag . '-hosts.cfg';
            file_put_contents($filename, $config);
            PluginMonitoringToolbox::logIfDebug("Written file: " . $filename);

            if ($file) return $config;
        }
        return array_merge($a_templates, $a_hosts);
    }


    function generateServicesCfg($tag = '', $file = false)
    {
        global $DB, $PM_CONFIG, $TIMER_DEBUG;

        PluginMonitoringToolbox::logIfDebug("Starting generateServicesCfg services ($tag) ...");
        $pMonitoringCommand = new PluginMonitoringCommand();
        $pmEventhandler = new PluginMonitoringEventhandler();
        $pMonitoringCheck = new PluginMonitoringCheck();
        $pmComponent = new PluginMonitoringComponent();
        $pmContact_Item = new PluginMonitoringContact_Item();
        $networkPort = new NetworkPort();
        $pmService = new PluginMonitoringService();
        $pmComponentscatalog_Host = new PluginMonitoringComponentscatalog_Host();
        $calendar = new Calendar();
        $pmConfig = new PluginMonitoringConfig();

        $a_services = [];

        // Get computer type contener / VM
//        $conteners = $computerType->find("`name`='BSDJail'");

        // Get entities concerned by the provided tag
        $where = '';
        if (!empty($_SESSION['plugin_monitoring']['allowed_entities'])) {
            $where = getEntitiesRestrictRequest("WHERE",
                "glpi_plugin_monitoring_services", '',
                $_SESSION['plugin_monitoring']['allowed_entities']);
        }

        // TODO: only contacts in allowed entities ...
        // Prepare individual contacts
        $a_contacts_entities = [];
        $a_list_contact = $pmContact_Item->find("`itemtype`='PluginMonitoringComponentscatalog' AND `users_id`>0");
        foreach ($a_list_contact as $data) {
            $contactentities = getSonsOf('glpi_entities', $data['entities_id']);
            if (isset($a_contacts_entities[$data['items_id']][$data['users_id']])) {
                $contactentities = array_merge($contactentities, $a_contacts_entities[$data['items_id']][$data['users_id']]);
            }
            $a_contacts_entities[$data['items_id']][$data['users_id']] = $contactentities;
        }
        // Prepare groups contacts
        $group = new Group();
        $a_list_contact = $pmContact_Item->find("`itemtype`='PluginMonitoringComponentscatalog' AND `groups_id`>0");
        foreach ($a_list_contact as $data) {
            $group->getFromDB($data['groups_id']);
            if ($group->fields['is_recursive'] == 1) {
                $contactentities = getSonsOf('glpi_entities', $group->fields['entities_id']);
            } else {
                $contactentities = [$group->fields['entities_id'] => $group->fields['entities_id']];
            }
            $queryg = "SELECT * FROM `glpi_groups_users` WHERE `groups_id`='" . $data['groups_id'] . "'";
            if ($resultg = $DB->query($queryg)) {
                while ($datag = $DB->fetch_array($resultg)) {
                    if (isset($a_contacts_entities[$data['items_id']][$datag['users_id']])) {
                        $contactentities = array_merge($contactentities, $a_contacts_entities[$data['items_id']][$datag['users_id']]);
                    }
                    $a_contacts_entities[$data['items_id']][$datag['users_id']] = $contactentities;
                }
            }
        }

        // Get all necessary objects!
        $a_components = $pmComponent->find();
        $componentscatalog_hosts = $pmComponentscatalog_Host->find();
//        $timeperiodsuffixes = [];
//        foreach ($_SESSION['plugin_monitoring']['allowed_entities'] as $entities_id) {
//            $timeperiodsuffixes[$entities_id] = '_' . $pmHostconfig->getValueAncestor('jetlag', $entities_id);
//        }
        $a_commands = $pMonitoringCommand->find();
        $a_checks = $pMonitoringCheck->find();
        $a_calendars = $calendar->find();

        // --------------------------------------------------
        // "Normal" services ....
        $query = "SELECT * FROM `glpi_plugin_monitoring_services` $where";
        PluginMonitoringToolbox::log("generateServicesCfg, query: " . $query);
        if ($result = $DB->query($query)) {
            PluginMonitoringToolbox::log("generateServicesCfg, huge query execution, got " . $DB->numrows($result) . " rows, duration: " . $TIMER_DEBUG->getTime());
            while ($data = $DB->fetch_array($result)) {
                PluginMonitoringToolbox::log(" - service: {$data['id']} - {$data['service_description']}");

                // Service component
                if (!isset($a_components[$data['plugin_monitoring_components_id']]) or empty($a_component)) {
                    PluginMonitoringToolbox::logIfDebug("[ERROR] service: {$data['id']} - no associated component !");
                    continue;
                }
                $a_component = $a_components[$data['plugin_monitoring_components_id']];

                if ($a_component['build_service'] != '1') {
                    PluginMonitoringToolbox::logIfDebug("service: {$data['id']} - no data built for this service.");
                    continue;
                }

                // Service component catalog host
                if (!isset($componentscatalog_hosts[$data['plugin_monitoring_componentscatalogs_hosts_id']])) {
                    PluginMonitoringToolbox::logIfDebug("[ERROR] service: {$data['id']} - no associated CC host !");
                    continue;
                }
                $cc_host = $componentscatalog_hosts[$data['plugin_monitoring_componentscatalogs_hosts_id']];

                /* @var CommonDBTM $my_host_item */
                $my_host_type = $cc_host['itemtype'];
                $my_host_item = new $my_host_type;
                if (!$my_host_item->getFromDB($cc_host['items_id'])) {
                    PluginMonitoringToolbox::log('[ERROR] Host item not found: ' . print_r($data, true));
                    continue;
                }

                $my_service = [];

                // Monitoring host name
                $my_host_name = self::monitoringFilter($my_host_item->getName());
                $this->set_value($my_host_name, 'host_name', $my_service);
                if ($PM_CONFIG['append_id_hostname'] == 1) {
                    $my_host_name = self::monitoringFilter($my_host_item->getName() . '-' . $my_host_item->getID());
                    $this->set_value($my_host_name, 'host_name', $my_service);
                }

                $notadd = 0;
                $notadddescription = '';

                $computerTypes_id = 0;

                PluginMonitoringToolbox::logIfDebug(" - fetching service: {$data['id']} ({$a_component['name']} - {$a_component['id']}) - {$cc_host['itemtype']} - {$cc_host['items_id']} -> {$my_service['host_name']}");
                $entities_id = $my_host_item->fields['entities_id'];
                if ($my_host_type == 'Computer') {
                    $computerTypes_id = $my_host_item->fields['computertypes_id'];
                }

                if (isset($_SESSION['plugin_monitoring']['servicetemplates'][$a_component['id']])) {
                    $this->set_value($_SESSION['plugin_monitoring']['servicetemplates'][$a_component['id']], 'use', $my_service);
                } else {
                    // TODO - Service has no defined template!
                }
                $this->set_value($cc_host['items_id'], '_HOSTITEMSID', $my_service);
                $this->set_value($cc_host['itemtype'], '_HOSTITEMTYPE', $my_service);

                // service_description and display name
                $this->set_value($a_component['name'], 'display_name', $my_service);
                if (!empty($a_component['description'])) {
                    $this->set_value($a_component['description'], 'service_description', $my_service);
                } else {
                    $this->set_value(self::monitoringFilter($a_component['name']), 'service_description', $my_service);
                }

                // TODO: NE
                // In case we have multiple networkt ports, we must have different description, else it will be dropped by shinken
                if ($data['networkports_id'] > 0) {
                    $networkPort->getFromDB($data['networkports_id']);
                    $this->set_value($my_service['service_description'] . '-' . self::monitoringFilter($networkPort->fields['name'])
                        , 'service_description', $hn);
                    $this->set_value($a_component['name'] . '-' . self::monitoringFilter($networkPort->fields['name']), 'display_name', $my_service);
                }
                PluginMonitoringToolbox::logIfDebug(" - adding service " . $my_service['service_description'] . " on " . $my_service['host_name']);

                if (isset(self::$default['glpi']['entityId'])) {
                    $this->set_value($my_host_item->fields['entities_id'], self::$default['glpi']['entityId'], $my_service);
                }
                if (isset(self::$default['glpi']['itemType'])) {
                    $this->set_value('Service', self::$default['glpi']['itemType'], $my_service);
                }
                if (isset(self::$default['glpi']['itemId'])) {
                    $this->set_value($data['id'], self::$default['glpi']['itemId'], $my_service);
                }

                // Check command
                $a_command = $a_commands[$a_component['plugin_monitoring_commands_id']];
                // Manage arguments
                $array = [];
                preg_match_all("/\\$(ARG\d+)\\$/", $a_command['command_line'], $array);
                sort($array[0]);
                $a_arguments = importArrayFromDB($a_component['arguments']);
                $a_argumentscustom = importArrayFromDB($data['arguments']);
                foreach ($a_argumentscustom as $key => $value) {
                    $a_arguments[$key] = $value;
                }
                foreach ($a_arguments as $key => $value) {
                    $a_arguments[$key] = str_replace('!', '\!', html_entity_decode($value));
                }
                $args = '';
                foreach ($array[0] as $arg) {
                    if (in_array($arg, ['$PLUGINSDIR$', '$NAGIOSPLUGINSDIR$', '$HOSTADDRESS$', '$MYSQLUSER$', '$MYSQLPASSWORD$'])) {
                        continue;
                    }

                    $arg = str_replace('$', '', $arg);
                    if (!isset($a_arguments[$arg])) {
                        $args .= '!';
                    } else {
                        if (strstr($a_arguments[$arg], "[[HOSTNAME]]")) {
                            $a_arguments[$arg] = str_replace("[[HOSTNAME]]", $my_host_name, $a_arguments[$arg]);

                            /* mohierf: disable this feature
                    } elseif (strstr($a_arguments[$arg], "[[NETWORKPORTDESCR]]")) {
                        if (class_exists("PluginFusioninventoryNetworkPort")) {
                            $pfNetworkPort = new PluginFusioninventoryNetworkPort();
                            $pfNetworkPort->loadNetworkport($data['networkports_id']);
                            $descr = $pfNetworkPort->getValue("ifdescr");
                            $a_arguments[$arg] = str_replace("[[NETWORKPORTDESCR]]", $descr, $a_arguments[$arg]);
                        }
                    } elseif (strstr($a_arguments[$arg], "[[NETWORKPORTNUM]]")) {
                        $networkPort = new NetworkPort();
                        if (isset($data['networkports_id'])
                            && $data['networkports_id'] > 0) {
                            $networkPort->getFromDB($data['networkports_id']);
                        } else if ($my_service['_HOSTITEMTYPE'] == 'Computer') {
                            $networkPort = PluginMonitoringHostaddress::getNetworkport(
                                $my_service['_HOSTITEMSID'],
                                $my_service['_HOSTITEMTYPE']);
                        }
                        if ($networkPort->getID() > 0) {
                            $logicalnum = $networkPort->fields['logical_number'];
                            $a_arguments[$arg] = str_replace("[[NETWORKPORTNUM]]", $logicalnum, $a_arguments[$arg]);
                        }
                    } elseif (strstr($a_arguments[$arg], "[[NETWORKPORTNAME]]")) {
                        $networkPort = new NetworkPort();
                        if (isset($data['networkports_id']) and $data['networkports_id'] > 0) {
                            $networkPort = new NetworkPort();
                            $networkPort->getFromDB($data['networkports_id']);
                        } else if ($my_service['_HOSTITEMTYPE'] == 'Computer') {
                            $networkPort = PluginMonitoringHostaddress::getNetworkport(
                                $my_service['_HOSTITEMSID'],
                                $my_service['_HOSTITEMTYPE']);
                        }
                        if ($networkPort->getID() > 0) {
                            $portname = $networkPort->fields['name'];
                            $a_arguments[$arg] = str_replace("[[NETWORKPORTNAME]]", $portname, $a_arguments[$arg]);
                        }
                        */

                        } else if (strstr($a_arguments[$arg], '[[IP]]')) {
                            $ip = PluginMonitoringHostaddress::getIp($cc_host['items_id'], $cc_host['itemtype'], '');
                            $a_arguments[$arg] = str_replace("[[IP]]", $ip, $a_arguments[$arg]);
                        } else if (strstr($a_arguments[$arg], "[")) {
                            $a_arguments[$arg] = PluginMonitoringService::convertArgument($data['id'], $a_arguments[$arg]);
                        }
                        if (empty($a_arguments)) {
                            $notadd = 1;
                            if ($notadddescription != '') {
                                $notadddescription .= ", ";
                            }
                            $notadddescription .= "Argument " . $a_arguments[$arg] . " do not have value";
                        }
                        $args .= '!' . $a_arguments[$arg];
                        if ($a_arguments[$arg] == '' and $a_component['alias_command'] != '') {
                            $args .= $a_component['alias_command'];
                        }
                    }
                }
                // End manage arguments
                if ($a_component['remotesystem'] == 'nrpe') {
                    if (!empty($a_component['alias_command'])) {
                        $alias_command = $a_component['alias_command'];
                        if (strstr($alias_command, '[[IP]]')) {
                            $ip = PluginMonitoringHostaddress::getIp($cc_host['items_id'], $cc_host['itemtype'], '');
                            $alias_command = str_replace("[[IP]]", $ip, $alias_command);
                        }
                        if ($my_host_type == 'Computer'
                            and $pmConfig->fields['nrpe_prefix_container'] == 1
                            and isset($conteners[$computerTypes_id])) {
                            // get Host of contener/VM
                            $where = "LOWER(`uuid`)" . ComputerVirtualMachine::getUUIDRestrictRequest($my_host_item->fields['uuid']);
                            $hosts = getAllDatasFromTable('glpi_computervirtualmachines', $where);
                            if (!empty($hosts)) {
                                $alias_command = $my_host_name . "_" . $alias_command;
                            }
                        }
                        $this->set_value(PluginMonitoringCommand::$command_prefix . "check_nrpe!" . $alias_command, 'check_command', $my_service);
                    } else {
                        $this->set_value(PluginMonitoringCommand::$command_prefix . "check_nrpe!" . $a_command['command_name'], 'check_command', $my_service);
                    }
                } else {
                    if (!empty($a_command['command_name'])) {
                        $this->set_value(PluginMonitoringCommand::$command_prefix . $a_command['command_name'] . $args, 'check_command', $my_service);
                    }
                }

                // Manage event handler
                $this->set_value('0', 'event_handler_enabled', $my_service);
                if ($a_component['plugin_monitoring_eventhandlers_id'] > 0
                    and $pmEventhandler->getFromDB($a_component['plugin_monitoring_eventhandlers_id'])) {
                    $this->set_value(PluginMonitoringCommand::$command_prefix . $pmEventhandler->fields['command_name'], 'event_handler', $my_service);
                    $this->set_value('1', 'event_handler_enabled', $my_service);
                }

                // * Contacts
                // The service will be configured with the contacts of its related host!

                // If a service template has not been defined :
                if (!isset($_SESSION['plugin_monitoring']['servicetemplates'][$a_component['id']])) {
                    // Get the entity jet lag ...
                    $tz_suffix = '';
                    if (isset($_SESSION['plugin_monitoring']['entities'][$entities_id])) {
                        $tz_suffix = '_' . $_SESSION['plugin_monitoring']['entities'][$entities_id]['jet_lag'];
                        if ($tz_suffix == '_0') {
                            $tz_suffix = '';
                        }
                    }

                    $a_check = $a_checks[$a_component['plugin_monitoring_checks_id']];
                    $this->set_value($a_check['check_interval'], 'check_interval', $my_service);
                    $this->set_value($a_check['retry_interval'], 'retry_interval', $my_service);
                    $this->set_value($a_check['max_check_attempts'], 'max_check_attempts', $my_service);
                    if (isset($a_calendars[$a_component['calendars_id']]) && $this->_addTimeperiod($entities_id, $a_component['calendars_id'])) {
                        $this->set_value(self::monitoringFilter($a_calendars[$a_component['calendars_id']]['name'] . $tz_suffix), 'check_period', $my_service);
                    } else {
                        $this->set_value(self::$default['shinken']['services']['check_period'], 'check_period', $my_service);
                    }
                    $elements = [
                        'notification_interval' => 30,
                        'notification_period' => "24x7",
                        'notification_options' => 'w,u,c,r,f,s',
                        'first_notification_delay' => 0,
                        'process_perf_data' => 1,
//                        'active_checks_enabled' => 1,
//                        'passive_checks_enabled' => 1,
                        'parallelize_check' => 1,
                        'obsess_over_service' => 0,
                        'check_freshness' => 1,
                        'freshness_threshold' => 3600,
                        'notifications_enabled' => 1
                    ];
                    foreach ($elements as $key => $val) {
                        $this->set_value($val, $key, $my_service);
                    }
                }

                if ($notadd == '1') {
                    unset($my_service);
                    $input = [];
                    $input['id'] = $data['id'];
                    $input['output'] = $notadddescription;
                    $input['state'] = "CRITICAL";
                    $input['state_type'] = "HARD";
                    $pmService->update($input);
                } else {
                    $a_services[] = $this->properties_list_to_string($my_service);
                }
            }
        }

        PluginMonitoringToolbox::log("Found " . count($a_services) . " services");
        PluginMonitoringToolbox::logIfDebug("End generateServicesCfg services");

        if ($PM_CONFIG['build_files']) {
            $config = "# Generated by the monitoring plugin for GLPI\n# on " . date("Y-m-d H:i:s") . "\n";
            $config .= "# ---\n";
            $config .= "# Services\n";
            $config .= "# ---\n";

            foreach ($a_services as $data) {
                $config .= $this->writeFile("service", $data);
            }
            $filename = PLUGIN_MONITORING_CFG_DIR . '/' . $tag . '-services.cfg';
            file_put_contents($filename, $config);
            PluginMonitoringToolbox::logIfDebug("Written file: " . $filename);

            if ($file) return $config;
        }

        return $a_services;
    }


    function generateServicesTemplatesCfg($tag = '', $file = false)
    {
        global $DB, $PM_CONFIG;

        if (!isset($_SESSION['plugin_monitoring'])) {
            $_SESSION['plugin_monitoring'] = [];
        }
        if (!isset($_SESSION['plugin_monitoring']['servicetemplates'])) {
            $_SESSION['plugin_monitoring']['servicetemplates'] = [];
        }

        PluginMonitoringToolbox::logIfDebug("Starting generateServicesTemplatesCfg ($tag) ...");
        $pMonitoringCheck = new PluginMonitoringCheck();

        $a_servicetemplates = [];

        // Build a Shinken service template for each declared component ...
        // Fix service template association bug: #191
        $query = "SELECT * FROM `glpi_plugin_monitoring_components` WHERE `build_service`='1' ORDER BY `id`";
        // Select components with some grouping ...
        // $query = "SELECT * FROM `glpi_plugin_monitoring_components`
        // GROUP BY `plugin_monitoring_checks_id`, `active_checks_enabled`,
        // `passive_checks_enabled`, `freshness_count`, `freshness_type`, `calendars_id`, `business_impact`
        // ORDER BY `id`";
        if ($result = $DB->query($query)) {
            if ($DB->numrows($result) != 0) {
                while ($data = $DB->fetch_array($result)) {

                    $my_service_tpl = [];
                    PluginMonitoringToolbox::logIfDebug(" - add template " . 'template' . $data['id'] . ' - service');
                    // Fix service template association bug: #191
                    // $my_service_tpl = $this->add_value_type(
                    // self::shinkenFilter('stag - '.$data['id']),
                    // 'name', $my_service_tpl);
                    $this->set_value(self::monitoringFilter($data['name']), 'name', $my_service_tpl);
                    // Alias is not used by Shinken !
                    $this->set_value($data['description'] . ' / ' . $data['name'], 'alias', $my_service_tpl);
                    if (isset ($data['business_impact'])) {
                        $this->set_value($data['business_impact'], 'business_impact', $my_service_tpl);
                    } else {
                        if (!empty(self::$default['shinken']['services']['business_impact'])) {
                            $this->set_value(self::$default['shinken']['services']['business_impact'], 'business_impact', $my_service_tpl);
                        } else {
                            $this->set_value('0', 'business_impact', $my_service_tpl);
                        }
                    }
                    $pMonitoringCheck->getFromDB($data['plugin_monitoring_checks_id']);
                    $this->set_value($pMonitoringCheck->fields['check_interval'], 'check_interval', $my_service_tpl);
                    $this->set_value($pMonitoringCheck->fields['retry_interval'], 'retry_interval', $my_service_tpl);
                    $this->set_value($pMonitoringCheck->fields['max_check_attempts'], 'max_check_attempts', $my_service_tpl);

                    // check_period, defined in each service ...

                    // notification parameters, defined in each service ...
                    // $my_service_tpl['notification_interval'] = '30';
                    // $my_service_tpl['notification_period'] = "24x7";
                    // $my_service_tpl['notification_options'] = 'w,u,c,r,f,s';
                    // $my_service_tpl['process_perf_data'] = '1';
                    $this->set_value($data['active_checks_enabled'], 'active_checks_enabled', $my_service_tpl);
                    $this->set_value($data['passive_checks_enabled'], 'passive_checks_enabled', $my_service_tpl);
                    $this->set_value('1', 'parallelize_check', $my_service_tpl);
                    $this->set_value('0', 'obsess_over_service', $my_service_tpl);

                    // Manage freshness
                    // $my_service_tpl['check_freshness'] = '1';
                    // $my_service_tpl['freshness_threshold'] = '3600';
                    if ($data['freshness_count'] == 0) {
                        $this->set_value('0', 'check_freshness', $my_service_tpl);
                        $this->set_value('3600', 'freshness_threshold', $my_service_tpl);
                    } else {
                        $multiple = 1;
                        if ($data['freshness_type'] == 'seconds') {
                            $multiple = 1;
                        } else if ($data['freshness_type'] == 'minutes') {
                            $multiple = 60;
                        } else if ($data['freshness_type'] == 'hours') {
                            $multiple = 3600;
                        } else if ($data['freshness_type'] == 'days') {
                            $multiple = 86400;
                        }
                        $this->set_value('1', 'check_freshness', $my_service_tpl);
                        $this->set_value(($data['freshness_count'] * $multiple), 'freshness_threshold', $my_service_tpl);
                    }
                    $this->set_value('1', 'notifications_enabled', $my_service_tpl);
                    $this->set_value('0', 'event_handler_enabled', $my_service_tpl);
                    $this->set_value(self::$default['shinken']['services']['stalking_options'], 'stalking_options', $my_service_tpl);

                    if (isset(self::$default['shinken']['services']['flap_detection_enabled'])) {
                        $this->set_value(self::$default['shinken']['services']['flap_detection_enabled'], 'flap_detection_enabled', $my_service_tpl);
                        $this->set_value(self::$default['shinken']['services']['flap_detection_options'], 'flap_detection_options', $my_service_tpl);
                        $this->set_value(self::$default['shinken']['services']['low_flap_threshold'], 'low_flap_threshold', $my_service_tpl);
                        $this->set_value(self::$default['shinken']['services']['high_flap_threshold'], 'high_flap_threshold', $my_service_tpl);
                    }
                    $this->set_value(self::$default['shinken']['services']['failure_prediction_enabled'], 'failure_prediction_enabled', $my_service_tpl);

                    $this->set_value('0', 'is_volatile', $my_service_tpl);

                    // This is a template!
                    $this->set_value('0', 'register', $my_service_tpl);

                    // Manage user interface ...
//                    $this->set_value('service', 'icon_set', $my_service_tpl);

                    // Fix service template association bug: #191
                    // And simplify code !
                    // $queryc = "SELECT * FROM `glpi_plugin_monitoring_components`
                    // WHERE `plugin_monitoring_checks_id`='".$data['plugin_monitoring_checks_id']."'
                    // AND `active_checks_enabled`='".$data['active_checks_enabled']."'
                    // AND `passive_checks_enabled`='".$data['passive_checks_enabled']."'
                    // AND `calendars_id`='".$data['calendars_id']."'";
                    // $resultc = $DB->query($queryc);
                    // while ($datac=$DB->fetch_array($resultc)) {
                    // $a_templatesdef[$datac['id']] = $my_service_tpl['name'];
                    // }
                    if (!isset($_SESSION['plugin_monitoring']['servicetemplates'][$data['id']])) {
                        $_SESSION['plugin_monitoring']['servicetemplates'][$data['id']] = $my_service_tpl['name'];
                    }

                    $a_servicetemplates[] = $this->properties_list_to_string($my_service_tpl);
                }
            }
        }

        PluginMonitoringToolbox::log("Found " . count($a_servicetemplates) . " services templates");
        PluginMonitoringToolbox::logIfDebug("End generateServicesTemplatesCfg");

        if ($PM_CONFIG['build_files']) {
            $config = "# Generated by the monitoring plugin for GLPI\n# on " . date("Y-m-d H:i:s") . "\n";
            $config .= "# ---\n";
            $config .= "# Services templates\n";
            $config .= "# ---\n";

            foreach ($a_servicetemplates as $data) {
                $config .= $this->writeFile("service", $data);
            }
            $filename = PLUGIN_MONITORING_CFG_DIR . '/' . $tag . '-services_templates.cfg';
            file_put_contents($filename, $config);
            PluginMonitoringToolbox::logIfDebug("Written file: " . $filename);

            if ($file) return $config;
        }

        return $a_servicetemplates;
    }


    function generateHostgroupsCfg($tag = '', $file = false)
    {
        global $DB, $TIMER_DEBUG, $PM_CONFIG;

        PluginMonitoringToolbox::logIfDebug("Starting generateHostgroupsCfg ($tag) ...");
        $entity = new Entity();

        $a_hostgroups = [];

        // Get entities concerned by the provided tag
        $where = '';
        if (!empty($_SESSION['plugin_monitoring']['allowed_entities'])) {
            $where = getEntitiesRestrictRequest("WHERE",
                "glpi_entities", '',
                $_SESSION['plugin_monitoring']['allowed_entities']);
        }

        $query = "SELECT
            `id` AS entityId, 
            `name` AS entityName, 
            `level` AS entityLevel, 
            `comment`, `address`, `postcode`, `town`, 
            `state`, `country`, `website`, `fax`, `email`, `phonenumber`
         FROM `glpi_entities` $where";

        if ($result = $DB->query($query)) {
            PluginMonitoringToolbox::log("generateHostgroupsCfg, huge query execution, got " . $DB->numrows($result) . " rows, duration: " . $TIMER_DEBUG->getTime());
            while ($data = $DB->fetch_array($result)) {
                /*
                 * Nagios configuration file :
                   define hostgroup{
                      hostgroup_name	hostgroup_name
                      alias	alias
                      members	hosts
                      hostgroup_members	hostgroups
                      notes	note_string
                      notes_url	url
                      action_url	url
                   }
                 */
                if ($data['entityLevel'] > self::HOSTGROUP_LEVEL) {
                    continue;
                }

                $my_group = [];
                // Hostgroup name
                $hostgroup_name = self::monitoringFilter($data['entityName']);
                $hostgroup_name = preg_replace("/\s/", "_", $hostgroup_name);

                PluginMonitoringToolbox::log(" - add group $hostgroup_name ...");

                $this->set_value($hostgroup_name, 'hostgroup_name', $my_group);
                $this->set_value($data['entityName'], 'alias', $my_group);

                // Host group members
                $a_sons_list = getSonsOf("glpi_entities", $data['entityId']);
                if (count($a_sons_list) > 1) {
                    $first_member = true;
                    foreach ($a_sons_list as $son_entity) {
                        if ($son_entity == $data['entityId']) continue;

                        $entity->getFromDB($son_entity);
                        // Only immediate sub level are considered as hostgroup members
                        if ($data['entityLevel'] + 1 != $entity->fields['level']) continue;

                        $hostgroup_name = self::monitoringFilter($entity->getName());
                        $hostgroup_name = preg_replace("/\s/", "_", $hostgroup_name);

                        $this->set_value($hostgroup_name, 'hostgroup_members', $my_group);
                        if ($first_member) $first_member = false;
                    }
                }

                // Comments in notes ...
                // PluginMonitoringToolbox::logIfDebug(" - location:{$data['locationName']} - {$data['locationComment']}");
                $notes = [];
                if (!empty($data['comment'])) {
                    $notes[] = str_replace("\r\n", "<br>", $data['comment']);
                }
                if (!empty($data['address'])) {
                    $notes[] = str_replace("\r\n", "<br>", $data['address']);
                    if (!empty($data['postcode']) && !empty($data['town'])) {
                        $notes[] = $data['postcode'] . " " . $data['town'];
                    } else if (!empty($data['postcode'])) {
                        $notes[] = $data['postcode'];
                    } else if (!empty($data['town'])) {
                        $notes[] = $data['town'];
                    }

                    if (!empty($data['state']) && !empty($data['country'])) {
                        $notes[] = $data['state'] . " - " . $data['country'];
                    } else if (!empty($data['state'])) {
                        $notes[] = $data['state'];
                    } else if (!empty($data['country'])) {
                        $notes[] = $data['country'];
                    }

                    $notes[] = "";
                    if (!empty($data['phonenumber'])) {
                        $notes[] = "<i class='fa fa-phone'></i>&nbsp;: " . $data['phonenumber'];
                    }
                    if (!empty($data['fax'])) {
                        $notes[] = "<i class='fa fa-fax'></i>&nbsp;: " . $data['fax'];
                    }
                    if (!empty($data['email'])) {
                        $notes[] = "<i class='fa fa-envelope'></i>&nbsp;: " . $data['email'];
                    }
                    if (!empty($data['website'])) {
                        $notes[] = "<i class='fa fa-globe'></i>&nbsp;: " . $data['website'];
                    }
                }
                if (count($notes) > 0) {
                    $this->set_value(implode("<br>", $notes), 'notes', $my_group);
                }

                $my_group = $this->properties_list_to_string($my_group);
                $a_hostgroups[] = $my_group;
            }
        }

        PluginMonitoringToolbox::logIfDebug("End generateHostgroupsCfg");

        if ($PM_CONFIG['build_files']) {
            $config = "# Generated by the monitoring plugin for GLPI\n# on " . date("Y-m-d H:i:s") . "\n";
            $config .= "# ---\n";
            $config .= "# Hosts groups\n";
            $config .= "# ---\n";

            foreach ($a_hostgroups as $data) {
                $config .= $this->writeFile("hostgroup", $data);
            }
            $filename = PLUGIN_MONITORING_CFG_DIR . '/' . $tag . '-hostgroups.cfg';
            file_put_contents($filename, $config);
            PluginMonitoringToolbox::logIfDebug("Written file: " . $filename);

            if ($file) return $config;
        }

        return $a_hostgroups;
    }


    function generateContactsCfg($tag = '', $file = false)
    {
        global $DB, $TIMER_DEBUG, $PM_CONFIG;

        PluginMonitoringToolbox::logIfDebug("Starting generateContactsCfg ($tag) ...");

        $a_users_used = [];
        $a_contacts = [];

        // Get entities concerned by the provided tag
        $where = '';
        if (!empty($_SESSION['plugin_monitoring']['allowed_entities'])) {
            $where = getEntitiesRestrictRequest("WHERE",
                "glpi_plugin_monitoring_contacts_items", '',
                $_SESSION['plugin_monitoring']['allowed_entities'],
                true);
        }

        $query = "SELECT * FROM `glpi_plugin_monitoring_contacts_items` $where";
        PluginMonitoringToolbox::logIfDebug("generateContactsCfg, query: " . $query);
        if ($result = $DB->query($query)) {
            PluginMonitoringToolbox::log("generateContactsCfg, huge query execution, got " . $DB->numrows($result) . " rows, duration: " . $TIMER_DEBUG->getTime());
            while ($data = $DB->fetch_array($result)) {
                if ($data['users_id'] > 0) {
                    if (!isset($a_users_used[$data['users_id']])) {
                        if ($got_contact = $this->_getContactFromUser($data['users_id'], $file)) {
                            $a_contacts[] = $got_contact;
                            $a_users_used[$data['users_id']] = true;
                        }
                    }
                } else if ($data['groups_id'] > 0) {
                    $queryg = "SELECT * FROM `glpi_groups_users` WHERE `groups_id`='" . $data['groups_id'] . "'";
                    if ($resultg = $DB->query($queryg)) {
                        while ($datag = $DB->fetch_array($resultg)) {
                            if (!isset($a_users_used[$datag['users_id']])) {
                                if ($got_contact = $this->_getContactFromUser($datag['users_id'], $file)) {
                                    $a_contacts[] = $got_contact;
                                    $a_users_used[$datag['users_id']] = true;
                                }
                            }
                        }
                    }
                }
            }
        }

        // Add default monitoring user
        $user = new User();
        if ($user->getFromDBByCrit(['name' => PluginMonitoringContact::$default_user])) {
            PluginMonitoringToolbox::logIfDebug("Default user: " . print_r($user, true));
            if (!isset($a_users_used[$user->getID()])) {
                if ($got_contact = $this->_getContactFromUser($user->getID(), $file)) {
                    $a_contacts[] = $got_contact;
                    $a_users_used[$user->getID()] = true;
                }
            }
        }

        PluginMonitoringToolbox::log("Found " . count($a_contacts) . " contacts");
        PluginMonitoringToolbox::logIfDebug("End generateContactsCfg");

        if ($PM_CONFIG['build_files']) {
            $config = "# Generated by the monitoring plugin for GLPI\n# on " . date("Y-m-d H:i:s") . "\n";
            $config .= "# ---\n";
            $config .= "# Contacts\n";
            $config .= "# ---\n";

            foreach ($a_contacts as $data) {
                $config .= $this->writeFile("contact", $data);
            }
            $filename = PLUGIN_MONITORING_CFG_DIR . '/' . $tag . '-contacts.cfg';
            file_put_contents($filename, $config);
            PluginMonitoringToolbox::logIfDebug("Written file: " . $filename);

            if ($file) return $config;
        }

        return $a_contacts;
    }


    function _getContactFromUser($users_id, $file = false)
    {
        PluginMonitoringToolbox::logIfDebug("Get contact from user: " . $users_id);

        $user = new User();
        if (!$user->getFromDB($users_id)) {
            PluginMonitoringToolbox::log("[ERROR] Unknown user: $users_id!");
            return null;
        }

        $pmContactTemplate = new PluginMonitoringContacttemplate();
        $fields = null;
        if (isset($_SESSION['plugin_monitoring']['default_contact_template'])) {
            $fields = $_SESSION['plugin_monitoring']['default_contact_template'];
        }

        // Get the monitoring contact and its contact template
        $pmContact = new PluginMonitoringContact();
        if (!$pmContact->getFromDBByCrit(['users_id' => $users_id])) {
            // Use the default contact template
            PluginMonitoringToolbox::log("[ERROR] {$user->getName()} is not configured for monitoring... using the default contact template");
            $fields = $_SESSION['plugin_monitoring']['default_contact_template'];
        } else {
            if (!$pmContactTemplate->getFromDB($pmContact->getField('plugin_monitoring_contacttemplates_id'))) {
                // Use the default contact template
                PluginMonitoringToolbox::log("[ERROR] using the default contact template");
                $fields = $_SESSION['plugin_monitoring']['default_contact_template'];
//            } else {
//                $fields = $pmContactTemplate->fields;
            }
        }
        if (!$fields) {
            PluginMonitoringToolbox::log("[ERROR] no contact template information found!");
            return false;
        }

        $my_contact = [];

        PluginMonitoringToolbox::logIfDebug("- build contact '" . $user->getName() . "' in entity: " . $user->fields['entities_id']);

        // For comments ...
        if ($file) {
            $this->set_value($user->getName(), 'file_comment', $my_contact);
        }

        // For the framework configuration...
        $this->set_value($user->fields['name'], 'contact_name', $my_contact);
        $this->set_value($user->getName(), 'alias', $my_contact);
        $this->set_value($user->getName(), 'display_name', $my_contact);

        if (isset(self::$default['shinken']['contacts']['note'])) {
            $this->set_value(self::$default['shinken']['contacts']['note'] . $pmContactTemplate->getName(), 'note', $my_contact);
        }

        $this->set_value($fields['hn_enabled'], 'host_notifications_enabled', $my_contact);
        $this->set_value($fields['sn_enabled'], 'service_notifications_enabled', $my_contact);

        // Get the contact entity jet lag ...
        $tz_suffix = '';
        if (isset($_SESSION['plugin_monitoring']['entities'][$user->fields['entities_id']])) {
            $tz_suffix = '_' . $_SESSION['plugin_monitoring']['entities'][$user->fields['entities_id']]['jet_lag'];
            if ($tz_suffix == '_0') {
                $tz_suffix = '';
            }
        }
//        // Contact entity jetlag ...
//        $pmHostconfig = new PluginMonitoringHostconfig();
//        // $user->fields['entities_id']
//        $tz_suffix = '_' . $pmHostconfig->getValueAncestor('jetlag', $user->getEntityID());
//        if ($tz_suffix == '_0') {
//            $tz_suffix = '';
//        }
        PluginMonitoringToolbox::logIfDebug("- time zone: $tz_suffix");

        // Notification periods
        $calendar = new Calendar();
        if ($calendar->getFromDB($fields['sn_period']) and
            $this->_addTimeperiod($user->getField('entities_id'), $fields['sn_period'])) {
            $this->set_value(self::monitoringFilter($calendar->getName() . $tz_suffix),
                'service_notification_period', $my_contact);
        } else {
            $this->set_value(self::$default['shinken']['contacts']['service_notification_period'],
                'service_notification_period', $my_contact);
        }

        if ($calendar->getFromDB($fields['hn_period']) and
            $this->_addTimeperiod($user->getField('entities_id'), $fields['hn_period'])) {
            $this->set_value(self::monitoringFilter($calendar->getName() . $tz_suffix),
                'host_notification_period', $my_contact);
        } else {
            $this->set_value(self::$default['shinken']['contacts']['host_notification_period'],
                'host_notification_period', $my_contact);
        }

        $my_contact['service_notification_options'] = [];
        if ($fields['sn_options_w'] == 1) {
            $this->set_value('w', 'service_notification_options', $my_contact);
        }
        if ($fields['sn_options_u'] == 1) {
            $this->set_value('u', 'service_notification_options', $my_contact);
        }
        if ($fields['sn_options_c'] == 1) {
            $this->set_value('c', 'service_notification_options', $my_contact);
        }
        if ($fields['sn_options_r'] == 1) {
            $this->set_value('r', 'service_notification_options', $my_contact);
        }
        if ($fields['sn_options_f'] == 1) {
            $this->set_value('f', 'service_notification_options', $my_contact);
        }
        if ($fields['sn_options_s'] == 1) {
            $this->set_value('s', 'service_notification_options', $my_contact);
        }
        if ($fields['sn_options_n'] == 1) {
            $this->set_value('n', 'service_notification_options', $my_contact);
        }
        if (empty($my_contact['service_notification_options'])) {
            $this->set_value('n', 'service_notification_options', $my_contact);
        }

        $my_contact['host_notification_options'] = [];
        if ($fields['hn_options_d'] == 1) {
            $this->set_value('d', 'host_notification_options', $my_contact);
        }
        if ($fields['hn_options_u'] == 1) {
            $this->set_value('u', 'host_notification_options', $my_contact);
        }
        if ($fields['hn_options_r'] == 1) {
            $this->set_value('r', 'host_notification_options', $my_contact);
        }
        if ($fields['hn_options_f'] == 1) {
            $this->set_value('f', 'host_notification_options', $my_contact);
        }
        if ($fields['hn_options_s'] == 1) {
            $this->set_value('s', 'host_notification_options', $my_contact);
        }
        if ($fields['hn_options_n'] == 1) {
            $this->set_value('n', 'host_notification_options', $my_contact);
        }
        if (empty($my_contact['host_notification_options'])) {
            $this->set_value('n', 'host_notification_options', $my_contact);
        }

        $pmNotificationcommand = new PluginMonitoringNotificationcommand();
        if ($pmNotificationcommand->getFromDB($fields['sn_commands']) and
            isset($pmNotificationcommand->fields['command_name'])) {
            $this->set_value(PluginMonitoringCommand::$command_prefix . $pmNotificationcommand->getField('command_name'),
                'service_notification_commands', $my_contact);
        } else {
            $this->set_value('', 'service_notification_commands', $my_contact);
        }
        if ($pmNotificationcommand->getFromDB($fields['hn_commands']) and
            isset($pmNotificationcommand->fields['command_name'])) {
            $this->set_value(PluginMonitoringCommand::$command_prefix . $pmNotificationcommand->getField('command_name'),
                'host_notification_commands', $my_contact);
        } else {
            $this->set_value('', 'host_notification_commands', $my_contact);
        }

        // Get first email
        $a_emails = UserEmail::getAllForUser($users_id);
        foreach ($a_emails as $email) {
            $this->set_value($email, 'email', $my_contact);
            break;
        }
        if (!isset($my_contact['email'])) {
            $this->set_value('', 'email', $my_contact);
        }
        $this->set_value($user->fields['phone'], 'pager', $my_contact);

        $this->set_value($fields['ui_administrator'], 'is_admin', $my_contact);
        $this->set_value($fields['ui_can_submit_commands'], 'can_submit_commands', $my_contact);
        if (empty($user->fields['password'])) {
            $this->set_value(self::$default['webui']['contacts']['password'], 'password', $my_contact);
        } else {
            $this->set_value($user->fields['password'], 'password', $my_contact);
        }

        $my_contact = $this->properties_list_to_string($my_contact);
        PluginMonitoringToolbox::logIfDebug("- built: " . print_r($my_contact, true));

        return $my_contact;
    }


    function generateTimeperiodsCfg($tag = '', $file = false)
    {
        global $PM_CONFIG;

        PluginMonitoringToolbox::logIfDebug("Starting generateTimeperiodsCfg ...");

        // Indeed, time periods are compiled 'on the fly' during the other objects building process!
        $a_timeperiods = [];
        if (isset($_SESSION['plugin_monitoring']['timeperiods'])) {
            PluginMonitoringToolbox::logIfDebug("Time periods: ");
            foreach ($_SESSION['plugin_monitoring']['timeperiods'] as $data) {
                $my_timeperiod = [];
                foreach ($data as $key => $val) {
                    $this->set_value($val, $key, $my_timeperiod);
                }
                if (!empty($_SESSION['plugin_monitoring']['definition_order'])) {
                    $this->set_value($_SESSION['plugin_monitoring']['definition_order'], 'definition_order', $my_timeperiod);
                }
                PluginMonitoringToolbox::logIfDebug(" - " . print_r($my_timeperiod, true));
                $a_timeperiods[] = $this->properties_list_to_string($my_timeperiod);
            }
        }
        if (isset($_SESSION['plugin_monitoring']['holidays'])) {
            PluginMonitoringToolbox::logIfDebug("Exclusion periods: ");
            foreach ($_SESSION['plugin_monitoring']['holidays'] as $data) {
                $my_timeperiod = [];
                foreach ($data as $key => $val) {
                    $this->set_value($val, $key, $my_timeperiod);
                }
                if (!empty($_SESSION['plugin_monitoring']['definition_order'])) {
                    $this->set_value($_SESSION['plugin_monitoring']['definition_order'], 'definition_order', $my_timeperiod);
                }
                PluginMonitoringToolbox::logIfDebug(" - " . print_r($my_timeperiod, true));
                $a_timeperiods[] = $this->properties_list_to_string($my_timeperiod);
            }
        }

        PluginMonitoringToolbox::log("Found " . count($a_timeperiods) . " time periods");
        PluginMonitoringToolbox::logIfDebug("End generateTimeperiodsCfg");

        if ($PM_CONFIG['build_files']) {
            $config = "# Generated by the monitoring plugin for GLPI\n# on " . date("Y-m-d H:i:s") . "\n";
            $config .= "# ---\n";
            $config .= "# Time periods\n";
            $config .= "# ---\n";

            foreach ($a_timeperiods as $data) {
                $config .= $this->writeFile("contact", $data);
            }
            $filename = PLUGIN_MONITORING_CFG_DIR . '/' . $tag . '-timeperiods.cfg';
            file_put_contents($filename, $config);
            PluginMonitoringToolbox::logIfDebug("Written file: " . $filename);

            if ($file) return $config;
        }

        return $a_timeperiods;
    }


    function _addHoliday($entities_id = -1, $holidays_id = -1)
    {
//        $hostconfig = new PluginMonitoringHostconfig();
        $holiday = new Calendar();
        if (!$holiday->getFromDB($holidays_id)) {
            PluginMonitoringToolbox::log("[ERROR]  invalid holiday: $holidays_id ...");
            return false;
        }

        if (!isset($_SESSION['plugin_monitoring']['holidays'])) {
            $_SESSION['plugin_monitoring']['holidays'] = [];
        }
        PluginMonitoringToolbox::logIfDebug("Starting _addHoliday: $entities_id / $holidays_id ...");

        // Get the contact entity jet lag ...
        $tz_suffix = '';
        if (isset($_SESSION['plugin_monitoring']['entities'][$entities_id])) {
            $tz_suffix = '_' . $_SESSION['plugin_monitoring']['entities'][$entities_id]['jet_lag'];
            if ($tz_suffix == '_0') {
                $tz_suffix = '';
            }
        }

//        // Jetlag for required entity ...
//        if (!isset($_SESSION['plugin_monitoring']['jetlag'])) {
//            $_SESSION['plugin_monitoring']['jetlag'] = [];
//        }
//        if (!isset($_SESSION['plugin_monitoring']['jetlag'][$entities_id])) {
//            $_SESSION['plugin_monitoring']['jetlag'][$entities_id] =
//                $hostconfig->getValueAncestor('jetlag', $entities_id);
//        }
//        $tz_suffix = $_SESSION['plugin_monitoring']['jetlag'][$entities_id];
//        if ($tz_suffix == '_0') {
//            $tz_suffix = '';
//        }
        PluginMonitoringToolbox::logIfDebug(" - entity: $entities_id, jetlag: $tz_suffix");

        $tmp = [];
        if ($tz_suffix == 0) {
            $tmp['timeperiod_name'] = self::monitoringFilter($holiday->fields['name']);
            $tmp['alias'] = $holiday->fields['name'];
        } else {
            $tmp['timeperiod_name'] = self::monitoringFilter($holiday->fields['name'] . "_" . $tz_suffix);
            $tmp['alias'] = $holiday->fields['name'] . " (" . $tz_suffix . ")";
        }

        // If timeperiod already exists in memory ...
        if (isset($_SESSION['plugin_monitoring']['holidays'][$tmp['timeperiod_name']])) {
            return $tmp['timeperiod_name'];
        }

        PluginMonitoringToolbox::logIfDebug(" - _addHoliday, building TP '{$tmp['timeperiod_name']}' for entity: $entities_id");
        // $holiday->getFromDB($a_choliday['holidays_id']);
        if ($holiday->fields['is_perpetual'] == 1
            && $holiday->fields['begin_date'] == $holiday->fields['end_date']) {
            $datetime = strtotime($holiday->fields['begin_date']);
            $tmp[strtolower(date('F', $datetime)) .
            ' ' . date('j', $datetime)] = '00:00-24:00';
        }

        $days = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday',
            'friday', 'saturday'];
        $saturday = '';
        $reportHours = 0;
//        $beforeday = 'saturday';
        foreach ($days as $numday => $day) {
            if (isset($tmp[$day])) {
                $splitDay = explode(',', $tmp[$day]);
                $toAdd = '';
                if ($reportHours > 0) {
                    $toAdd = '00:00-' . sprintf("%02s", $reportHours) . ':00';
                    $reportHours = 0;
                }
                foreach ($splitDay as $num => $hourMinute) {
                    $previous_begin = 0;
                    $beginEnd = explode('-', $hourMinute);
                    // ** Begin **
                    $split = explode(':', $beginEnd[0]);
                    $split[0] .= $tz_suffix;
                    if ($split[0] > 24) {
                        //$reportHours = $split[0]-24;
                        unset($splitDay[$num]);
                    } else {
                        if ($split[0] < 0) {
                            $reportHours = $split[0];
                            $previous_begin = 24 + $split[0];
                            $split[0] = '00';
                        }
                        $beginEnd[0] = sprintf("%02s", $split[0]) . ':' . $split[1];
                        // ** End **
                        $split = explode(':', $beginEnd[1]);
                        $split[0] .= $tz_suffix;
                        if ($split[0] < 0) {
                            if ($numday - 1 == -1) {
                                $saturday .= "," . sprintf("%02s", $previous_begin) . ":00-" . sprintf("%02s", (24 + $split[0])) . ":00";
                            } else {
                                $tmp[$days[($numday - 1)]] .= "," . sprintf("%02s", $previous_begin) . ":00-" . sprintf("%02s", (24 + $split[0])) . ":00";
                            }
                            unset($splitDay[$num]);
                        } else {
                            if ($split[0] > 24) {
                                $reportHours = $split[0] - 24;
                                $split[0] = 24;
                            }
                            $beginEnd[1] = sprintf("%02s", $split[0]) . ':' . $split[1];

                            $hourMinute = implode('-', $beginEnd);
                            $splitDay[$num] = $hourMinute;
                        }
                    }
                }
                if ($reportHours < 0) {
                    $reportHours = 0;
                }
                if (!empty($toAdd)) {
                    array_unshift($splitDay, $toAdd);
                }
                $tmp[$day] = implode(',', $splitDay);
            } else if ($reportHours > 0) {
                //$tmp[$day] = '00:00-'.$reportHours.':00';
                $reportHours = 0;
            }
//            $beforeday = $day;
        }
        // Manage for report hours from saturday to sunday
        if ($reportHours > 0) {
            $splitDay = explode(',', $tmp['sunday']);
            array_unshift($splitDay, '00:00-' . sprintf("%02s", $reportHours) . ':00');
            $tmp['sunday'] = implode(',', $splitDay);
        }
        if ($saturday != '') {
            if (isset($tmp['saturday'])) {
                $tmp['saturday'] .= $saturday;
            } else {
                $tmp['saturday'] = $saturday;
            }
        }

        // concatenate if needed
        foreach ($days as $day) {
            if (isset($tmp[$day])) {
                $splitDay = explode(',', $tmp[$day]);
                $beforeHour = '';
                $beforeNum = 0;
                foreach ($splitDay as $num => $data) {
                    if (substr($data, 0, 2) == $beforeHour) {
                        $splitDay[$beforeNum] = substr($splitDay[$beforeNum], 0, 6) . substr($data, 6, 5);
                        $beforeHour = substr($data, 6, 2);
                        unset($splitDay[$num]);
                    } else {
                        $beforeHour = substr($data, 6, 2);
                        $beforeNum = $num;
                    }
                }
                $tmp[$day] = implode(',', $splitDay);
            }
        }

        $_SESSION['plugin_monitoring']['holidays'][$tmp['timeperiod_name']] = $tmp;

        PluginMonitoringToolbox::logIfDebug("End _addHoliday: {$tmp['timeperiod_name']}");

        return $tmp['timeperiod_name'];
    }


    function _addTimeperiod($entities_id = -1, $calendars_id = -1)
    {
        if (!isset($_SESSION['plugin_monitoring']['timeperiods'])) {
            $_SESSION['plugin_monitoring']['timeperiods'] = [];
        }
//        if (!isset($_SESSION['plugin_monitoring']['timeperiodsmapping'])) {
//            $_SESSION['plugin_monitoring']['timeperiodsmapping'] = [];
//        }
        PluginMonitoringToolbox::logIfDebug("Starting _addTimeperiod: $entities_id / $calendars_id ...");

        $calendar = new Calendar();
//        $hostconfig = new PluginMonitoringHostconfig();
        $entity = new Entity();

        if (!$entity->getFromDB($entities_id)) {
            PluginMonitoringToolbox::log("[ERROR] invalid entity: $entities_id");
            return false;
        }

        if (!$calendar->getFromDB($calendars_id)) {
            PluginMonitoringToolbox::log("[ERROR]  invalid calendar: $calendars_id ...");
            return false;
        }

        // Jetlag for required entity ...
        $tz_suffix = 0;
        if (isset($_SESSION['plugin_monitoring']['entities'][$entities_id])) {
            $tz_suffix = (int)$_SESSION['plugin_monitoring']['entities'][$entities_id]['jet_lag'];
        }

        $tmp = [];
        if (empty($tz_suffix)) {
            $tmp['timeperiod_name'] = self::monitoringFilter($calendar->getName());
            $tmp['alias'] = $calendar->getName();
        } else {
            $tmp['timeperiod_name'] = self::monitoringFilter($calendar->getName() . '_' . $tz_suffix);
            $tmp['alias'] = $calendar->getName() . " (" . $tz_suffix . ")";
        }

        // If timeperiod already exists in memory ...
        if (isset($_SESSION['plugin_monitoring']['timeperiods'][$tmp['timeperiod_name']])) {
            PluginMonitoringToolbox::logIfDebug(" - TP '{$tmp['timeperiod_name']}' is still defined.");
            return true;
        }

        PluginMonitoringToolbox::log(" - building TP '{$tmp['timeperiod_name']}', jet lag: $tz_suffix");

        $calendarSegment = new CalendarSegment();
        $a_listsegment = $calendarSegment->find("`calendars_id`='" . $calendars_id . "'");
        $a_cal = [];
        foreach ($a_listsegment as $datasegment) {
            $begin = preg_replace("/:00$/", "", $datasegment['begin']);
            $end = preg_replace("/:00$/", "", $datasegment['end']);
            $day = "";
            switch ($datasegment['day']) {
                case "0":
                    $day = "sunday";
                    break;

                case "1":
                    $day = "monday";
                    break;

                case "2":
                    $day = "tuesday";
                    break;

                case "3":
                    $day = "wednesday";
                    break;

                case "4":
                    $day = "thursday";
                    break;

                case "5":
                    $day = "friday";
                    break;

                case "6":
                    $day = "saturday";
                    break;

            }
            $a_cal[$day][] = $begin . "-" . $end;
        }
        PluginMonitoringToolbox::logIfDebug(" - _addTimeperiod, building calendar '{$tmp['timeperiod_name']}': " . print_r($a_cal, true));
        foreach ($a_cal as $day => $a_times) {
            $tmp[$day] = implode(',', $a_times);
        }

        $calendar_Holiday = new Calendar_Holiday();
        $a_cholidays = $calendar_Holiday->find("`calendars_id`='" . $calendars_id . "'");
        $a_excluded = [];
        foreach ($a_cholidays as $a_choliday) {
            PluginMonitoringToolbox::logIfDebug(" - _addTimeperiod, building holiday '{$a_choliday['holidays_id']}'");
            $a_excluded[] = $this->_addHoliday($entities_id, $a_choliday['holidays_id']);
            // $holiday->getFromDB($a_choliday['holidays_id']);
            // if ($holiday->fields['is_perpetual'] == 1
            // && $holiday->fields['begin_date'] == $holiday->fields['end_date']) {
            // $datetime = strtotime($holiday->fields['begin_date']);
            // $tmp[strtolower(date('F', $datetime)).
            // ' '.date('j', $datetime)] = '00:00-24:00';
            // }
        }
        if (count($a_excluded) > 0) {
            $tmp['exclude'] = implode(',', $a_excluded);
        }

        $days = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
        $saturday = '';
        $reportHours = 0;
        foreach ($days as $numday => $day) {
            if ($tz_suffix == 0 or !isset($tmp[$day])) {
                if ($reportHours > 0) {
                    $reportHours = 0;
                }
            }

            $splitDay = explode(',', $tmp[$day]);
            $toAdd = '';
            if ($reportHours > 0) {
                $toAdd = '00:00-' . sprintf("%02s", $reportHours) . ':00';
                $reportHours = 0;
            }
            foreach ($splitDay as $num => $hourMinute) {
                $previous_begin = 0;
                $beginEnd = explode('-', $hourMinute);

                // ** Begin **, get 00:00
                $split = explode(':', $beginEnd[0]);
                $hour = (int)$split[0];
                $hour += $tz_suffix;
                if ($hour > 24) {
                    //$reportHours = $split[0]-24;
                    unset($splitDay[$num]);
                } else {
                    if ($hour < 0) {
                        $reportHours = $hour;
                        $previous_begin = 24 + $hour;
                        $split[0] = '00';
                    }
                    $beginEnd[0] = sprintf("%02s", $hour) . ':' . $split[1];

                    // ** End **
                    $split = explode(':', $beginEnd[1]);
                    $hour = (int)$split[0];
                    $hour += $tz_suffix;
                    if ($hour < 0) {
                        if ($numday - 1 == -1) {
                            $saturday .= "," . sprintf("%02s", $previous_begin) . ":00-" . sprintf("%02s", (24 + $hour)) . ":00";
                        } else {
                            $tmp[$days[($numday - 1)]] .= "," . sprintf("%02s", $previous_begin) . ":00-" . sprintf("%02s", (24 + $hour)) . ":00";
                        }
                        unset($splitDay[$num]);
                    } else {
                        if ($hour > 24) {
                            $reportHours = $hour - 24;
                            $hour = 24;
                        }
                        $beginEnd[1] = sprintf("%02s", $hour) . ':' . $split[1];

                        $hourMinute = implode('-', $beginEnd);
                        $splitDay[$num] = $hourMinute;
                    }
                }
            }
            if ($reportHours < 0) {
//                     if (!isset($tmp[$beforeday])) {
//                        $tmp[$beforeday] = [];
//                     }
//                     $splitBeforeDay = explode(',', $tmp[$beforeday]);
//                     $splitBeforeDay[] = sprintf("%02s", (24 + $reportHours)).':00-24:00';
//                     $tmp[$beforeday] = implode(',', $splitBeforeDay);
                $reportHours = 0;
            }
            if (!empty($toAdd)) {
                array_unshift($splitDay, $toAdd);
            }
            $tmp[$day] = implode(',', $splitDay);
//            $beforeday = $day;
        }
        // Manage for report hours from saturday to sunday
        if ($reportHours > 0) {
            $splitDay = explode(',', $tmp['sunday']);
            array_unshift($splitDay, '00:00-' . sprintf("%02s", $reportHours) . ':00');
            $tmp['sunday'] = implode(',', $splitDay);
        }
        if (!empty($saturday)) {
            if (isset($tmp['saturday'])) {
                $tmp['saturday'] .= $saturday;
            } else {
                $tmp['saturday'] = $saturday;
            }
        }

        // concatenate if needed
        foreach ($days as $day) {
            if (!isset($tmp[$day])) {
                break;
            }
            $splitDay = explode(',', $tmp[$day]);
            $beforeHour = '';
            $beforeNum = 0;
            foreach ($splitDay as $num => $data) {
                if (substr($data, 0, 2) == $beforeHour) {
                    $splitDay[$beforeNum] = substr($splitDay[$beforeNum], 0, 6) . substr($data, 6, 5);
                    $beforeHour = substr($data, 6, 2);
                    unset($splitDay[$num]);
                } else {
                    $beforeHour = substr($data, 6, 2);
                    $beforeNum = $num;
                }
            }
            $tmp[$day] = implode(',', $splitDay);
        }

        $_SESSION['plugin_monitoring']['timeperiods'][$tmp['timeperiod_name']] = $tmp;

        PluginMonitoringToolbox::log(" - new TP: {$tmp['timeperiod_name']}");
        PluginMonitoringToolbox::log(" - new TP: {$tmp['timeperiod_name']}: " . print_r($tmp, true));

        return true;
    }


    function generateRealmsCfg($tag = '', $file = false)
    {
        global $PM_CONFIG;

        if (!isset($_SESSION['plugin_monitoring'])) {
            $_SESSION['plugin_monitoring'] = [];
        }

        PluginMonitoringToolbox::logIfDebug("Starting generateRealmsCfg ...");

        // Get entities concerned by the provided tag and get the definition order of the highest entty
//        $where = '';
//        if (!empty($_SESSION['plugin_monitoring']['allowed_entities'])) {
//            $where = getEntitiesRestrictRequest("WHERE",
//                "glpi_entities", '',
//                $_SESSION['plugin_monitoring']['allowed_entities']);
//        }

        // Indeed, realms are compiled 'on the fly' during the other objects building process!
        $a_realms = [];
        if (isset($_SESSION['plugin_monitoring']['realms'])) {
            foreach ($_SESSION['plugin_monitoring']['realms'] as $data) {
                $my_realm = [];
                $this->set_value(self::monitoringFilter($data['name']), 'realm_name', $my_realm);
                $this->set_value($data['comment'], 'notes', $my_realm);
                if ($data['name'] != 'All') {
                    $this->set_value('All', 'higher_realms', $my_realm);
                }
                if (!empty($_SESSION['plugin_monitoring']['definition_order'])) {
                    $this->set_value($_SESSION['plugin_monitoring']['definition_order'], 'definition_order', $my_realm);
                }
                $this->set_value($data['is_default'], 'default', $my_realm);
                $a_realms[] = $this->properties_list_to_string($my_realm);
            }
        }

        PluginMonitoringToolbox::log("Found " . count($a_realms) . " realms");
        PluginMonitoringToolbox::logIfDebug("End generateRealmsCfg");

        if ($PM_CONFIG['build_files']) {
            $config = "# Generated by the monitoring plugin for GLPI\n# on " . date("Y-m-d H:i:s") . "\n";
            $config .= "# ---\n";
            $config .= "# Realms\n";
            $config .= "# ---\n";

            foreach ($a_realms as $data) {
                $config .= $this->writeFile("contact", $data);
            }
            $filename = PLUGIN_MONITORING_CFG_DIR . '/' . $tag . '-realms.cfg';
            file_put_contents($filename, $config);
            PluginMonitoringToolbox::logIfDebug("Written file: " . $filename);

            if ($file) return $config;
        }

        return $a_realms;
    }


    function _addRealm(PluginMonitoringRealm $realm)
    {

        if (!isset($_SESSION['plugin_monitoring']['realms'])) {
            $_SESSION['plugin_monitoring']['realms'] = [];
        }

        if (!isset($_SESSION['plugin_monitoring']['realms'][$realm->getID()])) {
            $_SESSION['plugin_monitoring']['realms'][$realm->getID()] = $realm->fields;
            PluginMonitoringToolbox::logIfDebug("Added realm: " . $realm->getName());
        }

        PluginMonitoringToolbox::logIfDebug("End _addRealm: " . print_r($realm->fields, true));
    }


    /**
     * Set a property value with the right type (str, int, bool, float)
     *
     * @param $val
     * @param $key
     * @param $data
     */
    function set_value($val, $key, &$data)
    {
        if ($this->_is_a_list_property($key)) {
            // Properties that are lists are always strings lists...
            if (!isset($data[$key])) {
                $data[$key] = [];
            }
            $data[$key][] = (string)$val;
        } else {
            switch ($key) {
                // boolean fields
                case "active_checks_enabled":
                case "broker_complete_links":
                case "business_rule_downtime_as_ack":
                case "business_rule_smart_notifications":
                case "can_submit_commands":
                case "check_freshness":
                case "default":
                case "enable_environment_macros":
                case "event_handler_enabled":
                case "expert":
                case "explode_hostgroup":
                case "failure_prediction_enabled":
                case "flap_detection_enabled":
                case "host_dependency_enabled":
                case "host_notifications_enabled":
                case "inherits_parent":
                case "is_active":
                case "is_admin":
                case "is_volatile":
                case "merge_host_contacts":
                case "notifications_enabled":
                case "obsess_over_host":
                case "obsess_over_service":
                case "parallelize_check":
                case "passive_checks_enabled":
                case "process_perf_data":
                case "register":
                case "retain_nonstatus_information":
                case "retain_status_information":
                case "service_notifications_enabled":
                case "snapshot_enabled":
                case "trigger_broker_raise_enabled":
                    // Always a string value
                    $data[$key] = (string)$val;
                    break;

                // integer fields
                case "business_impact":
                case "check_interval":
                case "discoveryrule_order":
                case "first_notification":
                case "first_notification_delay":
                case "first_notification_time":
                case "freshness_threshold":
                case "high_flap_threshold":
                case "id":
                case "last_notification":
                case "last_notification_time":
                case "low_flap_threshold":
                case "max_check_attempts":
                case "min_business_impact":
                case "notification_interval":
                case "retry_interval":
                case "snapshot_interval":
                case "timeout":
                case "time_to_orphanage":
                    // Always a string value
//                    $data[$key] = (int)$val;
                    $data[$key] = (string)$val;
                    break;

                default: // string
                    $data[$key] = (string)$val;
            }
        }
    }


    function properties_list_to_string($data)
    {
        foreach ($data as $key => $val) {
            if ($this->_is_a_list_property($key)) {
                $data[$key] = implode(',', array_unique($val));
            }
        }
        return $data;
    }


    function _is_a_list_property($key)
    {
        return in_array($key, [
            '_DOCUMENTS',
            "business_impact_modulations", "business_rule_host_notification_options",
            "business_rule_service_notification_options", "checkmodulations", "contacts", "contact_groups",
            "custom_views", "dateranges", "escalations", "escalation_options", "exclude", "execution_failure_criteria",
            "flap_detection_options", "higher_realms", "hostgroups", "hostgroup_members", "host_notification_commands",
            "host_notification_options", "labels", "macromodulations", "members", "modules", "notificationways",
            "notification_failure_criteria", "notification_options", "parents", "realm_members", "resultmodulations",
            "servicegroups", "service_dependencies", "service_excludes", "service_includes",
            "service_notification_commands", "service_notification_options", "service_overrides", "snapshot_criteria",
            "stalking_options", "trending_policies", "unknown_members", "use"
        ]);
    }
}
