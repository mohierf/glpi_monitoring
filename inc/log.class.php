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
                    'description' => __('Clean monitoring logs', 'monitoring'),
                    'parameter'   => __('Cron parameter Email des compteurs alignak', 'alignak')
                ];
                break;
        }
        return array();
    }


    static function logEvent($event, $value, $who='', $itemtype='', $items_id='') {

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
     * @param string $who
     */
    static function logRestart($who='') {
        self::logEvent('restart', $who);
    }


    /**
     * @param CronTask $task
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
        if (! $DB->query($query)) {
            $task->log("Error with: " . $query);
        }

        $task->log("Cleaning services events entries older than " . $PM_CONFIG['log_retention'] . " days.");
        $query = "DELETE FROM `glpi_plugin_monitoring_serviceevents` WHERE UNIX_TIMESTAMP(date) < UNIX_TIMESTAMP() - $seconds";
        if (! $DB->query($query)) {
            $task->log("Error with: " . $query);
        }

        return true;
    }


    /**
     * Check if a restart happened before the provided delay
     *
     * @param int $delay number of seconds
     * @return bool
     */
    function isRestartRecent($delay = 1800)
    {
        $date5min = date("Y-m-d H:i:s", (date("U") - $delay)) . "\n";
        $a_restarts = $this->find("(`action`='restart' OR `action`='restart_planned') "
            . "AND `date_mod` > '" . $date5min . "'", "`id` DESC", 1);
        if (count($a_restarts) > 0) {
            return true;
        }
        return false;
    }
}
