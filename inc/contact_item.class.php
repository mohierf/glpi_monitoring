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

class PluginMonitoringContact_Item extends CommonDBTM
{


    static $rightname = 'plugin_monitoring_contact';


    static function getTypeName($nb = 0)
    {
        return __('Contacts', 'monitoring');
    }


    /**
     * @param $itemtype
     * @param $items_id
     */
    function showContacts($itemtype, $items_id)
    {
        $can_edit = $this->canUpdate();

        if ($can_edit) {
            // Display the contact adding section
            $this->addContact($itemtype, $items_id);
        }

        $rand = mt_rand();

        echo '<table class="tab_cadre_fixe">';
        echo '<tr>';
        echo '<th>';
        echo __('Contacts', 'monitoring');
        echo '</th>';
        echo '</tr>';
        echo '</table>';

        // Still related contacts groups
        $a_list = $this->find("`items_id`='$items_id' AND `itemtype`='$itemtype' AND `groups_id` > 0");
        if (empty($a_list)) {
            echo __('No contacts groups are yet associated to this catalog.', 'monitoring');
        } else {
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
            echo "<th>" . __('Group') . " - " . __('Name') . "</th>";
            echo "<th colspan='3'></th>";
            echo "</tr>";

            $group = new Group();
            foreach ($a_list as $data) {
                $group->getFromDB($data['groups_id']);

                echo "<tr>";
                echo "<td width='10'>";
                if ($can_edit) {
                    Html::showMassiveActionCheckBox(__CLASS__, $data["id"]);
                }
                echo "</td>";

                echo "<td class='center'>";
                echo $group->getLink(1);
                echo "</td>";
                echo "<td colspan='3'>";
                echo "</td>";

                echo "</tr>";
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

        // Still related contacts users
        $a_list = $this->find("`items_id`='$items_id' AND `itemtype`='$itemtype' AND `users_id` > 0");
        if (empty($a_list)) {
            echo __('No contacts users are yet associated to this catalog.', 'monitoring');
        } else {
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
            echo "<th>" . __('User') . " - " . __('Name') . "</th>";
            echo "<th>" . __('Entity') . "</th>";
            echo "<th>" . __('Email address') . "</th>";
            echo "<th>" . __('Phone') . "</th>";
            echo "</tr>";

            $entity = new Entity();
            $user = new User();
            foreach ($a_list as $data) {
                $user->getFromDB($data['users_id']);

                echo "<tr>";
                echo "<td width='10'>";
                if ($can_edit) {
                    Html::showMassiveActionCheckBox(__CLASS__, $data["id"]);
                }
                echo "</td>";

                echo "<td class='center'>";
                echo $user->getLink(1);
                echo "</td>";
                $entity->getFromDB($data['entities_id']);
                echo "<td class='center'>";
                echo $entity->getName() . " <strong>(R)</strong>";
                echo "</td>";
                echo "<td class='center'>";
                $a_emails = UserEmail::getAllForUser($data['users_id']);
                $first = 0;
                foreach ($a_emails as $email) {
                    if ($first == 0) {
                        echo $email;
                    }
                    $first++;
                }
                echo "</td>";
                echo "<td class='center'>";
                echo $user->fields['phone'];
                echo "</td>";

                echo "</tr>";
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
    }


    function addContact($itemtype, $items_id)
    {
        global $CFG_GLPI;

        $this->getEmpty();

        $this->showFormHeader();

        echo "<tr>";
        echo "<td>";
        echo __('User') . "&nbsp;:";
        echo "<input type='hidden' name='items_id' value='" . $items_id . "'/>";
        echo "<input type='hidden' name='itemtype' value='" . $itemtype . "'/>";
        echo "</td>";
        echo "<td>";

        $paramscomment = array('value' => '__VALUE__');
        $toupdate = array('users_id' => 'value',
            'to_update' => "show_entity",
            'url' => $CFG_GLPI["root_doc"] . "/plugins/monitoring/ajax/dropdownUserEntities.php",
            'moreparams' => $paramscomment);
        Dropdown::show("User", array('name' => 'users_id', 'toupdate' => $toupdate));

        echo "</td>";
        echo "<td>";
        echo __('Entity') . " (" . strtolower(__('Recursive')) . ")&nbsp;:";
        echo "</td>";
        echo "<td>";
        echo "<span id='show_entity'></span>\n";
        echo "</td>";
        echo "</tr>";

        $this->showFormButtons();

        $this->showFormHeader();

        echo "<tr>";
        echo "<td>";
        echo __('Group') . "&nbsp;:";
        echo "<input type='hidden' name='items_id' value='" . $items_id . "'/>";
        echo "<input type='hidden' name='itemtype' value='" . $itemtype . "'/>";
        echo "</td>";
        echo "<td>";
        Dropdown::show("Group", array('name' => 'groups_id'));
        echo "</td>";
        echo "<td colspan='2'>";
        echo "</td>";
        echo "</tr>";

        $this->showFormButtons();
    }
}
