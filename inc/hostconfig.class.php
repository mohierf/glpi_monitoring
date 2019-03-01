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

class PluginMonitoringHostconfig extends CommonDBTM
{
    static $rightname = 'plugin_monitoring_componentscatalog';

    /**
     * Initialization called on plugin installation
     *
     * @param Migration $migration
     */
    function initialize($migration)
    {
        global $DB;

        // Get for the default root entity
        $configs = $this->find("`itemtype`='Entity' AND `items_id`='0'");
        if (count($configs) <= 0) {
            $realm = -1;
            $pmRealm = new PluginMonitoringRealm();
            if ($pmRealm->getFromDBByCrit(["name" => "All"])) {
                $realm = $pmRealm->getID();
            }
            $migration->displayMessage("  default realm: " . $realm);

            $component = -1;
            $pmComponent = new PluginMonitoringComponent();
            if ($pmComponent->getFromDBByCrit(["name" => $DB->escape("Host check (ping)")])) {
                $component = $pmComponent->getID();
            }
            $migration->displayMessage("  host check component: " . $component);

            $input = [];
            $input['itemtype'] = 'Entity';
            $input['items_id'] = 0;
            $input['plugin_monitoring_realms_id'] = $realm;
            $input['plugin_monitoring_components_id'] = $component;
            $this->add($input);
        }
    }


    /**
     * Get name of this type
     *
     * @param int $nb
     *
     * @return string name of this type by language of the user connected
     *
     */
    static function getTypeName($nb = 0)
    {
        return __('Host configuration', 'monitoring');
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
            'name' => __('Hosts configurations', 'monitoring')
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
            'table' => PluginMonitoringComponent::getTable(),
            'field' => 'name',
            'datatype' => 'itemlink',
            'linkfield' => 'plugin_monitoring_components_id',
            'name' => __('Host check component', 'monitoring'),
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
     * This may be called for an entity. Then $itemtype is "Entity"
     *
     * @param $items_id integer ID
     * @param $itemtype
     *
     * @return bool true if form is ok
     *
     */
    function showForm($items_id, $itemtype)
    {
        global $CFG_GLPI;

        PluginMonitoringToolbox::logIfDebug("PluginMonitoringHostconfig::showForm for: $itemtype, $items_id");

        $entity_view = ($itemtype == "Entity");

        if ($entity_view) {
            $entities_id = $items_id;
        } else {
            /* @var CommonDBTM $item */
            $item = new $itemtype();
            $item->getFromDB($items_id);
            $entities_id = $item->fields['entities_id'];
        }
        $inherited = ($entities_id != '0' OR $itemtype != 'Entity');
        PluginMonitoringToolbox::logIfDebug("PluginMonitoringHostconfig::showForm, entities_id: $entities_id");

        $found = $this->find("`itemtype`='$itemtype' AND `items_id`='$items_id'");
        if (count($found) <= 0) {
            $this->getEmpty();
            if ($inherited) {
                $this->fields['plugin_monitoring_components_id'] = -1;
                $this->fields['plugin_monitoring_realms_id'] = -1;
            }
        } else {
            $found = current($found);
            PluginMonitoringToolbox::logIfDebug("PluginMonitoringHostconfig::showForm, found $itemtype $items_id: " . print_r($found, true));
            $this->getFromDB($found['id']);
        }

        echo "<form name='form' method='post'
         action='" . $CFG_GLPI['root_doc'] . "/plugins/monitoring/front/hostconfig.form.php'>";

        echo "<table class='tab_cadre_fixe'";

        echo "<tr class='tab_bg_1'>";
        echo "<th colspan='4'>";
        echo __('Hosts configuration', 'monitoring');
        echo "</th>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>";
        echo __('Host check component', 'monitoring') . "&nbsp;:";
        echo "</td>";
        echo "<td>";
        $toadd = [];
        if ($inherited) {
            $toadd["-1"] = __('Inheritance of the parent entity');
        }
        Dropdown::show('PluginMonitoringComponent',
            [
                'name' => 'plugin_monitoring_components_id',
                'value' => $this->fields['plugin_monitoring_components_id'],
                'toadd' => $toadd,
                'display_emptychoice' => true
            ]);
        echo "</td>";

        echo "<td>" . __('Realm', 'monitoring') . "&nbsp;:</td>";
        echo "<td>";
        $toadd = [];
        if ($inherited) {
            $toadd["-1"] = __('Inheritance of the parent entity');
        }
        Dropdown::show('PluginMonitoringRealm',
            [
                'name' => 'plugin_monitoring_realms_id',
                'value' => $this->fields['plugin_monitoring_realms_id'],
                'toadd' => $toadd,
                'display_emptychoice' => false
            ]);
        echo "</td>";
        echo "</tr>";

        // Inheritance
        if ($this->fields['plugin_monitoring_components_id'] == '-1' or
            $this->fields['plugin_monitoring_realms_id'] == '-1') {
            echo "<tr class='tab_bg_1'>";
            if ($this->fields['plugin_monitoring_components_id'] == '-1') {
                echo "<td colspan='2' class='green center'>";
                echo __('Inheritance of the parent entity') . "&nbsp;:&nbsp;";
                $value = $this->getValueAncestor("plugin_monitoring_components_id", $entities_id);
                if ($value != "n/a") {
                    $pmComponent = new PluginMonitoringComponent();
                    $pmComponent->getFromDB($value);
                    echo $pmComponent->getLink();
                } else {
                    echo "<span class='red'>";
                    echo __('Inherited value not set!') . "&nbsp;:&nbsp;";
                    echo '</span>';
                }
                echo "</td>";
            } else {
                echo "<td colspan='2'>";
                echo "</td>";
            }

            if ($this->fields['plugin_monitoring_realms_id'] == '-1') {
                echo "<td colspan='2' class='green center'>";
                echo __('Inheritance of the parent entity') . "&nbsp;:&nbsp;";
                $value = $this->getValueAncestor("plugin_monitoring_realms_id", $entities_id);
                if ($value != "n/a") {
                    $pmRealm = new PluginMonitoringRealm();
                    $pmRealm->getFromDB($value);
                    echo $pmRealm->getLink();
                } else {
                    echo "<span class='red'>";
                    echo __('Inherited value not set!') . "&nbsp;:&nbsp;";
                    echo '</span>';
                }
                echo "</td>";
            } else {
                echo "<td colspan='2'>";
                echo "</td>";
            }
            echo "</tr>";
        }


        // Only for the root entoty
        // todo: why?
        if ($itemtype == 'Entity' AND $items_id == '0') {
            echo "<tr class='tab_bg_1'>";
            echo "<td>";
            echo __('Monitoring Server', 'monitoring') . "&nbsp;:";
            echo "</td>";
            echo "<td>";
            $toadd = [];
            if ($inherited) {
                $toadd["-1"] = __('Inheritance of the parent entity');
            }
            Dropdown::show("Computer", [
                'name' => 'computers_id',
                'value' => $this->fields['computers_id'],
                'toadd' => $toadd,
                'display_emptychoice' => FALSE
            ]);
            echo "</td>";
            echo "<td colspan='2'></td>";
            echo "</tr>";
        }

        if ($this->canCreate()) {
            echo "<tr class='tab_bg_1'>";
            echo "<td colspan='4' align='center'>";
            if (isset($this->fields['id']) AND !empty($this->fields['id'])) {
                echo "<input type='hidden' name='id' value='" . $this->fields['id'] . "'/>";
            }
            echo "<input type='hidden' name='itemtype' value='" . $itemtype . "'/>";
            echo "<input type='hidden' name='items_id' value='" . $items_id . "'/>";
            echo "<input type='submit' name='update' value=\"" . __('Save') . "\" class='submit'>";
            echo "</td>";
            echo "</tr>";
        }

        echo "</table>";
        Html::closeForm();

        return true;
    }


    function getValueAncestor($fieldname, $entities_id, $itemtype = '', $items_id = '')
    {
        PluginMonitoringToolbox::logIfDebug("PluginMonitoringHostconfig::getValueAncestor: $fieldname, $entities_id, $itemtype, $items_id ");

        if (!empty($itemtype) AND !empty($items_id)) {
            // Get for a specific item
            $configs = $this->find("`itemtype`='$itemtype' AND `items_id`='$items_id'");
            if (count($configs) > 0) {
                $data = current($configs);
                if ($data[$fieldname] != '-1') {
                    return $data[$fieldname];
                }
            }
            // Not found for a specifc item... go further for an entity!
        }

        PluginMonitoringToolbox::logIfDebug("getValueAncestor: for an entity...");
        if ($entities_id == -1) {
            // If entity is unset, consider the root entity
            $entities_id = 0;
        }
        // Get for an entity
        $configs = $this->find("`itemtype`='Entity' AND `items_id`='$entities_id'");
        if (count($configs) <= 0) {
            // Not found, search in entity ancestors
            $entities_ancestors = getAncestorsOf("glpi_entities", $entities_id);
            $nbentities = count($entities_ancestors);
            for ($i = 0; $i < $nbentities; $i++) {
                $entity_id = array_pop($entities_ancestors);
                PluginMonitoringToolbox::logIfDebug("- ancestor: $entity_id");

                $configs = $this->find("`itemtype`='Entity' AND `items_id`='$entity_id'");
                if (count($configs) > 0) {
                    // Found!
                    $data = current($configs);
                    PluginMonitoringToolbox::logIfDebug("-> found: " . print_r($data, true));
                    if ($data[$fieldname] != '-1') {
                        return $data[$fieldname];
                    }
                }
            }
        } else {
            // Found!
            $data = current($configs);
            // Fix #168 ...
            if ($data[$fieldname] != '-1') {
                return $data[$fieldname];
            } else {
                $entities_ancestors = getAncestorsOf("glpi_entities", $entities_id);
                $nbentities = count($entities_ancestors);
                for ($i = 0; $i < $nbentities; $i++) {
                    $entity_id = array_pop($entities_ancestors);
                    PluginMonitoringToolbox::logIfDebug("- ancestor2: $entity_id");
                    $configs = $this->find("`itemtype`='Entity' AND `items_id`='$entity_id'");
                    if (count($configs) > 0) {
                        // Found!
                        $data = current($configs);
                        return $data[$fieldname];
                    }
                }
            }
        }

        return "n/a";
    }
}