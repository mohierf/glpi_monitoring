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

class PluginMonitoringComponentscatalog_rule extends CommonDBTM
{
    static $rightname = 'plugin_monitoring_componentscatalog';

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
        return _n('Dynamic hosts rule', 'Dynamic hosts rules', $nb);
    }


    function showRules($componentscatalogs_id)
    {
        $can_edit = $this->canUpdate();

        if ($can_edit) {
            $this->showAddRule($componentscatalogs_id);
        }

        $rand = mt_rand();

        echo '<table class="tab_cadre_fixe">';
        echo '<tr>';
        echo '<th>';
        echo __('Dynamic hosts rules', 'monitoring');
        echo '</th>';
        echo '</tr>';
        echo '</table>';

        // Still related hosts
        // Get catalog rules
        $rules = $this->find("`plugin_monitoring_componentscatalogs_id`='$componentscatalogs_id'");
        if (empty($rules)) {
            echo __('No dynamic hosts affectation rules are yet associated to this catalog.', 'monitoring');
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

        echo "<table class='tab_cadre_fixe'>";
        echo "<tr>";
        echo "<th width='10'>" . ($can_edit ? Html::getCheckAllAsCheckbox('mass' . __CLASS__ . $rand) : "&nbsp;") . "</th>";
        echo "<th>" . __('Type') . "</th>";
        echo "<th>" . __('Name') . "</th>";
        echo "</tr>";

        foreach ($rules as $data) {
            $this->getFromDB($data['id']);

            echo '<tr>';

            echo "<td width='10'>";
            if ($can_edit) {
                Html::showMassiveActionCheckBox(__CLASS__, $data["id"]);
            }
            echo "</td>";
            echo '<td class="center">';
            echo $data['itemtype'];
            echo '</td>';
            echo '<td class="center">';
            if (empty($this->getName())) {
                $this->fields['name'] = $this->fields['itemtype'];
            }
            echo $this->getLink();
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


    function showAddRule($componentscatalogs_id)
    {
        $used = [];
        $networkport_types = ['Computer'];
//        $networkport_types[] = "PluginMonitoringNetworkport";

        $rules = $this->find("`plugin_monitoring_componentscatalogs_id`='$componentscatalogs_id'");
        foreach ($rules as $data) {
            $key = array_search($data['itemtype'], $networkport_types);
            if (isset($key)) {
                unset($networkport_types[$key]);
            }
        }

//        $query = "SELECT * FROM `{$this->getTable()}` WHERE `plugin_monitoring_componentscatalogs_id`='$componentscatalogs_id'";
//        $result = $DB->query($query);
//        while ($data = $DB->fetch_array($result)) {
//            $key = array_search($data['itemtype'], $networkport_types);
//            if (isset($key)) {
//                unset($networkport_types[$key]);
//            }
//        }
//
        if (count($used) == count($networkport_types)) {
            return;
        }

        $this->getEmpty();

        $this->showFormHeader();

        echo "<tr class='tab_bg_1'>";
        echo "<td>";
        echo __('Name') . "&nbsp;:";
        echo "</td>";
        echo "<td>";
        echo "<input type='text' name='name' value=''/>";
        echo "</td>";
        echo "<td>";
        echo __('Item type') . "&nbsp;:";
        echo "</td>";
        echo "<td>";
        Dropdown::showItemTypes('itemtype', $networkport_types, ["used" => $used]);
        echo "<input type='hidden' name='plugin_monitoring_componentscatalogs_id' value='". $componentscatalogs_id ."' >";
        echo "</td>";
        echo "</tr>";

        $this->showFormButtons();

    }


    /**
     * Show the search engine for rules
     */
    function showRuleSearch()
    {
        global $CFG_GLPI;

        PluginMonitoringToolbox::logIfDebug(print_r($_GET, true));

        $params = Search::manageParams($_GET['itemtype'], $_GET);
        $params['showbookmark'] = false;
        $params['target'] = $CFG_GLPI['root_doc'] . "/plugins/monitoring/front/componentscatalog_rule.form.php";
        $params['addhidden'] = [];
        $params['addhidden']['plugin_monitoring_componentscatalogs_id'] = $_GET['plugin_monitoring_componentscatalogs_id'];
        $params['addhidden']['name'] = $_GET['name'];
        if (isset($_GET['id'])) {
            $params['addhidden']['id'] = $_GET['id'];
        }

        ob_start();
        Search::showGenericSearch($_GET['itemtype'], $params);
        $form = ob_get_contents();
        ob_end_clean();
        if (isset($_GET['id'])) {
            $table = "<tr class='tab_bg_1'>"
                . "<td align='center'>"
                . "<input type='submit' name='updaterule' value=\"" . __('Update this rule', 'monitoring') . "\" class='submit' >"
                . "</td>"
                . "<td align='center'>"
                . "<input type='submit' name='deleterule' value=\"" . __('Delete this rule', 'monitoring') . "\" class='submit' >"
                . "</td>"
                . "</tr>"
                . "</table><input";
        } else {
            $table = "<tr class='tab_bg_1'>"
                . "<td align='center' colspan='2'>"
                . "<input type='submit' name='addrule' value=\"" . __('Add this rule', 'monitoring') . "\" class='submit' >"
                . "</td>"
                . "</tr>"
                . "</table><input";
        }
        $form = str_replace("</table>\n<input", $table, $form);
        echo $form;

        echo "<br/>";
        echo "<table class='tab_cadre_fixe'>";

        echo "<tr>";
        echo "<th>";
        echo __('Preview', 'monitoring');
        echo "</th>";
        echo "</tr>";

        echo "<tr>";
        echo "<td>";

        $pmComponentscatalog = new PluginMonitoringComponentscatalog();
        $pmComponentscatalog->getFromDB($_GET['plugin_monitoring_componentscatalogs_id']);

        $default_entity = 0;
        if (isset($_SESSION['glpiactive_entity'])) {
            $default_entity = $_SESSION['glpiactive_entity'];
        }
        $entities_isrecursive = 0;
        if (isset($_SESSION['glpiactiveentities'])
            AND count($_SESSION['glpiactiveentities']) > 1) {
            $entities_isrecursive = 1;
        }

        Session::changeActiveEntities($pmComponentscatalog->fields['entities_id'],
            $pmComponentscatalog->fields['is_recursive']);

        Search::showList($_GET['itemtype'], $params);

        Session::changeActiveEntities($default_entity,
            $entities_isrecursive);
        echo "</td>";
        echo "</tr>";
        echo "</table>";
    }


    /*
     * Use when a rule is added, calculate for all items in GLPI DB
     */
    static function getItemsDynamically($parm)
    {
        global $DB;

        $pmCc = new PluginMonitoringComponentscatalog();
        $pmCc_Host = new PluginMonitoringComponentscatalog_Host();
        $pmService = new PluginMonitoringService();

        $found_devices = [];
        $devicesnetworkport_present = [];

        $pmCc_Rule = new PluginMonitoringComponentscatalog_rule();
        if ($pmCc_Rule->getFromDB($parm->fields['id'])) {
            PluginMonitoringToolbox::log(
                "addRule, getItemsDynamically: " . print_r($pmCc_Rule->fields, true));

            $pmCc->getFromDB($pmCc_Rule->fields['plugin_monitoring_componentscatalogs_id']);

            // Load correct entity
            $default_entity = 0;
            if (isset($_SESSION['glpiactive_entity'])) {
                $default_entity = $_SESSION['glpiactive_entity'];
            }
            $entities_isrecursive = 0;
            if (isset($_SESSION['glpiactiveentities'])
                and count($_SESSION['glpiactiveentities']) > 1) {
                $entities_isrecursive = 1;
            }
            if (!isset($_SESSION['glpiactiveprofile']['entities'])) {
                $_SESSION['glpiactiveprofile']['entities'] = [];
            }
            Session::changeActiveEntities($pmCc->fields['entities_id'], $pmCc->fields['is_recursive']);

            $itemtype = $pmCc_Rule->fields['itemtype'];
            $condition = importArrayFromDB($pmCc_Rule->fields['condition']);
            $params = Search::manageParams($itemtype, $condition, false);

            $pmComponentscatalog_hosts = new PluginMonitoringComponentscatalog_Host();
            $hosts = $pmComponentscatalog_hosts->find(
                "`plugin_monitoring_componentscatalogs_id`='{$pmCc_Rule->fields["plugin_monitoring_componentscatalogs_id"]}' 
                AND `itemtype`='{$pmCc_Rule->fields['itemtype']}' AND `is_static`='0'");
            foreach($hosts as $cc_host) {
                $found_devices[$cc_host['id']] = true;
            }

//            $queryd = "SELECT * FROM `glpi_plugin_monitoring_componentscatalogs_hosts`
//            WHERE `plugin_monitoring_componentscatalogs_id`='" . $pmCc_Rule->fields["plugin_monitoring_componentscatalogs_id"] . "'
//               AND `itemtype`='" . $pmCc_Rule->fields['itemtype'] . "'
//               AND `is_static`='0'";
//            $result = $DB->query($queryd);
//            while ($data = $DB->fetch_array($result)) {
//                $found_devices[$data['id']] = true;
//            }

//            $queryd = "SELECT `glpi_plugin_monitoring_services`.`id` FROM `glpi_plugin_monitoring_componentscatalogs_hosts`
//               LEFT JOIN `glpi_plugin_monitoring_services`
//                  ON `plugin_monitoring_componentscatalogs_hosts_id` =
//                     `glpi_plugin_monitoring_componentscatalogs_hosts`.`id`
//            WHERE `plugin_monitoring_componentscatalogs_id`='" . $pmCc_Rule->fields["plugin_monitoring_componentscatalogs_id"] . "'
//               AND `itemtype`='NetworkEquipment'
//               AND `networkports_id`!='0'
//               AND `is_static`='0'";
//            $result = $DB->query($queryd);
//            while ($data = $DB->fetch_array($result)) {
//                $devicesnetworkport_present[$data['id']] = true;
//            }

            $data = Search::prepareDatasForSearch($itemtype, $params);
            $data['search']['export_all'] = true;
            Search::constructSQL($data);

            $DBread = DBConnection::getReadConnection();
            $DBread->query("SET SESSION group_concat_max_len = 16384;");
            $result = $DBread->query($data['sql']['search']);
            /// Check group concat limit : if warning : increase limit
            if ($result2 = $DBread->query('SHOW WARNINGS')) {
                if ($DBread->numrows($result2) > 0) {
                    $res = $DBread->fetch_assoc($result2);
                    if ($res['Code'] == 1260) {
                        $DBread->query("SET SESSION group_concat_max_len = 4194304;");
                        $result = $DBread->query($data['sql']['search']);
                    }
                }
            }

            while ($data = $DB->fetch_array($result)) {
                $networkports_id = 0;
                $itemtype_device = $pmCc_Rule->fields['itemtype'];
                $items_id_device = $data['id'];
                $cc_id = $pmCc_Rule->fields["plugin_monitoring_componentscatalogs_id"];
                PluginMonitoringToolbox::log("getItemsDynamically, CC Host: $itemtype_device, $items_id_device");




                if ($itemtype_device == 'PluginMonitoringNetworkport') {
                    $pmNetworkport = new PluginMonitoringNetworkport();
                    $pmNetworkport->getFromDB($data['id']);
                    $itemtype_device = $pmNetworkport->fields['itemtype'];
                    $items_id_device = $pmNetworkport->fields['items_id'];
                    $networkports_id = $pmNetworkport->fields['networkports_id'];
                    $networkPort = new NetworkPort();

                    if ($networkPort->getFromDB($networkports_id)) {

                        $querynet = "SELECT `itemtype`, `items_id`,
                        `glpi_plugin_monitoring_services`.`id`
                        FROM `glpi_plugin_monitoring_componentscatalogs_hosts`
                     LEFT JOIN `glpi_plugin_monitoring_services`
                        ON `plugin_monitoring_componentscatalogs_hosts_id` =
                           `glpi_plugin_monitoring_componentscatalogs_hosts`.`id`
                     WHERE `plugin_monitoring_componentscatalogs_id`='" . $pmCc_Rule->fields["plugin_monitoring_componentscatalogs_id"] . "'
                        AND `itemtype`='" . $itemtype_device . "'
                        AND `items_id`='" . $items_id_device . "'
                        AND `networkports_id`='" . $networkports_id . "'
                           LIMIT 1";
                        $resultnet = $DB->query($querynet);
                        if ($DB->numrows($resultnet) == 0) {
                            $input = [];
                            $input['plugin_monitoring_componentscatalogs_id'] = $pmCc_Rule->fields["plugin_monitoring_componentscatalogs_id"];
                            $input['is_static'] = '0';
                            $input['items_id'] = $items_id_device;
                            $input['itemtype'] = $itemtype_device;
                            $input['networkports_id'] = $networkports_id;
                            $componentscatalogs_hosts_id = $pmCc_Host->add($input);
                        } else {
                            $data2 = $DB->fetch_assoc($resultnet);
                            // modify entity of services (if entity of device is changed)
                            $itemtype = $data2['itemtype'];
                            $item = new $itemtype();
                            $item->getFromDB($data2['items_id']);
                            $queryu = "UPDATE `glpi_plugin_monitoring_services`
                           SET `entities_id`='" . $item->fields['entities_id'] . "'
                              WHERE `id`='" . $data2['id'] . "'";
                            $DB->query($queryu);


                            unset($devicesnetworkport_present[$data2['id']]);
                        }
                    } else {
                        $pmNetworkport->delete($pmNetworkport->fields);
                    }
                    // Reload current entity
                    Session::changeActiveEntities($default_entity, $entities_isrecursive);

                } else {
                    if (!$pmCc_Host->getFromDBByCrit([
                        'plugin_monitoring_componentscatalogs_id' => $cc_id,
                        'itemtype' => $itemtype_device, 'items_id' => $items_id_device])) {

                        // Host does not yet exist
                        $input = [];
                        $input['is_static'] = '0';
                        $input['items_id'] = $items_id_device;
                        $input['itemtype'] = $itemtype_device;
                        $input['plugin_monitoring_componentscatalogs_id'] = $cc_id;
                        $pmCc_Host->add($input);
                        PluginMonitoringToolbox::log("getItemsDynamically, added a new CC Host");
                    } else {
                        // Get the related computer (or else)
                        $itemtype = $pmCc_Host->fields['itemtype'];
                        $item = new $itemtype();
                        /* @var CommonDBTM $item */
                        if ($item->getFromDB($pmCc_Host->fields['items_id'])) {
                            // Search add/update the PM host
                            $pmHost = new PluginMonitoringHost();
                            if ($pmHost->getFromDBByCrit([
                                'itemtype' => $itemtype, 'items_id' => $pmCc_Host->fields['items_id']])) {
                                // Found with itemtype / items_id
                                $input = [];
                                $input['id'] = $pmHost->getID();
                                $input['entities_id'] = $item->fields['entities_id'];
                                $input['name'] = $item->getName();
                                $input['host_name'] = $item->getName();
                                $pmHost->update($input);
                                PluginMonitoringToolbox::log("-> updated PM Host with name");
                            } else {
                                if ($pmHost->getFromDBByCrit(['host_name' => $item->getName()])) {
                                    // Found with name
                                    $input = [];
                                    $input['id'] = $pmHost->getID();
                                    $input['entities_id'] = $item->fields['entities_id'];
                                    $input['itemtype'] = "Computer";
                                    $input['items_id'] = $item->getID();
                                    $pmHost->update($input);
                                    PluginMonitoringToolbox::log("-> updated PM Host with ids");
                                } else {
                                    PluginMonitoringToolbox::log("-> no PM Host");
                                    PluginMonitoringHost::addHost($pmCc_Host);
                                }
                            }

                            // Update services entities id
//                            $query = "UPDATE `glpi_plugin_monitoring_services`
//                            SET `entities_id`='{$item->fields['entities_id']}'
//                            WHERE `plugin_monitoring_componentscatalogs_hosts_id`='{$pmCc_Host->getID()}'";
                            $query = "UPDATE `glpi_plugin_monitoring_services` 
                            SET `entities_id`='{$item->fields['entities_id']}'
                            WHERE `host_name`='{$pmHost->fields['host_name']}'";
                            $DB->query($query);

                            PluginMonitoringToolbox::log(
                                "getItemsDynamically, updated an existing CC Host");
                        } else {
                            PluginMonitoringToolbox::log(
                                "The CC host is not existing: {$pmCc_Host->fields['itemtype']} - {$pmCc_Host->fields['items_id']}!");
                        }

                        // Remove from found devices list
                        unset($found_devices[$pmCc_Host->getID()]);
                    }
                }
            }

            // Reload current entity
            Session::changeActiveEntities($default_entity, $entities_isrecursive);

            foreach ($devicesnetworkport_present as $id => $num) {
                $_SESSION['plugin_monitoring']['cc_host']['itemtype'] = $itemtype_device;
                $_SESSION['plugin_monitoring']['cc_host']['items_id'] = $items_id_device;
                $pmService->delete(['id' => $id]);
            }
        } else {
            PluginMonitoringToolbox::log("removeRule, getItemsDynamically: " . print_r($parm, true));

            // Remove the rule
            $pmComponentscatalog_hosts = new PluginMonitoringComponentscatalog_Host();
            $hosts = $pmComponentscatalog_hosts->find(
                "`plugin_monitoring_componentscatalogs_id`='{$parm->fields["plugin_monitoring_componentscatalogs_id"]}' 
                AND `itemtype`='{$parm->fields['itemtype']}'");
            foreach($hosts as $cc_host) {
                $found_devices[$cc_host['id']] = true;
            }
        }

        foreach ($found_devices as $id => $num) {
            $pmCc_Host->delete(['id' => $id]);
        }
        return true;
    }


    /*
     * When add or update an item (Computer, ...), check if
     * a rule verify it
     */
    static function doesThisItemVerifyRule($parm)
    {
        global $DB;

        $itemtype = get_class($parm);
        $items_id = $parm->fields['id'];

        $session_glpisearch = [];
        if (isset($_SESSION['glpisearch'])) {
            $session_glpisearch = $_SESSION['glpisearch'];
        }
        $session_glpisearchcount = [];
        if (isset($_SESSION['glpisearchcount'])) {
            $session_glpisearchcount = $_SESSION['glpisearchcount'];
        }
        $session_glpisearchcount2 = [];
        if (isset($_SESSION['glpisearchcount2'])) {
            $session_glpisearchcount2 = $_SESSION['glpisearchcount2'];
        }

        $a_find = [];
        $pmComponentscatalog_rule = new PluginMonitoringComponentscatalog_rule();
        $pmComponentscatalog = new PluginMonitoringComponentscatalog();

        $query = "SELECT * FROM `" . $pmComponentscatalog_rule->getTable() . "`
         WHERE `itemtype`='" . $itemtype . "'";
        $result = $DB->query($query);
        $get_tmp = [];
        if (isset($_GET)) {
            $get_tmp = $_GET;
        }

        while ($data = $DB->fetch_array($result)) {
            if (!isset($_SESSION['glpiactiveentities_string'])) {
                $_SESSION['glpiactiveentities_string'] = $parm->fields['entities_id'];
            }

            // Load right entity
            $pmComponentscatalog->getFromDB($data['plugin_monitoring_componentscatalogs_id']);
            $default_entity = 0;
            if (isset($_SESSION['glpiactive_entity'])) {
                $default_entity = $_SESSION['glpiactive_entity'];
            }
            $entities_isrecursive = 0;
            if (isset($_SESSION['glpiactiveentities'])
                AND count($_SESSION['glpiactiveentities']) > 1) {
                $entities_isrecursive = 1;
            }
            if (!isset($_SESSION['glpiactiveprofile']['entities'])) {
                $_SESSION['glpiactiveprofile']['entities'] = [
                    $pmComponentscatalog->fields['entities_id'] => [
                        'id' => $pmComponentscatalog->fields['entities_id'],
                        'name' => '',
                        'is_recursive' => $pmComponentscatalog->fields['is_recursive']
                    ]
                ];
            }
            Session::changeActiveEntities($pmComponentscatalog->fields['entities_id'],
                $pmComponentscatalog->fields['is_recursive']);

            $itemtype = $data['itemtype'];

            $condition = importArrayFromDB($data['condition']);
            $params = Search::manageParams($itemtype, $condition, FALSE);

            $datar = Search::prepareDatasForSearch($itemtype, $params);
            $datar['search']['export_all'] = true;
            Search::constructSQL($datar);

            $DBread = DBConnection::getReadConnection();
            $DBread->query("SET SESSION group_concat_max_len = 16384;");
            $resultr = $DBread->query($datar['sql']['search']);
            /// Check group concat limit : if warning : increase limit
            if ($result2 = $DBread->query('SHOW WARNINGS')) {
                if ($DBread->numrows($result2) > 0) {
                    $res = $DBread->fetch_assoc($result2);
                    if ($res['Code'] == 1260) {
                        $DBread->query("SET SESSION group_concat_max_len = 4194304;");
                        $resultr = $DBread->query($datar['sql']['search']);
                    }
                }
            }
            $find = 0;
            while ($datar = $DB->fetch_array($resultr)) {
                if ($datar['id'] == $items_id) {
                    $find = 1;
                    break;
                }
            }
            if ($find == 1) {
                $a_find[$data['plugin_monitoring_componentscatalogs_id']] = 1;
            } else {
                if (!isset($a_find[$data['plugin_monitoring_componentscatalogs_id']])) {
                    $a_find[$data['plugin_monitoring_componentscatalogs_id']] = 0;
                }
            }

            // Reload current entity
            Session::changeActiveEntities($default_entity,
                $entities_isrecursive);
        }
        if (count($get_tmp) > 0) {
            $_GET = $get_tmp;
        }
        $pmComponentscatalog_Host = new PluginMonitoringComponentscatalog_Host();

        foreach ($a_find as $componentscalalog_id => $is_present) {
            if ($is_present == '0') { // * Remove from dynamic if present
                $query = "SELECT * FROM `glpi_plugin_monitoring_componentscatalogs_hosts`
               WHERE `plugin_monitoring_componentscatalogs_id`='" . $componentscalalog_id . "'
                  AND `itemtype`='" . $itemtype . "'
                  AND `items_id`='" . $items_id . "'
                  AND`is_static`='0'";
                $result = $DB->query($query);
                while ($data = $DB->fetch_array($result)) {
                    $pmComponentscatalog_Host->delete(['id' => $data['id']]);
                }
            } else { //  add if not present
                $query = "SELECT * FROM `glpi_plugin_monitoring_componentscatalogs_hosts`
               WHERE `plugin_monitoring_componentscatalogs_id`='" . $componentscalalog_id . "'
                  AND `itemtype`='" . $itemtype . "'
                  AND `items_id`='" . $items_id . "'
                     LIMIT 1";
                $result = $DB->query($query);
                if ($DB->numrows($result) == '0') {
                    $input = [];
                    $input['plugin_monitoring_componentscatalogs_id'] = $componentscalalog_id;
                    $input['is_static'] = '0';
                    $input['items_id'] = $items_id;
                    $input['itemtype'] = $itemtype;
                    $pmComponentscatalog_Host->add($input);
                } else {
                    $data2 = $DB->fetch_assoc($result);
                    // modify entity of services (if entity of device is changed)
                    $item = new $itemtype();
                    $item->getFromDB($items_id);
                    $queryu = "UPDATE `glpi_plugin_monitoring_services`
                     SET `entities_id`='" . $item->fields['entities_id'] . "'
                        WHERE `plugin_monitoring_componentscatalogs_hosts_id`='" . $data2['id'] . "'";
                    $DB->query($queryu);
                }
            }
        }
        if ($itemtype == 'NetworkEquipment') {
            //Get networkports
            $pmComponentscatalog_rule->doesThisItemVerifyRuleNetworkport($parm);
        }

        $_SESSION['glpisearch'] = $session_glpisearch;
        $_SESSION['glpisearchcount'] = $session_glpisearchcount;
        $_SESSION['glpisearchcount2'] = $session_glpisearchcount2;
    }


    static function doesThisItemVerifyRuleNetworkport($parm)
    {
        global $DB;

        $pmComponentscatalog_rule = new self();
        $pmService = new PluginMonitoringService();

        $a_networkports_id = [];
        if (get_class($parm) == 'PluginMonitoringNetworkport') {
            $a_networkports_id[$parm->fields['networkports_id']] = $parm->fields['items_id'];
        } else if (get_class($parm) == 'NetworkEquipment') {
            $query = "SELECT * FROM `glpi_plugin_monitoring_networkports`
            WHERE `items_id`='" . $parm->fields['id'] . "'";
            $result = $DB->query($query);
            while ($data = $DB->fetch_array($result)) {
                $a_networkports_id[$data['networkports_id']] = $parm->fields['id'];
            }
        }

        foreach ($a_networkports_id as $networkports_id => $networkequipments_id) {
            $a_find = [];

            $query = "SELECT * FROM `" . $pmComponentscatalog_rule->getTable() . "`
            WHERE `itemtype`='PluginMonitoringNetworkport'";
            $result = $DB->query($query);
            $get_tmp = [];
            if (isset($_GET)) {
                $get_tmp = $_GET;
            }
            while ($data = $DB->fetch_array($result)) {
                $session_glpisearch = [];
                if (isset($_SESSION['glpisearch'])) {
                    $session_glpisearch = $_SESSION['glpisearch'];
                }
                $session_glpisearchcount = [];
                if (isset($_SESSION['glpisearchcount'])) {
                    $session_glpisearchcount = $_SESSION['glpisearchcount'];
                }
                $session_glpisearchcount2 = [];
                if (isset($_SESSION['glpisearchcount2'])) {
                    $session_glpisearchcount2 = $_SESSION['glpisearchcount2'];
                }

                $_GET = importArrayFromDB($data['condition']);

                if (!isset($_SESSION['glpiactiveentities_string'])) {
                    if (get_class($parm) == 'PluginMonitoringNetworkport') {
                        $item = new $parm->fields['itemtype'];
                        $item->getFromDB($parm->fields['items_id']);
                        $_SESSION['glpiactiveentities_string'] = $item->fields['entities_id'];
                    } else {
                        $_SESSION['glpiactiveentities_string'] = $parm->fields['entities_id'];
                    }
                }

                $condition = importArrayFromDB($data['condition']);
                $params = Search::manageParams($data['itemtype'], $condition, FALSE);

                $datar = Search::prepareDatasForSearch($data['itemtype'], $params);
                $datar['search']['export_all'] = true;
                Search::constructSQL($datar);

                $DBread = DBConnection::getReadConnection();
                $DBread->query("SET SESSION group_concat_max_len = 16384;");
                $resultr = $DBread->query($datar['sql']['search']);

                if ($DBread->numrows($resultr) > 0) {
                    $a_find[$data['plugin_monitoring_componentscatalogs_id']][$networkports_id] = 1;
                } else {
                    if (!isset($a_find[$data['plugin_monitoring_componentscatalogs_id']][$networkports_id])) {
                        $a_find[$data['plugin_monitoring_componentscatalogs_id']][$networkports_id] = 0;
                    }
                }
            }
            if (count($get_tmp) > 0) {
                $_GET = $get_tmp;
            }
            $pmComponentscatalog_Host = new PluginMonitoringComponentscatalog_Host();

            foreach ($a_find as $componentscalalog_id => $datan) {
                foreach ($datan as $networkports_id => $is_present) {
                    // Get all networports in this rule
                    if ($is_present == '0') { // * Remove from dynamic if present
                        $query = "SELECT `glpi_plugin_monitoring_services`.`id`,
                        `glpi_plugin_monitoring_services`.`plugin_monitoring_components_id`,
                        `glpi_plugin_monitoring_componentscatalogs_hosts`.`id` as hid
                        FROM `glpi_plugin_monitoring_services`
                     LEFT JOIN `glpi_plugin_monitoring_componentscatalogs_hosts` ON
                        `plugin_monitoring_componentscatalogs_hosts_id` = `glpi_plugin_monitoring_componentscatalogs_hosts`.`id`
                     WHERE `plugin_monitoring_componentscatalogs_id`='" . $componentscalalog_id . "'
                        AND `itemtype`='NetworkEquipment'
                        AND `items_id`='" . $networkequipments_id . "'
                        AND `is_static`='0'
                        AND `networkports_id`='" . $networkports_id . "'";
                        $result = $DB->query($query);
                        while ($data = $DB->fetch_array($result)) {
                            $pmComponentscatalog_Host->getFromDB($data['hid']);
                            $_SESSION['plugin_monitoring']['cc_host'] = $pmComponentscatalog_Host->fields;
                            $pmService->delete(['id' => $data['id']]);
                        }
                    } else { //  add if not present
                        // * Add componentscatalogs_hosts if not exist
                        $componentscatalogs_hosts_id = 0;
                        $query = "SELECT * FROM `glpi_plugin_monitoring_componentscatalogs_hosts`
                     WHERE `plugin_monitoring_componentscatalogs_id`='" . $componentscalalog_id . "'
                        AND `itemtype`='NetworkEquipment'
                        AND `items_id`='" . $networkequipments_id . "'
                           LIMIT 1";
                        $result = $DB->query($query);
                        if ($DB->numrows($result) == '0') {
                            $input = [];
                            $input['plugin_monitoring_componentscatalogs_id'] = $componentscalalog_id;
                            $input['is_static'] = '0';
                            $input['itemtype'] = "NetworkEquipment";
                            $input['items_id'] = $networkequipments_id;
                            $_SESSION['plugin_monitoring']['nohook_addcomponentscatalog_host'] = 1;
                            $componentscatalogs_hosts_id = $pmComponentscatalog_Host->add($input);
                        } else {
                            $a_componentscatalogs_hosts = $DB->fetch_assoc($result);
                            $componentscatalogs_hosts_id = $a_componentscatalogs_hosts['id'];
                        }
                        // * Add service if not exist
                        $pmComponentscatalog_Host->linkComponents($componentscalalog_id,
                            $componentscatalogs_hosts_id,
                            $networkports_id);

                    }
                }
            }
        }
    }
}
