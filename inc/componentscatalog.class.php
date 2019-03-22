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

class PluginMonitoringComponentscatalog extends CommonDropdown
{
    const HOMEPAGE = 1024;
    const DASHBOARD = 2048;

    public $display_dropdowntitle = false;

    public $first_level_menu = "plugins";
    public $second_level_menu = "pluginmonitoringmenu";
    public $third_level_menu = "componentscatalog";

    static $rightname = 'plugin_monitoring_componentscatalog';


    static function getTypeName($nb = 0)
    {
        return __('Components catalog', 'monitoring');
    }


    function defineTabs($options = [])
    {

        $ong = [];
        $this->addDefaultFormTab($ong)
//            ->addStandardTab('Document_Item', $ong, $options)
            ->addStandardTab("PluginMonitoringComponentscatalog", $ong, $options)
            ->addStandardTab('Log', $ong, $options);

        return $ong;
    }


    /**
     * Display tab
     *
     * @param $item         CommonGLPI
     * @param $withtemplate integer
     *
     * @return array|string name of the tab(s) to display
     */
    function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        if (!$withtemplate) {
            switch ($item->getType()) {
                case 'Central' :
                    if (Session::haveRight("plugin_monitoring_central", READ)
                        && Session::haveRight("plugin_monitoring_componentscatalog", PluginMonitoringComponentscatalog::HOMEPAGE)) {
                        return [1 => __('Components catalogs', 'monitoring')];
                    }
                    return '';
            }
            /* @var PluginMonitoringComponentscatalog $item */
            if ($item->getID() > 0) {
                $ong = [];
                $ong[1] = self::createTabEntry(__('Components', 'monitoring'), self::countForComponents($item));
                $ong[2] = self::createTabEntry(__('Static hosts', 'monitoring'), self::countForStaticHosts($item));
                $ong[3] = self::createTabEntry(_n('Rule', 'Rules', 2), self::countForRules($item));
                $ong[4] = self::createTabEntry(__('Dynamic hosts', 'monitoring'), self::countForDynamicHosts($item));
                $ong[5] = self::createTabEntry(__('Contacts', 'monitoring'), self::countForContacts($item));
                return $ong;
            }
        }
        return '';
    }


    /**
     * @param $item PluginMonitoringComponentscatalog object
     *
     * @return int
     */
    static function countForStaticHosts(PluginMonitoringComponentscatalog $item)
    {
        $dbu = new DbUtils();
        return $dbu->countElementsInTable('glpi_plugin_monitoring_componentscatalogs_hosts',
            ['WHERE' => "`plugin_monitoring_componentscatalogs_id` = '" . $item->getID() . "' AND `is_static`='1'"]);
    }


    /**
     * @param $item PluginMonitoringComponentscatalog object
     *
     * @return int
     */
    static function countForDynamicHosts(PluginMonitoringComponentscatalog $item)
    {
        $dbu = new DbUtils();
        return $dbu->countElementsInTable('glpi_plugin_monitoring_componentscatalogs_hosts',
            ['WHERE' => "`plugin_monitoring_componentscatalogs_id` = '" . $item->getID() . "' AND `is_static`='0'"]);
    }


    /**
     * @param $item PluginMonitoringComponentscatalog object
     *
     * @return int
     */
    static function countForRules(PluginMonitoringComponentscatalog $item)
    {
        $dbu = new DbUtils();
        return $dbu->countElementsInTable('glpi_plugin_monitoring_componentscatalogs_rules',
            ['WHERE' => "`plugin_monitoring_componentscatalogs_id` = '" . $item->getID() . "'"]);
    }


    /**
     * @param $item PluginMonitoringComponentscatalog object
     *
     * @return int
     */
    static function countForComponents(PluginMonitoringComponentscatalog $item)
    {
        $dbu = new DbUtils();
        return $dbu->countElementsInTable('glpi_plugin_monitoring_componentscatalogs_components',
            ['WHERE' => "`plugin_monitoring_componentscatalogs_id` = '" . $item->getID() . "'"]);
    }


    /**
     * @param $item PluginMonitoringComponentscatalog object
     *
     * @return int
     */
    static function countForContacts(PluginMonitoringComponentscatalog $item)
    {
        $dbu = new DbUtils();
        return $dbu->countElementsInTable('glpi_plugin_monitoring_contacts_items',
            ['WHERE' => "`items_id` = '" . $item->getID() . "' AND `itemtype`='PluginMonitoringComponentscatalog'"]);
    }


    /**
     * Display content of tab
     *
     * @param CommonGLPI $item
     * @param integer $tabnum
     * @param int $withtemplate
     *
     * @return boolean true
     */
    static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        /* @var CommonDBTM $item */
        if ($item->getID() > 0) {
            switch ($tabnum) {
                case 1:
                    $pmComponentscatalog_Component = new PluginMonitoringComponentscatalog_Component();
                    $pmComponentscatalog_Component->showComponents($item->getID());
                    break;

                case 2 :
                    // Hosts static
                    $pmComponentscatalog_Host = new PluginMonitoringComponentscatalog_Host();
                    $pmComponentscatalog_Host->showHosts($item->getID(), true);
                    break;

                case 3 :
                    $pmComponentscatalog_rule = new PluginMonitoringComponentscatalog_rule();
                    $pmComponentscatalog_rule->showRules($item->getID());
                    break;

                case 4 :
                    // Hosts dynamic
                    $pmComponentscatalog_Host = new PluginMonitoringComponentscatalog_Host();
                    $pmComponentscatalog_Host->showHosts($item->getID(), false);
                    break;

                case 5 :
                    $pmContact_Item = new PluginMonitoringContact_Item();
                    $pmContact_Item->showContacts("PluginMonitoringComponentscatalog", $item->getID());
                    break;

                default :

            }
        }
        return true;
    }


    function getAdditionalFields()
    {
        return [
//            [
//                'name' => 'hosts_count',
//                'label' => __('Related hosts count', 'monitoring'),
//                'type' => 'hosts_count'
//            ],
            [
                'name' => 'notification_interval',
                'label' => __('Interval between 2 notifications (in minutes)', 'monitoring'),
                'type' => 'notification_interval'
            ],
            [
                'name' => 'hostsnotification_id',
                'label' => __('Hosts notification options', 'monitoring'),
                'type' => 'hosts_notification_id',
            ],
            [
                'name' => 'servicesnotification_id',
                'label' => __('Services notification options', 'monitoring'),
                'type' => 'services_notification_id'
            ],
            [
                'name' => 'additional_templates',
                'label' => __('Additional templates list', 'monitoring'),
                'type' => 'additional_templates'
            ]
        ];
    }


    function rawSearchOptions()
    {
        $tab = [];
        $tab[] = [
            'id' => 'common',
            'name' => __('Components catalogs', 'monitoring')
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
            'field' => 'notification_interval',
            'datatype' => 'number',
            'name' => __('Interval between 2 notifications (in minutes)', 'monitoring'),
        ];

        $tab[] = [
            'id' => $index++,
            'table' => PluginMonitoringHostnotificationtemplate::getTable(),
            'field' => 'name',
            'datatype' => 'itemlink',
            'linkfield' => 'hostnotificationtemplates_id',
            'name' => __('Hosts notification template', 'monitoring'),
        ];

        $tab[] = [
            'id' => $index++,
            'table' => PluginMonitoringServicenotificationtemplate::getTable(),
            'field' => 'name',
            'datatype' => 'itemlink',
            'linkfield' => 'servicenotificationtemplates_id',
            'name' => __('Services notification template', 'monitoring'),
        ];

        $tab[] = [
            'id' => $index++,
            'table' => $this->getTable(),
            'field' => 'additional_templates',
            'datatype' => 'string',
            'name' => __('Additional templates list)', 'monitoring'),
        ];

        // Fred: I count not get the related hosts count... :/
//        $tab[] = [
//            'id' => $index,
//            'table' => PluginMonitoringComponentscatalog_Host::getTable(),
//            'field' => 'hosts_count',
//            'datatype' => 'specific',
//            'name' => __('Related hosts count', 'monitoring'),
//        ];

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


    /**
     * Convert hours/minutes for interval to minutes only
     *
     * @param array $input - data from the form
     *
     * @return array
     */
    function prepareInputForUpdate($input)
    {
        if (isset($input["notification_interval_hours"])
            and isset($input['notification_interval_minutes'])) {
            $input['notification_interval'] = (int)$input["notification_interval_hours"] * 60
                + (int)$input['notification_interval_minutes'];
            unset($input["notification_interval_hours"]);
            unset($input['notification_interval_minutes']);
        }

        return $input;
    }


    static function getSpecificValueToDisplay($field, $values, array $options = [])
    {
        PluginMonitoringToolbox::log("getSpecificValueToDisplay: $field");
        if (!is_array($values)) {
            $values = [$field => $values];
        }

        switch ($field) {

            case 'hosts_count' :
                return 12;
                break;

        }
        return parent::getSpecificValueToDisplay($field, $values, $options);
    }


    function displaySpecificTypeField($ID, $field = [])
    {
//        PluginMonitoringToolbox::log("displaySpecificTypeField: " . print_r($field, true));
        switch ($field['type']) {
            case 'notification_interval' :
                if ($ID <= 0) {
                    $this->fields['notification_interval'] = 30;
                }
                $hours = (int)($this->fields['notification_interval'] / 60);
                $minutes = (int)($this->fields['notification_interval'] % 60);
                Dropdown::showNumber('notification_interval_hours', [
                        'value' => $hours,
                        'min' => 0,
                        'max' => 168,
                        'step' => 1
                    ]
                );
                echo "&nbsp;" . __('hours', 'monitoring');
                Dropdown::showNumber('notification_interval_minutes', [
                        'value' => $minutes,
                        'min' => 0,
                        'max' => 59,
                        'step' => 1
                    ]
                );
                echo "&nbsp;" . __('minutes', 'monitoring');
                break;

            case 'hosts_notification_id' :
                Dropdown::show("PluginMonitoringHostnotificationtemplate", [
                    'name' => 'hostnotificationtemplates_id',
                    'value' => $this->fields['hostnotificationtemplates_id']
                ]);
                break;

            case 'services_notification_id' :
                Dropdown::show("PluginMonitoringServicenotificationtemplate", [
                    'name' => 'servicenotificationtemplates_id',
                    'value' => $this->fields['servicenotificationtemplates_id']
                ]);
                break;

            case 'additional_templates' :
                $objectDescription = autoName($this->fields["additional_templates"],
                    "name", 1, $this->getType());
                Html::autocompletionTextField($this, 'additional_templates', ['value' => $objectDescription]);
                break;
        }
    }


    /**
     * @param CommonDBTM $item
     */
    static function replayRulesCatalog($item)
    {

        $datas = getAllDatasFromTable("glpi_plugin_monitoring_componentscatalogs_rules",
            "`plugin_monitoring_componentscatalogs_id`='" . $item->getID() . "'");
        $pmComponentscatalog_rule = new PluginMonitoringComponentscatalog_rule();
        foreach ($datas as $data) {
            $pmComponentscatalog_rule->getFromDB($data['id']);
            PluginMonitoringComponentscatalog_rule::getItemsDynamically($pmComponentscatalog_rule);
        }
    }


    static function removeCatalog($item)
    {
        global $DB;

        // Delete related hosts
        $query = "SELECT * FROM `glpi_plugin_monitoring_componentscatalogs_hosts`
         WHERE `plugin_monitoring_componentscatalogs_id`='" . $item->fields["id"] . "'
            AND `is_static`='1'";
        $result = $DB->query($query);
        $pmComponentscatalog_Host = new PluginMonitoringComponentscatalog_Host();
        while ($data = $DB->fetch_array($result)) {
            $pmComponentscatalog_Host->delete($data);
        }

        // Delete related rules
        $query = "SELECT * FROM `glpi_plugin_monitoring_componentscatalogs_rules`
         WHERE `plugin_monitoring_componentscatalogs_id`='" . $item->fields["id"] . "'";
        $result = $DB->query($query);
        $pmComponentscatalog_rule = new PluginMonitoringComponentscatalog_rule();
        while ($data = $DB->fetch_array($result)) {
            $pmComponentscatalog_rule->delete($data);
        }
    }
}
