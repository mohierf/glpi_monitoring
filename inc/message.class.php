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

class PluginMonitoringMessage extends CommonDBTM
{
    /**
     * Get important messages
     */
    static function getMessages()
    {
        PluginMonitoringToolbox::logIfDebug("PluginMonitoringMessage::getMessages");

        $pmMessage = new self();
        $confchanges = $pmMessage->configuration_changes_messages();
        if (!empty($confchanges)) {
            echo '<div class="msgboxmonit msgboxmonit-orange">';
            echo $confchanges;
            echo '</div>';
        }

        $runningshinken = $pmMessage->framework_running_messages();
        if (!empty($runningshinken)) {
            echo '<div class="msgboxmonit msgboxmonit-orange">';
            echo $runningshinken;
            echo '</div>';
        }
    }


    /**
     * Get modifications of resources (if have modifications);
     */
    function configuration_changes_messages()
    {
        $input = '';

        // Get id of the last restart
        $id_restart = 0;
        $pmLog = new PluginMonitoringLog();
        $a_restarts = $pmLog->find("(`action`='restart' OR `action`='restart_planned')", "`id` DESC", 1);
        if (count($a_restarts) > 0) {
            $a_restart = current($a_restarts);
            $id_restart = $a_restart['id'];
        }

        // Get change counters since the last restart
        $dbu = new DbUtils();
        $nb_deleted = $dbu->countElementsInTable(PluginMonitoringLog::getTable(),
            ['WHERE' => "`id` > '$id_restart' AND `action`='delete'"]);
        $nb_added = $dbu->countElementsInTable(PluginMonitoringLog::getTable(),
            ['WHERE' => "`id` > '$id_restart' AND `action`='add'"]);
        $nb_updated = $dbu->countElementsInTable(PluginMonitoringLog::getTable(),
            ['WHERE' => "`id` > '$id_restart' AND `action`='update'"]);

        if ($nb_deleted > 0 OR $nb_added > 0 OR $nb_updated > 0) {
            $input .= __('The configuration changed', 'monitoring') . "<br/>";
            if ($nb_added > 0) {
                $input .= '<div>';
                $input .= '&dash; <a onClick="$(\'#added_elements\').toggle()">'. $nb_added . "&nbsp;" . __('resources added', 'monitoring') . '</a>';

                $added = $pmLog->find("`action`='add' AND `id` > $id_restart", "`id` ASC");
                if (count($added) > 0) {
                    $input .= '<div id="added_elements" style="background: lightgrey; font-size: x-small; margin-left: 10px; display: none">';
                    $input .= '<ul>';
                    $idx = 0;
                    foreach ($added as $data) {
                        $input .= '<li>[' . Html::convDateTime($data['date_mod']) . '] ' .__('added: ', 'monitoring'). $data['value'] . '</li>';
                        if ($idx++ > 20) {
                            $input .= '<li>'. __('Do not display more than 20 items.', 'monitoring') . '</li>';
                            break;
                        }
                    }
                    $input .= '</ul>';
                    $input .= '</div>';
                }
                $input .= '</div>';
            }
            if ($nb_deleted> 0) {
                $input .= '<div>';
                $input .= '&dash; <a onClick="$(\'#deleted_elements\').toggle()">'. $nb_deleted . "&nbsp;" . __('resources deleted', 'monitoring') . '</a>';

                $added = $pmLog->find("`action`='delete' AND `id` > $id_restart", "`id` ASC");
                if (count($added) > 0) {
                    $input .= '<div id="deleted_elements" style="background: lightgrey; font-size: x-small; margin-left: 10px; display: none">';
                    $input .= '<ul>';
                    $idx = 0;
                    foreach ($added as $data) {
                        $input .= '<li>[' . Html::convDateTime($data['date_mod']) . '] ' .__('deleted: ', 'monitoring'). $data['value'] . '</li>';
                        if ($idx++ > 20) {
                            $input .= '<li>'. __('Do not display more than 20 items.', 'monitoring') . '</li>';
                            break;
                        }
                    }
                    $input .= '</ul>';
                    $input .= '</div>';
                }
                $input .= '</div>';
            }
            if ($nb_updated> 0) {
                $input .= '<div>';
                $input .= '&dash; <a onClick="$(\'#updated_elements\').toggle()">'. $nb_updated . "&nbsp;" . __('resources updated', 'monitoring') . '</a>';

                $added = $pmLog->find("`action`='update' AND `id` > $id_restart", "`id` ASC");
                if (count($added) > 0) {
                    $input .= '<div id="updated_elements" style="background: lightgrey; font-size: x-small; margin-left: 10px; display: none">';
                    $input .= '<ul>';
                    $idx = 0;
                    foreach ($added as $data) {
                        $input .= '<li>[' . Html::convDateTime($data['date_mod']) . '] ' .__('updated: ', 'monitoring'). $data['value'] . '</li>';
                        if ($idx++ > 20) {
                            $input .= '<li>'. __('Do not display more than 20 items.', 'monitoring') . '</li>';
                            break;
                        }
                    }
                    $input .= '</ul>';
                    $input .= '</div>';
                }
                $input .= '</div>';
            }

            // Try to restart Shinken via webservice
            $input .= "<br>";
            $pmShinkenwebservice = new PluginMonitoringShinkenwebservice();
            $pmShinkenwebservice->sendRestartArbiter();
//            $input .= __('Shinken is restarted automatically', 'monitoring');
            $input .= '<strong>' . __('The monitoring framework should reload this new configuration', 'monitoring') . '</strong>';
            $input .= "<br>";
        }

        return $input;
    }


    /**
     * Get maximum time between 2 checks and see if have one event in this period
     *
     */
    function framework_running_messages()
    {
        global $DB, $PM_CONFIG;

        $message = '';

        // One hour per default - convert to seconds
        $time_s = $PM_CONFIG['fmwk_check_period'] * 60;

        PluginMonitoringToolbox::logIfDebug("PluginMonitoringMessage::framework_running_messages, check period: " . $time_s);

        // If some hosts are configured
        $result = $DB->query("SELECT count(id) as hosts_total FROM `glpi_plugin_monitoring_hosts`");
        $data = $DB->fetch_assoc($result);
        if ($data['hosts_total'] > 0) {
            PluginMonitoringToolbox::logIfDebug("PluginMonitoringMessage::framework_running_messages, hosts count: " . $data['hosts_total']);
            // If some services are configured
            $result = $DB->query("SELECT count(id) as services_total FROM `glpi_plugin_monitoring_services`");
            $data = $DB->fetch_assoc($result);
            if ($data['services_total'] > 0) {
                PluginMonitoringToolbox::logIfDebug("PluginMonitoringMessage::framework_running_messages, services count: " . $data['services_total']);
                $result = $DB->query("SELECT * FROM `glpi_plugin_monitoring_services` 
                    WHERE UNIX_TIMESTAMP(last_check) > UNIX_TIMESTAMP() - " . $time_s . "
                    ORDER BY `last_check` DESC
                    LIMIT 1");
                if ($DB->numrows($result) <= 0) {
                    $message = __('No events found recently. The monitoring framework seems to be stopped', 'monitoring');
                }
            }
        }
        return $message;
    }
}

