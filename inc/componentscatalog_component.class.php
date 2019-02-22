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

class PluginMonitoringComponentscatalog_Component extends CommonDBTM
{

    static $rightname = 'plugin_monitoring_componentscatalog';

    static function getTypeName($nb = 0)
    {
        return __('Component', 'monitoring');
    }


    /**
     * Show the list of components linked to the provided components catalog
     *
     * @param $componentscatalogs_id
     */
    function showComponents($componentscatalogs_id)
    {
        global $DB, $PM_CONFIG;

        $can_edit = $this->canUpdate();

        if ($can_edit) {
            // Display the component adding section
            $this->addComponent($componentscatalogs_id);
        }

        $pmComponent = new PluginMonitoringComponent();
        $pmCommand = new PluginMonitoringCommand();
        $pmCheck = new PluginMonitoringCheck();
        $calendar = new Calendar();

        $rand = mt_rand();

        echo '<table class="tab_cadre_fixe">';
        echo '<tr>';
        echo '<th>';
        echo __('Associated components', 'monitoring');
        echo '</th>';
        echo '</tr>';
        echo '</table>';

        // Still used components
        $a_list = $this->find("`plugin_monitoring_componentscatalogs_id`='" . $componentscatalogs_id . "'");
        if (empty($a_list)) {
            echo __('No components are yet associated to this catalog.', 'monitoring');
            return;
        }

        if ($can_edit) {
            Html::openMassiveActionsForm('mass' . __CLASS__ . $rand);
            $massiveactionparams = [
                'container' => 'mass' . __CLASS__ . $rand,
                'specific_actions' => ['purge' => _x('button', 'Unlink')],
            ];
            Html::showMassiveActions($massiveactionparams);
        }

        echo '<table class="tab_cadre_fixe">';
        echo '<tr>';
        echo "<th width='10'>" . ($can_edit ? Html::getCheckAllAsCheckbox('mass' . __CLASS__ . $rand) : "&nbsp;") . "</th>";
        echo '<th>' . __('Name') . '</th>';
        echo '<th>' . __('Command name', 'monitoring') . '</th>';
        echo '<th>' . __('Check strategy', 'monitoring') . '</th>';
        echo '<th>' . __('Check period', 'monitoring') . '</th>';
        echo '</tr>';

        foreach ($a_list as $data) {
            $pmComponent->getFromDB($data['plugin_monitoring_components_id']);
            echo '<tr>';

            echo "<td width='10'>";
            if ($can_edit) {
                Html::showMassiveActionCheckBox('PluginMonitoringComponentscatalog_Component', $data["id"]);
            }
            echo "</td>";

            echo '<td class="center">';
            echo $pmComponent->getLink(["comments" => true]);
            echo '</td>';

            echo '<td class="center">';
            $pmCommand->getFromDB($pmComponent->fields['plugin_monitoring_commands_id']);
            echo $pmCommand->getLink();
            echo '</td>';

            echo '<td class="center">';
            $pmCheck->getFromDB($pmComponent->fields['plugin_monitoring_checks_id']);
            echo $pmCheck->getLink();
            echo '</td>';

            echo '<td class="center">';
            $calendar->getFromDB($pmComponent->fields['calendars_id']);
            echo $calendar->getLink();
            echo '</td>';

            echo '</tr>';
        }

        if ($can_edit) {
            $massiveactionparams = [
                'container' => 'mass' . __CLASS__ . $rand,
                'specific_actions' => ['purge' => _x('button', 'Unlink')],
            ];
            Html::showMassiveActions($massiveactionparams);
        }
        Html::closeForm();
        echo '</table>';
    }


    function addComponent($componentscatalogs_id)
    {
        global $PM_CONFIG;

        if (!Session::haveRight("plugin_monitoring_componentscatalog", UPDATE)) {
            return;
        }

        $this->getEmpty();

        $this->showFormHeader();

        // Still related components
        $used = [];
        $a_list = $this->find("`plugin_monitoring_componentscatalogs_id`='" . $componentscatalogs_id . "'");
        foreach ($a_list as $data) {
            $used[] = $data['plugin_monitoring_components_id'];
        }

        echo '<tr>';
        echo '<td colspan="2">';
        echo __('Add a new component', 'monitoring') . "&nbsp;:";
        echo '<input type="hidden" name="plugin_monitoring_componentscatalogs_id" value="' . $componentscatalogs_id . "'/>";
        echo '</td>';
        echo '<td colspan="2">';
        Dropdown::show("PluginMonitoringComponent", ['name' => 'plugin_monitoring_components_id', 'used' => $used]);
        echo '</td>';
        echo '</tr>';

        $this->showFormButtons();
    }


    function addComponentToItems($componentscatalogs_id, $components_id)
    {
        global $DB;

        $pmService = new PluginMonitoringService();
        $pmComponentscatalog_rule = new PluginMonitoringComponentscatalog_rule();
        $pmNetworkport = new PluginMonitoringNetworkport();

        $pluginMonitoringNetworkport = 0;
        $query = "SELECT * FROM `" . $pmComponentscatalog_rule->getTable() . "` 
        WHERE `itemtype`='PluginMonitoringNetworkport' 
        AND `plugin_monitoring_componentscatalogs_id`='" . $componentscatalogs_id . "' 
        LIMIT 1";

        $result = $DB->query($query);
        if ($DB->numrows($result) > 0) {
            $pluginMonitoringNetworkport = 1;
        }

        $query = "SELECT * FROM `glpi_plugin_monitoring_componentscatalogs_hosts` 
        WHERE `plugin_monitoring_componentscatalogs_id`='" . $componentscatalogs_id . "'";

        $result = $DB->query($query);
        while ($data = $DB->fetch_array($result)) {
            /* @var CommonDBTM $item */
            $itemtype = $data['itemtype'];
            $item = new $itemtype();
            $item->getFromDB($data['items_id']);
            if ($pluginMonitoringNetworkport == '0') {
                $input = [];
                $input['entities_id'] = $item->fields['entities_id'];
                $input['plugin_monitoring_componentscatalogs_hosts_id'] = $data['id'];
                $input['plugin_monitoring_components_id'] = $components_id;
                $input['name'] = Dropdown::getDropdownName("glpi_plugin_monitoring_components", $components_id);
                // TODO: initial states
                $input['state'] = 'WARNING';
                $input['state_type'] = 'HARD';
                $pmService->add($input);
            } else if ($pluginMonitoringNetworkport == '1') {
                $a_services_created = [];
                $querys = "SELECT * FROM `glpi_plugin_monitoring_services`
                WHERE `plugin_monitoring_components_id`='" . $components_id . "'
                AND `plugin_monitoring_componentscatalogs_hosts_id`='" . $data['id'] . "'";

                $results = $DB->query($querys);
                while ($datas = $DB->fetch_array($results)) {
                    $a_services_created[$datas['networkports_id']] = $datas['id'];
                }

                $a_ports = $pmNetworkport->find("`itemtype`='" . $data['itemtype'] . "' 
                AND `items_id`='" . $data['items_id'] . "'");
                foreach ($a_ports as $datap) {
                    if (isset($a_services_created[$datap["id"]])) {
                        unset($a_services_created[$datap["id"]]);
                    } else {
                        $input = [];
                        $input['networkports_id'] = $datap['networkports_id'];
                        $input['entities_id'] = $item->fields['entities_id'];
                        $input['plugin_monitoring_componentscatalogs_hosts_id'] = $data['id'];
                        $input['plugin_monitoring_components_id'] = $components_id;
                        $input['name'] = Dropdown::getDropdownName("glpi_plugin_monitoring_components", $components_id);
                        // TODO: initial states
                        $input['state'] = 'WARNING';
                        $input['state_type'] = 'HARD';
                        $pmService->add($input);
                    }
                }
                foreach ($a_services_created as $id) {
                    $_SESSION['plugin_monitoring_cc_host'] = $data;
                    $pmService->delete(['id' => $id]);
                }
            }
        }
    }


    function removeComponentToItems($componentscatalogs_id, $components_id)
    {
        global $DB;

        $pmService = new PluginMonitoringService();

        $query = "SELECT * FROM `glpi_plugin_monitoring_componentscatalogs_hosts`
         WHERE `plugin_monitoring_componentscatalogs_id`='" . $componentscatalogs_id . "'";
        $result = $DB->query($query);
        while ($data = $DB->fetch_array($result)) {
            $querys = "SELECT * FROM `glpi_plugin_monitoring_services`
            WHERE `plugin_monitoring_componentscatalogs_hosts_id`='" . $data['id'] . "'
               AND `plugin_monitoring_components_id`='" . $components_id . "'";
            $results = $DB->query($querys);
            while ($datas = $DB->fetch_array($results)) {
                $_SESSION['plugin_monitoring_cc_host'] = $data;
                $pmService->delete(['id' => $datas['id']]);
            }
        }
    }


    static function listForComponents($components_id)
    {
        global $DB;

        $pmComponentscatalog = new PluginMonitoringComponentscatalog();

        echo "<table class='tab_cadre' width='400'>";

        echo '<tr class="tab_bg_1">';
        echo '<th>' . __('Components catalog', 'monitoring') . '</th>';
        echo '</tr>';

        $query = "SELECT `glpi_plugin_monitoring_componentscatalogs`.* FROM `glpi_plugin_monitoring_componentscatalogs_components`
         LEFT JOIN `glpi_plugin_monitoring_componentscatalogs`
            ON `plugin_monitoring_componentscatalogs_id` =
               `glpi_plugin_monitoring_componentscatalogs`.`id`
         WHERE `plugin_monitoring_components_id`='" . $components_id . "'
         ORDER BY `glpi_plugin_monitoring_componentscatalogs`.`name`";
        $result = $DB->query($query);
        while ($data = $DB->fetch_array($result)) {
            echo '<tr class="tab_bg_1">';
            echo '<td>';
            $pmComponentscatalog->getFromDB($data['id']);
            echo $pmComponentscatalog->getLink(1);
            echo '</td>';
            echo '</tr>';
        }
        echo '</table>';
    }
}