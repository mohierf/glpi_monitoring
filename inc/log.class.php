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

class PluginMonitoringLog extends CommonDBTM
{
    static $rightname = 'config';

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
        return __('Monitoring logs', 'monitoring');
    }


    static function cronInfo($name)
    {
        switch ($name) {
            case 'cleanlogs':
                return [
                    'description' => __('Clean monitoring logs', 'monitoring')
                ];
                break;
        }
        return [];
    }


    static function logEvent($event, $value, $who = '', $itemtype = '', $items_id = '')
    {

        // Log an event with Tag information ...
        // Should be moved to the webservice caller function ???
        $pmLog = new self();
        $pmLog->add([
            'itemtype' => $itemtype,
            'items_id' => $items_id,
            'user_name' => !empty($who) ? $who : $_SESSION["glpiname"],
            'date_mod' => date("Y-m-d H:i:s"),
            'action' => $event,
            'value' => $value
        ]);
    }


    /**
     * Log a restart event
     *
     * @param string $who
     */
    static function logRestart($who = '')
    {
        if (PLUGIN_MONITORING_SYSTEM == 'alignak') {
            self::logEvent('reload_configuration_started', $who);
        }
    }


    /**
     * Log a restart finished event
     *
     * @param string $who
     */
    static function logRestartFinished($who = '')
    {
        if (PLUGIN_MONITORING_SYSTEM == 'alignak') {
            self::logEvent('reload_configuration_finished', $who);
        } else {
            self::logEvent('restart', $who);
        }
    }


    /**
     * @param CronTask $task
     *
     * @return bool
     */
    static function cronCleanlogs($task)
    {
        global $DB, $PM_CONFIG;

        $pmLog = new PluginMonitoringLog();

        $id_restart = 0;
        $a_restarts = $pmLog->find("`action`='restart'", "`id` DESC", 1);
        if (count($a_restarts) > 0) {
            $a_restart = current($a_restarts);
            $id_restart = $a_restart['id'];
            $task->log("Last restart: " . print_r($a_restart, true));
        } else {
            $task->log("No last restart");
        }
        $id_reload = 0;
        $a_reloads = $pmLog->find("`action`='reload'", "`id` DESC", 1);
        if (count($a_reloads) > 0) {
            $a_reload = current($a_reloads);
            $id_reload = $a_reload['id'];
            $task->log("Last reload: " . print_r($a_reload, true));
        } else {
            $task->log("No last reload");
        }

        $task->log("Cleaning log entries older than " . $PM_CONFIG['log_retention'] . " days.");
        $seconds = $PM_CONFIG['log_retention'] * DAY_TIMESTAMP;
        $query = "DELETE FROM `glpi_plugin_monitoring_logs` WHERE UNIX_TIMESTAMP(date_mod) < UNIX_TIMESTAMP() - $seconds";
        if (($id_restart > 0) || ($id_reload > 0)) {
            // Keep last reload or restart command
            $id_restart = max($id_restart, $id_reload);
            $query .= " AND `id` < '" . $id_restart . "'";
        }
        if (!$DB->query($query)) {
            $task->log("Error with: " . $query);
        }

        $task->log("Cleaning services events entries older than " . $PM_CONFIG['log_retention'] . " days.");
        $query = "DELETE FROM `glpi_plugin_monitoring_serviceevents` WHERE UNIX_TIMESTAMP(date) < UNIX_TIMESTAMP() - $seconds";
        if (!$DB->query($query)) {
            $task->log("Error with: " . $query);
        }

        return true;
    }


    /**
     * Check if a restart happened before the provided delay
     *
     * @param int $delay number of seconds
     *
     * @return bool|array
     */
    static function isRestartRecent($delay = 1800)
    {
        $date5min = date("Y-m-d H:i:s", (date("U") - $delay)) . "\n";
        $condition = "(`action`='restart' OR `action`='restart_planned')";
        if (PLUGIN_MONITORING_SYSTEM == 'alignak') {
            $condition = "(`action`='reload_configuration_finished')";
        }
        $condition .= "AND `date_mod` > '$date5min'";

        $pmLog = new self();
        $a_restarts = $pmLog->find($condition, "`id` DESC");
        if (count($a_restarts) > 0) {
            PluginMonitoringToolbox::logIfDebug("isRestartRecent, found: " . print_r($a_restarts, true));
            return $a_restarts;
        }
        return false;
    }


    /**
     * Get modifications of resources (if have modifications);
     *
     * @param bool $display
     *
     * @return bool|string
     */
    static function hasConfigurationChanged($display = true)
    {
        // Get id of the last restart
        $id_restart = 0;
        $condition = "(`action`='restart' OR `action`='restart_planned')";
        if (PLUGIN_MONITORING_SYSTEM == 'alignak') {
            $condition = "(`action`='reload_configuration_finished')";
        }

        $pmLog = new self();
        $a_restarts = $pmLog->find($condition, "`id` DESC", 1);
        if (count($a_restarts) > 0) {
            $a_restart = current($a_restarts);
            $id_restart = $a_restart['id'];
        }
        PluginMonitoringToolbox::log("hasConfigurationChanged, last restart: " . print_r($a_restart, true));

        // Get change counters since the last restart finished
        $dbu = new DbUtils();
        $nb_deleted = $dbu->countElementsInTable(PluginMonitoringLog::getTable(),
            ['WHERE' => "`id` > '$id_restart' AND `action`='delete'"]);
        $nb_added = $dbu->countElementsInTable(PluginMonitoringLog::getTable(),
            ['WHERE' => "`id` > '$id_restart' AND `action`='add'"]);
        $nb_updated = $dbu->countElementsInTable(PluginMonitoringLog::getTable(),
            ['WHERE' => "`id` > '$id_restart' AND `action`='update'"]);

        $message = '';

        if ($nb_deleted > 0 OR $nb_added > 0 OR $nb_updated > 0) {
            PluginMonitoringToolbox::log("The configuration changed since the last restart, added: $nb_added, deleted: $nb_deleted, updated: $nb_updated");
            $message .= __('The configuration changed', 'monitoring') . "<br/>";
            if ($nb_added > 0) {
                $message .= '<div>';
                $message .= '&dash; <a onClick="$(\'#added_elements\').toggle()">' . $nb_added . "&nbsp;" . __('resources added', 'monitoring') . '</a>';

                $added = $pmLog->find("`action`='add' AND `id` > $id_restart", "`id` ASC");
                if (count($added) > 0) {
                    $message .= '<div id="added_elements" style="background: lightgrey; font-size: x-small; margin-left: 10px; display: none">';
                    $message .= '<ul>';
                    $idx = 0;
                    foreach ($added as $data) {
                        $message .= '<li>[' . Html::convDateTime($data['date_mod']) . '] ' . __('added: ', 'monitoring') . $data['value'] . '</li>';
                        if ($idx++ > 20) {
                            $message .= '<li>' . __('Do not display more than 20 items.', 'monitoring') . '</li>';
                            break;
                        }
                    }
                    $message .= '</ul>';
                    $message .= '</div>';
                }
                $message .= '</div>';
            }
            if ($nb_deleted > 0) {
                $message .= '<div>';
                $message .= '&dash; <a onClick="$(\'#deleted_elements\').toggle()">' . $nb_deleted . "&nbsp;" . __('resources deleted', 'monitoring') . '</a>';

                $added = $pmLog->find("`action`='delete' AND `id` > $id_restart", "`id` ASC");
                if (count($added) > 0) {
                    $message .= '<div id="deleted_elements" style="background: lightgrey; font-size: x-small; margin-left: 10px; display: none">';
                    $message .= '<ul>';
                    $idx = 0;
                    foreach ($added as $data) {
                        $message .= '<li>[' . Html::convDateTime($data['date_mod']) . '] ' . __('deleted: ', 'monitoring') . $data['value'] . '</li>';
                        if ($idx++ > 20) {
                            $message .= '<li>' . __('Do not display more than 20 items.', 'monitoring') . '</li>';
                            break;
                        }
                    }
                    $message .= '</ul>';
                    $message .= '</div>';
                }
                $message .= '</div>';
            }
            if ($nb_updated > 0) {
                $message .= '<div>';
                $message .= '&dash; <a onClick="$(\'#updated_elements\').toggle()">' . $nb_updated . "&nbsp;" . __('resources updated', 'monitoring') . '</a>';

                $added = $pmLog->find("`action`='update' AND `id` > $id_restart", "`id` ASC");
                if (count($added) > 0) {
                    $message .= '<div id="updated_elements" style="background: lightgrey; font-size: x-small; margin-left: 10px; display: none">';
                    $message .= '<ul>';
                    $idx = 0;
                    foreach ($added as $data) {
                        $message .= '<li>[' . Html::convDateTime($data['date_mod']) . '] ' . __('updated: ', 'monitoring') . $data['value'] . '</li>';
                        if ($idx++ > 20) {
                            $message .= '<li>' . __('Do not display more than 20 items.', 'monitoring') . '</li>';
                            break;
                        }
                    }
                    $message .= '</ul>';
                    $message .= '</div>';
                }
                $message .= '</div>';
            }

            $message .= "<br/>" . '<strong>' . __('The monitoring framework should reload this new configuration', 'monitoring') . '</strong>' . "<br/>";
        }

        if ($display) {
            if (!empty($message)) {
                echo '<div class="msgboxmonit msgboxmonit-orange">';
                echo $message;
                echo '</div>';
            } else {
                echo '<div class="msgboxmonit msgboxmonit-green">';
                echo __("The configuration did not changed since the last restart", 'kiosks');
                echo '</div>';
            }
        }

        return !empty($message);
    }


    /**
     * Get maximum time between 2 checks and see if have one event in this period
     *
     */
    static function isFrameworkRunning($display = true)
    {
        global $DB, $PM_CONFIG;

        $message = '';

        // One hour per default - convert to seconds
        $time_s = $PM_CONFIG['fmwk_check_period'] * 60;

        PluginMonitoringToolbox::logIfDebug("PluginMonitoringLog::framework_running_messages, check period: " . $time_s);

        // If some hosts are configured
        $nb_hosts = countElementsInTable("glpi_plugin_monitoring_hosts", "");
        if ($nb_hosts > 0) {
            PluginMonitoringToolbox::logIfDebug(
                "PluginMonitoringLog::framework_running_messages, hosts count: " . $nb_hosts);

            // Get recent host check results
            $result = $DB->query("SELECT * FROM `glpi_plugin_monitoring_hosts` 
                    WHERE UNIX_TIMESTAMP(last_check) > UNIX_TIMESTAMP() - " . $time_s . "
                    ORDER BY `last_check` DESC LIMIT 1");
            if ($DB->numrows($result) <= 0) {
                $message = __('No host check results found recently. 
                The monitoring framework seems to be stopped', 'monitoring');
                // If some services are configured
                $nb_services = countElementsInTable("glpi_plugin_monitoring_services", "");
                if ($nb_services > 0) {
                    PluginMonitoringToolbox::logIfDebug(
                        "PluginMonitoringLog::framework_running_messages, services count: " . $nb_services);

                    // Get recent service check results
                    $result = $DB->query("SELECT * FROM `glpi_plugin_monitoring_services` 
                    WHERE UNIX_TIMESTAMP(last_check) > UNIX_TIMESTAMP() - " . $time_s . "
                    ORDER BY `last_check` DESC LIMIT 1");
                    if ($DB->numrows($result) <= 0) {
                        $message = __('No service check results found recently. 
                        The monitoring framework seems to be stopped', 'monitoring');
                    }
                }
            }
        }

        if ($display) {
            if (!empty($message)) {
                echo '<div class="msgboxmonit msgboxmonit-orange">';
                echo $message;
                echo '</div>';
            } else {
                echo '<div class="msgboxmonit msgboxmonit-green">';
                echo __("The monitoring framework is reporting events.", 'kiosks');
                echo '</div>';
            }

            return !empty($msg_configuration);
        }

        return $message;
    }
}
