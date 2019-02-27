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

class PluginMonitoringShinkenwebservice extends CommonDBTM
{

    function sendAcknowledge($host_id = -1, $service_id = -1, $author = '', $comment = '', $sticky = '1', $notify = '1', $persistent = '1', $operation = '')
    {
//      global $DB;

        if (($host_id == -1) && ($service_id == -1)) {
            PluginMonitoringToolbox::log("acknowledge, sendAcknowledge, host : $host_id / $service_id\n");
            return false;
        }

//      PluginMonitoringToolbox::log("acknowledge, sendAcknowledge, host : $host_id / $service_id\n");

        $pmTag = new PluginMonitoringTag();
        $pmService = new PluginMonitoringService();
        $pmService->getFromDB($service_id);
        $service_description = $pmService->getName(['monitoring' => '1']);
        $pmHost = new PluginMonitoringHost();
        $pmHost->getFromDB(($host_id == -1) ? $pmService->getHostID() : $host_id);
//        $hostname = $pmHost->getName(true);
//
        $hostname = $pmService->getHostName();

//      PluginMonitoringToolbox::log("acknowledge, sendAcknowledge, host : $hostname\n");

        // Acknowledge an host ...
//      $acknowledgeServiceOnly = true;
//      $a_fields = array();

        if ($host_id == -1) {
            $tag = PluginMonitoringEntity::getTagByEntities($pmService->getEntityID());
        } else {
            // ... one service of the host.
            $tag = PluginMonitoringEntity::getTagByEntities($pmHost->getEntityID());
        }

        $action = 'acknowledge';
        $a_fields = [
            'action' => empty($operation) ? 'add' : $operation,
            'host_name' => $hostname,
            'author' => $author,
            'service_description' => $service_description,
            'comment' => mb_convert_encoding($comment, "iso-8859-1"),
            // 'comment'              => $comment,
            'sticky' => $sticky,
            'notify' => $notify,
            'persistent' => $persistent
        ];

        return $this->sendCommand($pmTag, $action, $a_fields, '');
    }


    function sendDowntime($host_id = -1, $service_id = -1, $author = '', $comment = '', $flexible = '0', $start_time = '0', $end_time = '0', $duration = '3600', $operation = '')
    {
//      global $DB;

        if (($host_id == -1) && ($service_id == -1)) return false;

        $pmTag = new PluginMonitoringTag();
        $pmService = new PluginMonitoringService();
        $pmService->getFromDB($service_id);
        $service_description = $pmService->getName(['monitoring' => '1']);
        $pmHost = new PluginMonitoringHost();
        $pmHost->getFromDB(($host_id == -1) ? $pmService->getHostID() : $host_id);
        $hostname = $pmHost->getName(true);
//        $hostname = $pmService->getHostName();

        // Downtime an host ...
//      $acknowledgeServiceOnly = true;
//      $a_fields = array();

        if ($host_id == -1) {
            $tag = PluginMonitoringEntity::getTagByEntities($pmService->getEntityID());
        } else {
            // ... one service of the host.
            $tag = PluginMonitoringEntity::getTagByEntities($pmHost->getEntityID());
        }

        $action = 'downtime';
        $a_fields = [
            'action' => empty($operation) ? 'add' : $operation,
            'host_name' => $hostname,
            'service_description' => $service_description,
            'author' => $author,
            'comment' => mb_convert_encoding($comment, "iso-8859-1"),
            'flexible' => $flexible,
            'start_time' => PluginMonitoringServiceevent::convert_datetime_timestamp($start_time),
            'end_time' => PluginMonitoringServiceevent::convert_datetime_timestamp($end_time),
            'trigger_id' => '0',
            'duration' => $duration
        ];

        // Send downtime command ...
        return $this->sendCommand($pmTag, $action, $a_fields, '');
    }


    function sendRestartArbiter($force = false, $tag = null, $command = 'restart')
    {
        $pmTag = new PluginMonitoringTag();
        $pmLog = new PluginMonitoringLog();

        PluginMonitoringToolbox::logIfDebug("sendRestartArbiter, command : $command, tag: $tag, force: $force\n");

        if ($pmLog->isRestartRecent() and !$force) {
            PluginMonitoringToolbox::log("sendRestartArbiter, no monitoring framework sent. Previous was too recent!");
            return;
        }

        $a_tags = [];
        if (!empty($tag)) {
            $a_tags[] = $pmTag->getFromDB($tag);
        } else {
            $a_tags = $pmTag->find("`is_active` = '1'");
        }

        foreach ($a_tags as $index => $data) {
            PluginMonitoringToolbox::log("sendRestartArbiter, " . print_r($data, true));
            $pmTag->getFromDB($data['id']);

            if ($this->sendCommand($pmTag, $command, [], '')) {
                PluginMonitoringToolbox::log("sendRestartArbiter, command sent to the monitoring framework");
                $input = [];
                $input['user_name'] = $_SESSION['glpifirstname'] . ' ' . $_SESSION['glpirealname'] . ' (' . $_SESSION['glpiname'] . ')';
                $input['action'] = $command . "_planned";
                $input['date_mod'] = date("Y-m-d H:i:s");
                $input['value'] = $data['tag'];
                $pmLog->add($input);
            } else {
                PluginMonitoringToolbox::log("sendRestartArbiter, failed sending command to the monitoring framework!");
            }
        }
    }


    /**
     * @param PluginMonitoringTag $tag
     * @param string $action
     * @param array $a_fields
     * @param string $fields_string
     *
     * @return bool
     */
    function sendCommand($tag, $action, $a_fields, $fields_string = '')
    {
        $url = $tag->getUrl();
        $url = $url . '/' . $action;
        $auth = $tag->getAuth();

        if ($fields_string == '') {
            foreach ($a_fields as $key => $value) {
                $fields_string .= $key . '=' . $value . '&';
            }
            rtrim($fields_string, '&');
        }

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, count($a_fields));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 4);
        curl_setopt($ch, CURLOPT_TIMEOUT, 4);
        if ($auth != '') {
            curl_setopt($ch, CURLOPT_USERPWD, $auth);
        }

        $ret = curl_exec($ch);
        $return = true;
        if ($ret === false) {
            $return = false;
            Session::addMessageAfterRedirect(
                __('Monitoring framework communication failed: ', 'monitoring') .
                curl_error($ch) . '<br/>' . $url . ' ' . $fields_string,
                false,
                ERROR);
        } else if (strstr($ret, 'error')) {
            $return = false;
            Session::addMessageAfterRedirect(
                __('Monitoring framework communication failed: ', 'monitoring') .
                $ret . '<br/>' . $url . ' ' . $fields_string,
                false,
                ERROR);
        } else {
            Session::addMessageAfterRedirect(
                __('Monitoring framework communication succeeded: ', 'monitoring') .
                $ret . '<br/>' . $url . ' ' . $fields_string,
                false);
        }
        curl_close($ch);

        if (!$return) {
            // Set the monitoring server tag as not active...
            $input = [];
            $input['id'] = $tag->getID();
            $input['is_active'] = false;
            $tag->update($input);
        }
        return $return;
    }
}
