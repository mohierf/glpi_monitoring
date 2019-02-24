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
        $values = parent::getRights();
        $values[self::HOMEPAGE] = __('See in homepage', 'monitoring');
        $values[self::DASHBOARD] = __('See in dashboard', 'monitoring');

        return $values;
    }


    function defineTabs($options = [])
    {

        $ong = [];
        $this->addDefaultFormTab($ong)
            ->addStandardTab('Document_Item', $ong, $options)
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
                    if (Session::haveRight("plugin_monitoring_homepage", READ)
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

        switch ($item->getType()) {
            case 'Central' :
                $pmDisplay = new PluginMonitoringDisplay();
                $pmDisplay->showCounters("Componentscatalog");

                $pmComponentscatalog = new PluginMonitoringComponentscatalog();
                $pmComponentscatalog->showChecks();
                return true;

        }
        if ($item->getID() > 0) {
            switch ($tabnum) {
                case 1:
                    $pmComponentscatalog_Component = new PluginMonitoringComponentscatalog_Component();
                    $pmComponentscatalog_Component->showComponents($item->getID());
                    break;

                case 2 :
                    $pmComponentscatalog_Host = new PluginMonitoringComponentscatalog_Host();
                    $pmComponentscatalog_Host->showHosts($item->getID(), true);
                    break;

                case 3 :
                    $pmComponentscatalog_rule = new PluginMonitoringComponentscatalog_rule();
                    $pmComponentscatalog_rule->showRules($item->getID());
                    break;

                case 4 :
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
            'id' => $index,
            'table' => $this->getTable(),
            'field' => 'additional_templates',
            'datatype' => 'string',
            'name' => __('Additional templates list)', 'monitoring'),
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


    function displaySpecificTypeField($ID, $field = [])
    {
        switch ($field['type']) {
            case 'notification_interval' :
                if ($ID > 0) {
//               $this->fields['notification_interval'];
                } else {
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


    function showChecks()
    {
        echo "<table class='tab_cadre' width='100%'>";
        echo "<tr class='tab_bg_4' style='background: #cececc;'>";

        $a_componentscatalogs = $this->find();
        $i = 0;
        foreach ($a_componentscatalogs as $data) {
            $ret = $this->getInfoOfCatalog($data['id']);
            if ($ret[0] > 0) {
                echo "<td style='vertical-align: top;'>";

                echo $this->showWidget($data['id']);
                if (isset($_SESSION['plugin_monitoring']['reduced_interface'])) {
                    $this->ajaxLoad($data['id'], !$_SESSION['plugin_monitoring']['reduced_interface']);
                } else {
                    $this->ajaxLoad($data['id'], TRUE);
                }

                echo "</td>";

                $i++;
                if ($i == '4') {
                    echo "</tr>";
                    echo "<tr class='tab_bg_4' style='background: #cececc;'>";
                    $i = 0;
                }
            }
        }

        echo "</tr>";
        echo "</table>";
    }


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

        $pmComponentscatalog_Host = new PluginMonitoringComponentscatalog_Host();
        $pmComponentscatalog_rule = new PluginMonitoringComponentscatalog_rule();

        $query = "SELECT * FROM `glpi_plugin_monitoring_componentscatalogs_hosts`
         WHERE `plugin_monitoring_componentscatalogs_id`='" . $item->fields["id"] . "'
            AND `is_static`='1'";
        $result = $DB->query($query);
        while ($data = $DB->fetch_array($result)) {
            $pmComponentscatalog_Host->delete($data);
        }

        $query = "SELECT * FROM `glpi_plugin_monitoring_componentscatalogs_rules`
         WHERE `plugin_monitoring_componentscatalogs_id`='" . $item->fields["id"] . "'";
        $result = $DB->query($query);
        while ($data = $DB->fetch_array($result)) {
            $pmComponentscatalog_rule->delete($data);
        }
    }


    function showWidget($id)
    {
        return "<div id=\"updatecomponentscatalog" . $id . "\"></div>";
    }


    function showWidgetFrame($id, $reduced_interface = false, $is_minemap = FALSE)
    {
        global $CFG_GLPI;

        $this->getFromDB($id);
        $data = $this->fields;

        $ret = $this->getInfoOfCatalog($id);
        $nb_ressources = $ret[0];
        if ($nb_ressources == 0) {
            echo '<div class="ch-item">
            <div>
            <h1>' . __('Nothing to display ...', 'monitoring') . '</h1>
            </div>
         </div>';

            return;
        }

        $stateg = $ret[1];
        $hosts_ids = $ret[2];
        $services_ids = $ret[3];
        $hosts_ressources = $ret[4];
        $hosts_states = $ret[5];

        $colorclass = 'ok';
        $count = 0;

        $link = '';
        // PluginMonitoringToolbox::log("stateg $id - ".serialize($stateg)."\n");
        if ($stateg['CRITICAL'] > 0) {
            $count = $stateg['CRITICAL'];
            $colorclass = 'crit';
            $link = $CFG_GLPI['root_doc'] .
                "/plugins/monitoring/front/service.php?hidesearch=1"
//                 . "&reset=reset&"
                . "&criteria[0][field]=3"
                . "&criteria[0][searchtype]=equals"
                . "&criteria[0][value]=CRITICAL"

                . "&criteria[1][link]=AND"
                . "&criteria[1][field]=9"
                . "&criteria[1][searchtype]=equals"
                . "&criteria[1][value]=" . $id

                . "&itemtype=PluginMonitoringService"
                . "&start=0";
        } else if ($stateg['WARNING'] > 0) {
            $count = $stateg['WARNING'];
            $colorclass = 'warn';
            $link = $CFG_GLPI['root_doc'] .
                "/plugins/monitoring/front/service.php?hidesearch=1"
//                 . "&reset=reset"
                . "&criteria[0][field]=3"
                . "&criteria[0][searchtype]=equals"
                . "&criteria[0][value]=WARNING"

                . "&criteria[1][link]=AND"
                . "&criteria[1][field]=9"
                . "&criteria[1][searchtype]=equals"
                . "&criteria[1][value]=" . $id

                . "&criteria[2][link]=OR"
                . "&criteria[2][field]=3"
                . "&criteria[2][searchtype]=equals"
                . "&criteria[2][value]=UNKNOWN"

                . "&criteria[3][link]=AND"
                . "&criteria[3][field]=9"
                . "&criteria[3][searchtype]=equals"
                . "&criteria[3][value]=" . $id

                . "&criteria[4][link]=OR"
                . "&criteria[4][field]=3"
                . "&criteria[4][searchtype]=equals"
                . "&criteria[4][value]=RECOVERY"

                . "&criteria[5][link]=AND"
                . "&criteria[5][field]=9"
                . "&criteria[5][searchtype]=equals"
                . "&criteria[5][value]=" . $id

                . "&criteria[6][link]=OR"
                . "&criteria[6][field]=3"
                . "&criteria[6][searchtype]=equals"
                . "&criteria[6][value]=FLAPPING"

                . "&criteria[7][link]=AND"
                . "&criteria[7][field]=9"
                . "&criteria[7][searchtype]=equals"
                . "&criteria[7][value]=" . $id

                . "&itemtype=PluginMonitoringService"
                . "&start=0";
        } else {
            $count = $stateg['OK'];
            $count += $stateg['ACKNOWLEDGE'];
            $count += $stateg['UNKNOWN'];
            $link = $CFG_GLPI['root_doc'] .
                "/plugins/monitoring/front/service.php?hidesearch=1"
//                 . "&reset=reset"
                . "&criteria[0][field]=3"
                . "&criteria[0][searchtype]=equals"
                . "&criteria[0][value]=OK"

                . "&criteria[1][link]=AND"
                . "&criteria[1][field]=9"
                . "&criteria[1][searchtype]=equals"
                . "&criteria[1][value]=" . $id

                . "&criteria[2][link]=OR"
                . "&criteria[2][field]=3"
                . "&criteria[2][searchtype]=equals"
                . "&criteria[2][value]=UP"

                . "&itemtype=PluginMonitoringService"
                . "&start=0";
        }

        if (Session::haveRight("plugin_monitoring_servicescatalog", PluginMonitoringService::DASHBOARD)) {
            $link_catalog = $CFG_GLPI['root_doc'] .
                "/plugins/monitoring/front/service.php?hidesearch=1"
//                 . "&reset=reset"
                . "&criteria[0][field]=9"
                . "&criteria[0][searchtype]=equals"
                . "&criteria[0][value]=" . $id

                . "&itemtype=PluginMonitoringService"
                . "&start=0";

            echo '<div class="ch-item">
            <div class="ch-info-' . $colorclass . '">
            <h1><a href="' . $link_catalog . '" target="_blank">' . ucfirst($data['name']);
            if ($data['comment'] != '') {
                echo ' ' . $this->getComments();
            }
            echo '</a></h1>
               <p><a href="' . $link . '" target="_blank">' . $count . '</a><font style="font-size: 14px;">/ ' .
                ($stateg['CRITICAL'] + $stateg['WARNING'] + $stateg['OK'] + $stateg['ACKNOWLEDGE'] + $stateg['UNKNOWN']) . '</font></p>
            </div>
         </div>';
        } else {
            echo '<div class="ch-item">
            <div class="ch-info-' . $colorclass . '">
            <h1>' . ucfirst($data['name']);
            if ($data['comment'] != '') {
                echo ' ' . $this->getComments();
            }
            echo '</h1>
               <p>' . $count . '<font style="font-size: 14px;">/ ' .
                ($stateg['CRITICAL'] + $stateg['WARNING'] + $stateg['OK'] + $stateg['ACKNOWLEDGE'] + $stateg['UNKNOWN']) . '</font></p>
            </div>
         </div>';
        }

        // Get services list ...
        $services = [];
        $i = 0;
        foreach ($hosts_ressources as $resources) {
            foreach ($resources as $resource => $status) {
                $services[$i++] = $resource;
            }
            break;
        }
        sort($services);

        echo "<div class='minemapdiv' align='center'>"
            . "<a onclick='$(\"#minemapCC-" . $id . "\").toggle();'>"
            . __('Minemap', 'monitoring') . "</a></div>";
        if (!$is_minemap) {
            echo '<div class="minemapdiv" id="minemapCC-' . $id . '" style="display: none; z-index: 1500">';
        } else {
            echo '<div class="minemapdiv" id="minemapCC-' . $id . '">';
        }

        echo '<table class="tab_cadrehov" >';

        // Header with services name and link to services list ...
        echo "<tr>";
        echo "<th>";
        echo __('Hosts', 'monitoring');
        echo "</th>";
        for ($i = 0; $i < count($services); $i++) {
            // Do not display fake host service ...
            if ($services[$i] == '_fake_') continue;

            if (Session::haveRight("plugin_monitoring_service", READ)) {
                $link = $CFG_GLPI['root_doc'] .
                    "/plugins/monitoring/front/service.php?hidesearch=1"
//                    . "&reset=reset"
                    . "&criteria[0][field]=2"
                    . "&criteria[0][searchtype]=equals"
                    . "&criteria[0][value]=" . $services_ids[$services[$i]]

                    . "&itemtype=PluginMonitoringService"
                    . "&start=0'";
                echo '<th class="vertical">';
                echo '<a href="' . $link . '" target="_blank"><div class="rotated-text"><span class="rotated-text__inner">' . $services[$i] . '</span></div></a>';
                echo '</th>';
            } else {
                echo '<th class="vertical">';
                echo '<div class="rotated-text"><span class="rotated-text__inner">' . $services[$i] . '</span></div>';
                echo '</th>';
            }
        }
        echo '</tr>';

        $pmHost = new PluginMonitoringHost();
        $entityId = -1;
        $overallServicesState = 'OK';
        foreach ($hosts_ressources as $hosts_id => $resources) {
            // Reduced array or not ?
            if ($reduced_interface and $hosts_states[$hosts_id]) continue;

            $pmHost->getFromDB($hosts_ids[$hosts_id]['id']);
            if ($entityId != $pmHost->fields['entities_id']) {
                if ($entityId != -1) {
                    if ($overallServicesState != 'OK') {
                        $overallServicesState = 'OK';
                    }
                }
                // A new sub-table for each entity ...
                $entityId = $pmHost->fields['entities_id'];
                $pmEntity = new Entity();
                $pmEntity->getFromDB($entityId);
                $overallServicesState = 'OK';
                echo "<tr class='header'><th class='left' colspan='" . (count($services)) . "'>" . $pmEntity->fields['name'] . "</th></tr>";
            }
            $field_id = 20;
            if ($hosts_ids[$hosts_id]['itemtype'] == 'Printer') {
                $field_id = 21;
            } else if ($hosts_ids[$hosts_id]['itemtype'] == 'NetworkEquipment') {
                $field_id = 22;
            }

            $link = $CFG_GLPI['root_doc'] .
                "/plugins/monitoring/front/service.php?hidesearch=1"
//                 . "&reset=reset"
                . "&criteria[0][field]=" . $field_id . ""
                . "&criteria[0][searchtype]=equals"
                . "&criteria[0][value]=" . $hosts_ids[$hosts_id]['items_id']

                . "&itemtype=PluginMonitoringService"
                . "&start=0'";

            if ($hosts_states[$hosts_id]) {
                echo "<tr class='services tab_bg_2'>";
            } else {
                echo "<tr class='services tab_bg_3'>";
            }
            // echo "<td><div style='width: 5px !important;'>&nbsp;</div></td>";
            if (Session::haveRight("plugin_monitoring_service", READ)) {
                /* @var $item CommonDBTM */
                $item = new $hosts_ids[$hosts_id]['itemtype'];
                $item->getFromDB($hosts_ids[$hosts_id]['items_id']);
                echo "<td class='left'><a href='" . $link . "' target='_blank'>" . $hosts_ids[$hosts_id]['name'] . "</a> " . $item->getComments() . "</td>";
            } else {
                echo "<td class='left'>" . $hosts_ids[$hosts_id]['name'] . "</td>";
            }
            for ($i = 0; $i < count($services); $i++) {
                if ($services[$i] == '_fake_') continue;

                if ($resources[$services[$i]]['state'] != 'OK') {
                    $overallServicesState = $resources[$services[$i]]['state'];
                }
                echo '<td class="serviceState">';
                if (Session::haveRight("plugin_monitoring_service", READ)) {
                    $link_service = $link;
                    $link_service .= "&link[1]=AND&field[1]=2&searchtype[1]=equals&contains[1]=" .
                        $resources[$services[$i]]['plugin_monitoring_components_id'];
                    echo '<a href="' . $link_service . '" target="_blank">' .
                        '<div title="' . $resources[$services[$i]]['state'] .
                        " - " . $resources[$services[$i]]['last_check'] . " - " .
                        $resources[$services[$i]]['event'] .
                        '" class="service service' . $resources[$services[$i]]['state_type'] . ' service' . $resources[$services[$i]]['state'] . '"></div>' .
                        '</a>';
                } else {
                    echo '<div title="' . $resources[$services[$i]]['state'] .
                        " - " . $resources[$services[$i]]['last_check'] . " - " .
                        $resources[$services[$i]]['event'] .
                        '" class="service service' . $resources[$services[$i]]['state_type'] . ' service' . $resources[$services[$i]]['state'] . '"></div>';
                }
                echo '</td>';
            }
            echo '</tr>';
        }
        echo '</table>';
        echo '</div>';
    }


    function ajaxLoad($id, $is_minemap = false)
    {
        global $CFG_GLPI;

        echo "<script type=\"text/javascript\">
            (function worker() {
              $.get('" . $CFG_GLPI["root_doc"] . "/plugins/monitoring/ajax/updateWidgetComponentscatalog.php"
            . "?id=" . $id . "&is_minemap=" . $is_minemap .
            "', function(data) {
                $('#updatecomponentscatalog" . $id . "').html(data);
                setTimeout(worker, 50000);
              });
            })();
         </script>";
    }


    function getInfoOfCatalog($componentscatalogs_id)
    {
        global $DB;

        $pmComponentscatalog_Host = new PluginMonitoringComponentscatalog_Host();
        $pmService = new PluginMonitoringService();

        $stateg = [];
        $stateg['OK'] = 0;
        $stateg['WARNING'] = 0;
        $stateg['CRITICAL'] = 0;
        $stateg['UNKNOWN'] = 0;
        $stateg['ACKNOWLEDGE'] = 0;
        $a_gstate = [];
        $nb_ressources = 0;
        $hosts_ids = [];
        $hosts_states = [];
        $services_ids = [];
        $hosts_ressources = [];
        $a_componentscatalogs_hosts = [];

        $query = "
         SELECT
            CONCAT_WS('', `glpi_computers`.`name`, `glpi_printers`.`name`, `glpi_networkequipments`.`name`) AS name,
            CONCAT_WS('', `glpi_computers`.`entities_id`, `glpi_printers`.`entities_id`, `glpi_networkequipments`.`entities_id`) AS entities_id,
            `glpi_plugin_monitoring_componentscatalogs_hosts`.`id` AS catalog_id,
            `glpi_plugin_monitoring_hosts`.*
         FROM `glpi_plugin_monitoring_componentscatalogs_hosts`
         LEFT JOIN `glpi_computers`
            ON `glpi_plugin_monitoring_componentscatalogs_hosts`.`items_id` = `glpi_computers`.`id`
               AND `glpi_plugin_monitoring_componentscatalogs_hosts`.`itemtype`='Computer'
         LEFT JOIN `glpi_printers`
            ON `glpi_plugin_monitoring_componentscatalogs_hosts`.`items_id` = `glpi_printers`.`id`
               AND `glpi_plugin_monitoring_componentscatalogs_hosts`.`itemtype`='Printer'
         LEFT JOIN `glpi_networkequipments`
            ON `glpi_plugin_monitoring_componentscatalogs_hosts`.`items_id` = `glpi_networkequipments`.`id`
               AND `glpi_plugin_monitoring_componentscatalogs_hosts`.`itemtype`='NetworkEquipment'

         INNER JOIN `glpi_plugin_monitoring_hosts`
            ON (`glpi_plugin_monitoring_componentscatalogs_hosts`.`items_id` = `glpi_plugin_monitoring_hosts`.`items_id`
            AND `glpi_plugin_monitoring_componentscatalogs_hosts`.`itemtype` = `glpi_plugin_monitoring_hosts`.`itemtype`)
         WHERE `plugin_monitoring_componentscatalogs_id`='" . $componentscatalogs_id . "'
            AND CONCAT_WS('', `glpi_computers`.`entities_id`, `glpi_printers`.`entities_id`, `glpi_networkequipments`.`entities_id`) IN (" . $_SESSION['glpiactiveentities_string'] . ")
         ORDER BY entities_id ASC, name ASC";
        // PluginMonitoringToolbox::log("query : $query\n");

        $result = $DB->query($query);
        while ($dataComponentscatalog_Host = $DB->fetch_array($result)) {
            $ressources = [];
            $fakeService = [];
            $host_overall_state_ok = false;

            // Dummy service id ...
            $fakeService['name'] = '_fake_';
            $fakeService['id'] = $dataComponentscatalog_Host['id'] + 1000000;
            $fakeService['is_acknowledged'] = $dataComponentscatalog_Host['is_acknowledged'];
            $fakeService['last_check'] = $dataComponentscatalog_Host['last_check'];
            $fakeService['event'] = $dataComponentscatalog_Host['event'];
            $fakeService['perf_data'] = $dataComponentscatalog_Host['perf_data'];
            $fakeService['state_type'] = $dataComponentscatalog_Host['state_type'];
            $fakeService['state'] = ($dataComponentscatalog_Host['is_acknowledged'] == '1') ? 'ACKNOWLEDGE' : $dataComponentscatalog_Host['state'];
            $fakeService['state'] = ($dataComponentscatalog_Host['state_type'] == 'HARD') ? $fakeService['state'] : 'UNKNOWN';
            switch ($fakeService['state']) {
                case 'UP':
                    $fakeService['state'] = 'OK';
                    $host_overall_state_ok = true;
                    break;

                case 'DOWN':
                case 'UNREACHABLE':
                    $fakeService['state'] = 'CRITICAL';
                    break;

                case 'DOWNTIME':
                    $fakeService['state'] = 'ACKNOWLEDGE';
                    break;

                case 'WARNING':
                case 'RECOVERY':
                case 'FLAPPING':
                    $fakeService['state'] = 'WARNING';
                    break;

                default:
                    $fakeService['state'] = 'UNKNOWN';
                    break;
            }

            $queryService = "SELECT *, `glpi_plugin_monitoring_services`.`id` as serviceId, `glpi_plugin_monitoring_components`.`name`,
                 `glpi_plugin_monitoring_components`.`description` FROM `" . $pmService->getTable() . "`
            INNER JOIN `glpi_plugin_monitoring_components`
               ON (`plugin_monitoring_components_id` = `glpi_plugin_monitoring_components`.`id`)
            WHERE `plugin_monitoring_componentscatalogs_hosts_id`='" . $dataComponentscatalog_Host['catalog_id'] . "'
               AND `entities_id` IN (" . $_SESSION['glpiactiveentities_string'] . ")
            ORDER BY `glpi_plugin_monitoring_services`.`name` ASC;";
            // PluginMonitoringToolbox::log("query services - $queryService\n");
            $resultService = $DB->query($queryService);
            while ($dataService = $DB->fetch_array($resultService)) {
                $nb_ressources++;

                $pmService->getFromDB($dataService["serviceId"]);

                if ($dataService['is_acknowledged'] == '1') {
                    $dataService['state'] = 'ACKNOWLEDGE';
                }
                // If not hard state, then unknown ...
                if ($dataService['state_type'] != "HARD") {
                    $a_gstate[$dataService['id']] = "UNKNOWN";
                    if ($host_overall_state_ok) $host_overall_state_ok = false;
                } else {
                    // $statecurrent = PluginMonitoringHost::getState($dataService['state'],
                    // $dataService['state_type'],
                    // $dataService['event'],
                    // $dataService['is_acknowledged']);
                    $statecurrent = $pmService->getShortState();
                    if ($statecurrent == 'green') {
                        $a_gstate[$dataService['id']] = "OK";
                    } else if ($statecurrent == 'orange') {
                        $a_gstate[$dataService['id']] = "WARNING";
                        if ($host_overall_state_ok) $host_overall_state_ok = false;
                    } else if ($statecurrent == 'yellow') {
                        $a_gstate[$dataService['id']] = "WARNING";
                        if ($host_overall_state_ok) $host_overall_state_ok = false;
                    } else if ($statecurrent == 'red') {
                        $a_gstate[$dataService['id']] = "CRITICAL";
                        if ($host_overall_state_ok) $host_overall_state_ok = false;
                    } else if ($statecurrent == 'redblue') {
                        $a_gstate[$dataService['id']] = "ACKNOWLEDGE";
                        if ($host_overall_state_ok) $host_overall_state_ok = false;
                    }
                }
                $ressources[$dataService['name']] = $dataService;
                $services_ids[$dataService['name']] = $dataService['plugin_monitoring_components_id'];

                if (isset($dataService['id'])
                    && isset($a_gstate[$dataService['id']])) {
                    $stateg[$a_gstate[$dataService['id']]]++;
                }
            }

            if ($host_overall_state_ok) {
                $fakeService['state'] = 'OK';
            } else {
                $fakeService['state'] = 'CRITICAL';
            }
            $ressources[$fakeService['name']] = $fakeService;
            $services_ids[$fakeService['name']] = '';
            $a_gstate[$fakeService['id']] = $fakeService['state'];
            // $stateg[$a_gstate[$fakeService['id']]]++;

            $hosts_ids[$dataComponentscatalog_Host['id']] = $dataComponentscatalog_Host;
            $hosts_states[$dataComponentscatalog_Host['id']] = $host_overall_state_ok;
            $a_componentscatalogs_hosts[$dataComponentscatalog_Host['catalog_id']] = $dataComponentscatalog_Host['catalog_id'];
            $hosts_ressources[$dataComponentscatalog_Host['id']] = $ressources;
        }

        return [$nb_ressources,
            $stateg,
            $hosts_ids,
            $services_ids,
            $hosts_ressources,
            $hosts_states,
            $a_componentscatalogs_hosts];
    }


    function getRessources($componentscatalogs_id, $state, $state_type = 'HARD')
    {
        global $DB;

        $a_services = [];

        $pmComponentscatalog_Host = new PluginMonitoringComponentscatalog_Host();
        $pmService = new PluginMonitoringService();

        $query = "SELECT * FROM `glpi_plugin_monitoring_services`
         LEFT JOIN `" . $pmComponentscatalog_Host->getTable() . "`
            ON `plugin_monitoring_componentscatalogs_hosts_id`=
               `" . $pmComponentscatalog_Host->getTable() . "`.`id`
         WHERE `plugin_monitoring_componentscatalogs_id`='" . $componentscatalogs_id . "'
            AND `state_type` LIKE '" . $state_type . "'
         ORDER BY `name`";
        $result = $DB->query($query);
        while ($data = $DB->fetch_array($result)) {
            $pmService->getFromDB($data["id"]);
            if ($pmService->getShortState()) {
                // if (PluginMonitoringHost::getState($data['state'],
                // $data['state_type'],
                // '',
                // $data['is_acknowledged']) == $state) {
                $a_services[] = $data;
            }
        }
        return $a_services;
    }
}
