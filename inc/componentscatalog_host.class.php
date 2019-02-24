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

class PluginMonitoringComponentscatalog_Host extends CommonDBTM
{

    static $rightname = 'plugin_monitoring_componentscatalog';

    static function getTypeName($nb = 0)
    {
        return __('CC Host', 'monitoring');
    }


    /**
     * Show the list of hosts linked to the provided components catalog
     *
     * @param integer $componentscatalogs_id
     * @param boolean $static       true for statically related hosts
     */
    function showHosts($componentscatalogs_id, $static)
    {
        global $DB;

        $can_edit = $static and $this->canUpdate();

        if ($can_edit) {
            // Display the hosts adding section
            $this->relatedComputers($componentscatalogs_id, $static);
        }

        $rand = mt_rand();

        echo '<table class="tab_cadre_fixe">';
        echo '<tr>';
        echo '<th>';
        echo __('Associated hosts', 'monitoring');
        echo '</th>';
        echo '</tr>';
        echo '</table>';

        // Still related hosts
        $a_list = $this->find("`plugin_monitoring_componentscatalogs_id`='".$componentscatalogs_id."' AND `is_static`='". ($static ? '1':'0') ."'");
        if (empty($a_list)) {
            echo __('No hosts are yet associated to this catalog.', 'monitoring');
            return;
        }

        if ($can_edit) {
            Html::openMassiveActionsForm('mass'.__CLASS__.$rand);
            $massiveactionparams = [
                'container' => 'mass' . __CLASS__ . $rand,
                'specific_actions' => ['purge' => _x('button', 'Unlink')],
            ];
            Html::showMassiveActions($massiveactionparams);
        }

        echo "<table class='tab_cadre_fixe'>";
        echo "<tr>";
        echo "<th width='10'>" . ($this->canUpdate() ? Html::getCheckAllAsCheckbox('mass'.__CLASS__.$rand) : "&nbsp;") . "</th>";
        echo "<th>" . __('Type') . "</th>";
        echo "<th>" . __('Entity') . "</th>";
        echo "<th>" . __('Name') . "</th>";
        echo "<th>" . __('Serial number') . "</th>";
        echo "<th>" . __('Inventory number') . "</th>";
        echo "</tr>";

        foreach ($a_list as $data) {
            $used_hosts[] = $data['plugin_monitoring_hosts_id'];

            /* @var CommonDBTM $item */
            $itemtype = $data['itemtype'];
            $item = new $itemtype();

            $display_normal = true;
            $networkports = false;
            if ($itemtype == 'NetworkEquipment') {
                $querys = "SELECT * FROM `glpi_plugin_monitoring_services`
               WHERE `plugin_monitoring_componentscatalogs_hosts_id`='" . $data['id'] . "'
                  AND `networkports_id`='0'";
                $results = $DB->query($querys);
                if ($DB->numrows($results) == 0) {
                    $display_normal = false;
                }

                $querys = "SELECT * FROM `glpi_plugin_monitoring_services`
               WHERE `plugin_monitoring_componentscatalogs_hosts_id`='" . $data['id'] . "'
                  AND `networkports_id`!='0'";
                $results = $DB->query($querys);
                if ($DB->numrows($results) > 0) {
                    $networkports = true;
                }
            }
            $item->getFromDB($data['items_id']);
            if ($display_normal) {
                echo "<tr>";

                echo "<td width='10'>";
                if ($can_edit) {
                    Html::showMassiveActionCheckBox('PluginMonitoringComponentscatalog_Host', $data["id"]);
                }
                echo "</td>";
                echo "<td class='center'>";
                echo $item->getTypeName();
                echo "</td>";
                echo "<td class='center'>";
                echo Dropdown::getDropdownName("glpi_entities", $item->fields['entities_id']) . "</td>";
                echo "<td class='center" .
                    (isset($item->fields['is_deleted']) && $item->fields['is_deleted'] ? " tab_bg_2_2'" : "'");
                echo ">" . $item->getLink() . "</td>";
                echo "<td class='center'>" .
                    (isset($item->fields["serial"]) ? "" . $item->fields["serial"] . "" : "-") . "</td>";
                echo "<td class='center'>" .
                    (isset($item->fields["otherserial"]) ? "" . $item->fields["otherserial"] . "" : "-") . "</td>";

                echo "</tr>";
            }

            if ($networkports) {
                $itemport = new NetworkPort();
                while ($datas = $DB->fetch_array($results)) {
                    $itemport->getFromDB($datas['networkports_id']);
                    echo "<tr>";
                    echo "<td width='10'>";
                    if ($can_edit) {
                        Html::showMassiveActionCheckBox('PluginMonitoringComponentscatalog_Host', $data["id"]);
                    }
                    echo "<td class='center'>";
                    echo $itemport->getTypeName();
                    echo "</td>";
                    echo "<td class='center'>";
                    echo Dropdown::getDropdownName("glpi_entities", $item->fields['entities_id']) . "</td>";
                    echo "<td colspan='3' class='left" .
                        (isset($item->fields['is_deleted']) && $item->fields['is_deleted'] ? " tab_bg_2_2'" : "'");
                    echo ">" . $itemport->getLink() . " on " . $item->getLink(1) . "</td>";
                    echo "</tr>";
                }
            }
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


    function relatedComputers($componentscatalogs_id, $static)
    {
        $this->getEmpty();
        $this->showFormHeader();

        // Yet related hosts (computers)
        $used = [];
        $a_list = $this->find("`plugin_monitoring_componentscatalogs_id`='".$componentscatalogs_id."' AND `is_static`='". $static ? '1':'0' ."'");
        foreach ($a_list as $data) {
            $used[] = $data['plugin_monitoring_hosts_id'];
        }

        echo '<tr>';
        echo '<td colspan="2">';
        echo __('Add a new host', 'monitoring')."&nbsp;:";
        echo '<input type="hidden" name="plugin_monitoring_componentscatalogs_id" value="'.$componentscatalogs_id."'/>";
        echo '<input type="hidden" name="is_static" value="1"/>';
        echo '<input type="hidden" name="itemtype" value="Computer"/>';
        echo '</td>';
        echo '<td colspan="2">';
        Dropdown::show("Computer", array('name'=>'items_id', 'used'=>$used));
        echo '</td>';
        echo '</tr>';

        $this->showFormButtons();
    }


    /**
     * add / update templates for the host in the backend with result of the rules
     *
     * @param integer $componentscatalogs_id
     * @param integer $componentscatalogs_hosts_id
     * @param integer $networkports_id
     */
    function linkComponents($componentscatalogs_id, $componentscatalogs_hosts_id, $networkports_id = 0)
    {
        global $DB, $PM_CONFIG;

        $pmHost = new PluginMonitoringHost();
        $pmService = new PluginMonitoringService();

        $pmCC_Host = new PluginMonitoringComponentscatalog_Host();
        $pmCC_Host->getFromDB($componentscatalogs_hosts_id);

        // Get catalog components
        $pmCC_Components = new PluginMonitoringComponentscatalog_Component();
        $components = $pmCC_Components->find("`plugin_monitoring_componentscatalogs_id`='".$componentscatalogs_id."'");
        foreach ($components as $data) {
            /* @var $item CommonDBTM */
            $itemtype = $pmCC_Host->fields['itemtype'];
            $item = new $itemtype();
            $item->getFromDB($pmCC_Host->fields['items_id']);

            if ($networkports_id == 0) {
                $input['entities_id'] =  $item->fields['entities_id'];
                $input['plugin_monitoring_componentscatalogs_hosts_id'] = $componentscatalogs_hosts_id;
                $input['plugin_monitoring_components_id'] = $data['plugin_monitoring_components_id'];
                $input['name'] = Dropdown::getDropdownName("glpi_plugin_monitoring_components", $data['plugin_monitoring_components_id']);
                $input['state'] = 'WARNING';
                $input['state_type'] = 'HARD';
                $pmService->add($input);
            } else if ($networkports_id > 0) {
                $a_services = $pmService->find("`plugin_monitoring_components_id`='".$data['plugin_monitoring_components_id']."'
               AND `plugin_monitoring_componentscatalogs_hosts_id`='".$componentscatalogs_hosts_id."'
               AND `networkports_id`='".$networkports_id."'", "", 1);
                $item = new NetworkPort();
                $item->getFromDB($networkports_id);
                if (count($a_services) == 0) {
                    $input = array();
                    $input['networkports_id'] = $networkports_id;
                    $input['entities_id'] =  $item->fields['entities_id'];
                    $input['plugin_monitoring_componentscatalogs_hosts_id'] = $componentscatalogs_hosts_id;
                    $input['plugin_monitoring_components_id'] = $data['plugin_monitoring_components_id'];
                    $input['name'] = Dropdown::getDropdownName("glpi_plugin_monitoring_components", $data['plugin_monitoring_components_id']);
                    $input['state'] = 'WARNING';
                    $input['state_type'] = 'HARD';
                    $pmService->add($input);
                } else {
                    $a_service = current($a_services);
                    $queryu = "UPDATE `glpi_plugin_monitoring_services`
                  SET `entities_id`='".$item->fields['entities_id']."'
                     WHERE `id`='".$a_service['id']."'";
                    $DB->query($queryu);
                }
            }

        }
    }


    /**
     * The componentscatalog_host is deleted, so we need remove the template(s)
     * configured in the componentscatalog.
     *
     * @param PluginMonitoringComponentscatalog_Host $cc_host
     * @throws Exception
     */
    static function unlinkComponents($cc_host)
    {
        PluginMonitoringToolbox::log("unlinkComponents: " . print_r($cc_host, true));

        // Get related host services
        $pmService  = new PluginMonitoringService();
        $services = $pmService->find("`plugin_monitoring_componentscatalogs_hosts_id`='". $cc_host->getID() ."'");
        foreach ($services as $data) {
            $_SESSION['plugin_monitoring_cc_host'] = $cc_host->fields;
            $pmService->delete(array('id' => $data['id']));
        }
    }


    /**
     * Store information in the session to log the configuration change
     *
     * @return boolean
     */
    function pre_deleteItem()
    {
        $_SESSION['plugin_monitoring_cc_host'] = $this->fields;

        return true;
    }


    function post_addItem()
    {
        if (isset($_SESSION['plugin_monitoring_nohook_addcomponentscatalog_host'])) {
            unset($_SESSION['plugin_monitoring_nohook_addcomponentscatalog_host']);
        } else {
            if (isset($this->input['networkports_id'])
                && $this->input['networkports_id'] > 0) {
                $this->linkComponents(
                    $this->fields['plugin_monitoring_componentscatalogs_id'],
                    $this->fields['id'],
                    $this->input['networkports_id']);
            } else {
                $this->linkComponents(
                    $this->fields['plugin_monitoring_componentscatalogs_id'],
                    $this->fields['id']);
            }
        }
    }


    function post_purgeItem()
    {
        global $DB;

        PluginMonitoringToolbox::log("unlinkComponents: " . print_r($cc_host, true));

        $query = "SELECT * FROM `glpi_plugin_monitoring_componentscatalogs_hosts`
         WHERE `itemtype`='" . $this->fields['itemtype'] . "'
            AND `items_id`='" . $this->fields['items_id'] . "'
         LIMIT 1";
        $result = $DB->query($query);
        if ($DB->numrows($result) == 0) {
            $queryH = "SELECT * FROM `glpi_plugin_monitoring_hosts`
            WHERE `itemtype`='" . $this->fields['itemtype'] . "'
              AND `items_id`='" . $this->fields['items_id'] . "'
            LIMIT 1";
            $resultH = $DB->query($queryH);
            if ($DB->numrows($resultH) == 1) {
                $dataH = $DB->fetch_assoc($resultH);
                $pmHost = new PluginMonitoringHost();
                $pmHost->delete($dataH);
            }
        }
    }
}