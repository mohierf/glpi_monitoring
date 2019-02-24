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

class PluginMonitoringEntity extends CommonDBTM
{
    static $rightname = 'entity';

    /**
     * Initialization called on plugin installation
     * @param Migration $migration
     */
    function initialize($migration)
    {
        $check_period = -1;
        $calendar = new Calendar();
        if ($calendar->getFromDBByCrit(['name' => "monitoring-default"])) {
            $check_period = $calendar->getID();
        }

        // Get for the default root entity
        $configs = $this->find("`entities_id`='0'");
        if (count($configs) <= 0) {
            $input = [];
            $input['entities_id'] = 0;
            $input['tag'] = 'All';
            $input['definition_order'] = 100;
            $input['graphite_prefix'] = '';
            $input['jet_lag'] = 0;
            $input['calendars_id'] = $check_period;
            $this->add($input);
            $migration->displayMessage("  created a default entity configuration");
        }
    }


    static function getTypeName($nb = 0)
    {
        return __('Entity', 'monitoring');
    }


    function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        $array_ret = array();
        if ($item->getID() > -1) {
            if (Session::haveRight("entity", READ)) {
                $array_ret[0] = self::createTabEntry(__('Monitoring', 'monitoring'));
            }
        }
        return $array_ret;
    }


    static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        if ($item->getID() > -1) {
            // Show the host configuration form
            $pmHostconfig = new PluginMonitoringHostconfig();
            $pmHostconfig->showForm($item->getID(), "Entity");

            // Show the entity specific configuration form
            $pmEntity = new self();
            $pmEntity->showForm($item->getID());
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

//        $tab[] = [
//            'id' => $index++,
//            'table' => $this->getTable(),
//            'field' => 'active_checks_enabled',
//            'datatype' => 'bool',
//            'name' => __('Active check', 'monitoring'),
//        ];
//
//        $tab[] = [
//            'id' => $index++,
//            'table' => $this->getTable(),
//            'field' => 'passive_checks_enabled',
//            'datatype' => 'bool',
//            'name' => __('Passive check', 'monitoring'),
//        ];
//
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


    function showForm($items_id, $options = array())
    {
        global $CFG_GLPI;

        PluginMonitoringToolbox::logIfDebug("PluginMonitoringEntity::showForm, items_id: $items_id");
        $entities_id = $items_id;
        $exists = true;
        if (!$this->getFromDBByCrit(['entities_id' => $items_id])) {
            // New entry...
            $exists = false;
            $pmEntity = self::getForEntity($entities_id);
            if ($pmEntity) {
                $this->fields = $pmEntity->fields;
            } else {
                $this->getEmpty();
            }
            $this->fields['entities_id'] = $entities_id;
            if (isset($this->fields['id'])) {
                unset($this->fields['id']);
            }
        }
        PluginMonitoringToolbox::logIfDebug("PluginMonitoringEntity::showForm, fields: " . print_r($this->fields, true));

        echo "<form name='form' method='post'
         action='" . $CFG_GLPI['root_doc'] . "/plugins/monitoring/front/entity.form.php'>";

        echo "<table class='tab_cadre_fixe'";

        // Inheritance
        if (!$exists) {
            echo "<tr class='tab_bg_1'>";
            echo "<th colspan='4' class='green center' style='color:green'>";
            echo __('All values are inherited from a parent entity');
            echo "</th>";
            echo "</tr>";
        }

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Tag', 'monitoring') . " :</td>";
        echo "<td>";
        echo "<input type='text' name='tag' value='" . $this->fields["tag"] . "' size='30'/>";

        echo "</td>";
        echo "<td>" . __('Definition order', 'monitoring') . " :</td>";
        echo "<td>";
        Dropdown::showNumber('definition_order', [
                'value' => $this->fields['definition_order'],
                'min' => 0,
                'max' => 200,
                'step' => 10
            ]
        );
        echo "&nbsp;" . __('The lower for the highest priority', 'monitoring');
        echo "</td>";
        echo "</tr>";
        echo "<tr class='tab_bg_1'>";
        echo "<td colspan='2'><em>";
        echo __('Set tag to link entity witd a specific monitoring server', 'monitoring');
        echo "</em></td>";
        echo "<td colspan='2'><em>";
        echo __('Set the definition order for the entity related objects', 'monitoring');
        echo "</em></td>";
        echo "</tr>";


        echo "<tr class='tab_bg_1'>";
        echo "<td>";
        echo __('Graphite prefix', 'monitoring') . "&nbsp;:";
        echo "</td>";
        echo "<td>";
        echo "<input type='text' name='graphite_prefix' value='" . $this->fields["graphite_prefix"] . "' size='30'/>";
        echo "</td>";
        // * calendar
        echo "<td>" . __('Check period', 'monitoring') . "<span class='red'>*</span>&nbsp;:</td>";
        echo "<td>";
        Dropdown::show("Calendar", ['name' => 'calendars_id', 'value' => $this->fields['calendars_id']]);
        echo "</td>";
        echo "</tr>";
        echo "<tr class='tab_bg_1'>";
        echo "<td colspan='2'>";
        echo __('Set a prefix to be used for the Graphite metrics of the hosts of this entity', 'monitoring');
        echo "</td>";
        echo "<td colspan='2'>";
        echo __('Choose a check period for the hosts of this entity', 'monitoring');
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
        echo "</tr>";
        echo "<tr class='tab_bg_1'>";
        echo "<td colspan='2'><em>";
        echo __('Set the business impact level of the hosts of this entity', 'monitoring');
        echo "</em></td>";
        echo "<td colspan='2'><em>";
        echo __('Set the freshness check behavior for the hosts of this entity', 'monitoring');
        echo "</em></td>";
        echo "</tr>";



        echo "<tr class='tab_bg_1'>";
        echo "<td>";
        echo __('Jet lag', 'monitoring') . "&nbsp;:";
        echo "</td>";
        echo "<td>";

        $elements = [
            '-11' => '-11',
            '-10' => '-10',
            '-9' => '-9',
            '-8' => '-8',
            '-7' => '-7',
            '-6' => '-6',
            '-5' => '-5',
            '-4' => '-4',
            '-3' => '-3',
            '-2' => '-2',
            '-1' => '-1',
            '0' => '0',
            '1' => '+1',
            '2' => '+2',
            '3' => '+3',
            '4' => '+4',
            '5' => '+5',
            '6' => '+6',
            '7' => '+7',
            '8' => '+8',
            '9' => '+9',
            '10' => '+10',
            '11' => '+11',
            '12' => '+12',
            '13' => '+13',
            '14' => '+14',
        ];
        if (!$exists) {
            $elements["100"] = __('Inheritance of the parent entity');
        }
        Dropdown::showFromArray('jet_lag', $elements, ['value' => $this->fields['jet_lag']]);
        echo "</td>";
        echo "<td colspan='2'></td>";
        echo "</tr>";
        echo "<tr class='tab_bg_1'>";
        echo "<td colspan='2'><em>";
        echo __('Define the jet lag for this entity. This will be used to update the calendar of the entity.', 'monitoring');
        echo "</em></td>";
        echo "<td colspan='2'>";
        echo "</td>";
        echo "</tr>";


        echo "<tr class='tab_bg_1'>";
        echo "<td colspan='4' align='center'>";
        if (isset($this->fields['id'])) {
            echo Html::hidden('id', ['value' => $this->fields['id']]);
        }
        echo Html::hidden('entities_id', ['value' => $this->fields['entities_id']]);
        if (!$exists) {
            echo "<input type='submit' name='add' value=\"" . __('Save') . "\" class='submit'>";
        } else {
            echo "<input type='submit' name='update' value=\"" . __('Save') . "\" class='submit'>";
        }
        echo "</td>";
        echo "</tr>";

        echo "</table>";
        Html::closeForm();

        return true;
    }


    /**
     * Get the Alignak entity configuration for the provided entity.
     * If not found in the provided entity, have a look in this entity ancestors
     * @param $entities_id
     *
     * @return PluginMonitoringEntity
     */
    static function getForEntity($entities_id) {
        $dbu = new DbUtils();

        PluginMonitoringToolbox::logIfDebug("Get Alignak entity configuration for : ". $entities_id);
        $pmEntity = new self();
        if ($pmEntity->getFromDBByCrit(['entities_id' => $entities_id])) {
            // Found!
            PluginMonitoringToolbox::logIfDebug("Found!");
        } else {
            $ancestors = array_reverse($dbu->getAncestorsOf('glpi_entities', $entities_id));
            PluginMonitoringToolbox::logIfDebug("Entity ancestors: " . serialize($ancestors));
            foreach ($ancestors as $index => $id) {
                if ($pmEntity->getFromDBByCrit(['entities_id' => $id])) {
                    PluginMonitoringToolbox::logIfDebug("Found for ancestor: ". $id);
                    break;
                }
            }
        }
        if (empty($pmEntity->fields)) {
            return null;
        }

        return $pmEntity;
    }

    
    /**
     * If the provided tag is not empty and not found then an empty array is returned.
     * If the provided tag is empty, then we consider the root entity.
     * @param string $tag
     *
     * If tag is not provided, this function will return all the available monitoring tags of the declared entities
     *
     * @return array
     */
    function getMonitoredEntities($tag = '')
    {
        PluginMonitoringToolbox::log("getMonitoredEntities, for tag='$tag'");

        // Get entities matching the provided tag
        $result = [];
        $entities = empty($tag) ? $this->find("`tag`!=''") : $this->find("`tag`='$tag'");
        foreach ($entities as $pm_entity) {
            PluginMonitoringToolbox::log("- " . print_r($pm_entity, true));
            $result[] = $pm_entity['tag'];
        }
        $result = array_unique($result);

        PluginMonitoringToolbox::log("getMonitoredEntities, for tag='$tag', entities: " . print_r($result, true));
        return $result;
    }


    /**
     * If the provided tag is not empty and not found then an empty array is returned.
     * If the provided tag is empty, then we consider the root entity.
     * @param string $tag
     * @param bool $sons, to get sons of the required entity
     * @param bool $names, true to return the entity name else it will return the entity identifier
     *
     * If tag is not provided, this function will return all the available monitoring tags of the declared entities
     *
     * @return array
     */
    function getEntitiesByTag($tag = '', $sons=false, $names=false)
    {
        $entity = new Entity();

        PluginMonitoringToolbox::logIfDebug("getEntitiesByTag, for tag='$tag' (sons=$sons) (names=$names)");

        // Get entities matching the provided tag
        $result = [];
        $entities = empty($tag) ? [['entities_id' => 0]] : $this->find("`tag`='$tag'");
        foreach ($entities as $pm_entity) {
            if ($sons) {
                foreach (getSonsOf("glpi_entities", $pm_entity['entities_id']) as $entity_id) {
                    if ($names) {
                        $entity->getFromDB($entity_id);
                        $result[] = $entity->getName();
                    } else {
                        $result[] = $entity_id;
                    }
                }
            } else {
                if ($names) {
                    $entity->getFromDB($pm_entity['entities_id']);
                    $result[] = $entity->getName();
                } else {
                    $result[] = $pm_entity['entities_id'];
                }
            }
        }

        PluginMonitoringToolbox::logIfDebug("getEntitiesByTag, for tag='$tag' (sons=$sons), entities: " . print_r($result, true));
        return array_unique($result);
    }


    static function getTagByEntities($entities_id)
    {
        // Get entities matching the provided identifier
        $pmEntity = new self();
        $entities = $pmEntity->find("`entities_id`='$entities_id'");
        while ($data = $entities) {
            return $data['tag'];
        }

        return "";
    }
}