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

class PluginMonitoringAlignakWS extends CommonDBTM
{

    function getStatus($tag)
    {
        $pmTag = new PluginMonitoringTag();
        if (!$pmTag->getFromDB($tag)) {
            return false;
        }
        $result = $this->sendCommand($pmTag, 'status', [], '');
        if ($result === false) {
            // Set the monitoring server tag as not active and the status as unknown...
            $pmTag->update(['id' => $pmTag->getID(), 'is_active' => false, 'last_status' => 'unknown']);

            return false;
        } else {
            PluginMonitoringToolbox::logIfDebug(
                "command '/status' sent to the monitoring framework: " . $pmTag->getName() . " (" . $pmTag->getUrl() . ")");

            $status = "ok";
            $state = 0;
            if (isset($result['livestate'])) {
                // $result['livestate']['state'] is always 'up' because the arbiter respond!
                $status = $result['livestate']['state'] . " - " . $result['livestate']['output'];
            }
            if (isset($result['services'])) {
                foreach ($result['services'] as $daemon) {
                    $status .= PHP_EOL . $daemon['name'] . ": " . $daemon['livestate']['state'] . ", " . $daemon['livestate']['output'];
                    switch ($daemon['livestate']['state']) {
                        case "ok":
                            $state = max($state, 0);
                            break;
                        case "warning":
                            $state = max($state, 1);
                            break;
                        case "critical":
                            $state = max($state, 2);
                            break;
                        default:
                            $state = max($state, 3);
                            break;
                    }
                }
                $status = ["ok", "warning", "critical", "unknown"][$state] . PHP_EOL . $status;
            }
            // Set the monitoring server tag as active...
            $pmTag->update(['id' => $pmTag->getID(), 'is_active' => true, 'last_status' => $status]);

            PluginMonitoringToolbox::log("status for : " . $pmTag->getName() . " is: " . $status);

            return $status;
        }
    }


    function sendAcknowledge($host_id = -1, $service_id = -1, $author = '', $comment = '', $sticky = '1', $notify = '1', $persistent = '1', $operation = '')
    {
//      global $DB;
        Toolbox::deprecated('PluginMonitoringAlignakWS::sendAcknowledge method is deprecated');

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
        Toolbox::deprecated('PluginMonitoringAlignakWS::sendDowntime method is deprecated');

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


    function ReloadRequest($force = false, $tag = null, $command = 'reload_configuration')
    {
        PluginMonitoringToolbox::log("ReloadRequest, command : $command, tag: $tag, force: $force");

        if (PluginMonitoringLog::isRestartRecent() and !$force) {
            PluginMonitoringToolbox::log("ReloadRequest, no command sent. Previous was too recent!");
            return;
        }

        $a_tags = [];
        $pmTag = new PluginMonitoringTag();
        if (!empty($tag)) {
            if ($pmTag->getFromDB($tag)) {
                $a_tags[] = $pmTag->fields;
            }
        } else {
            $a_tags = $pmTag->find("`is_active` = '1'");
        }

        foreach ($a_tags as $data) {
            PluginMonitoringToolbox::logIfDebug("ReloadRequest, tag data: " . print_r($data, true));
            $pmTag->getFromDB($data['id']);

            $result = $this->sendCommand($pmTag, $command, [], '');
            if ($result) {
                PluginMonitoringToolbox::log(
                    "command '$command' sent to the monitoring framework: " . $pmTag->getName() . " (" . $pmTag->getUrl() . ")");

                if (isset($result['_message'])) {
                    Session::addMessageAfterRedirect(
                        __('Monitoring framework communication succeeded: ', 'monitoring') .
                        $result['_message'], false);
                } else {
                    Session::addMessageAfterRedirect(
                        __('Monitoring framework communication succeeded: ', 'monitoring') .
                        $result, false);
                }

                PluginMonitoringLog::logEvent(
                    $command . "_planned", $data['tag'], "", "PluginMonitoringTag", $pmTag->getID());
            } else {
                PluginMonitoringToolbox::log(
                    "ReloadRequest, failed sending '$command' to the monitoring framework: " . $pmTag->getName() . " (" . $pmTag->getUrl() . ")");
            }
        }
    }


    /**
     * Alignak WS function
     * -------------------
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

        // CURL request
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 4);
        curl_setopt($ch, CURLOPT_TIMEOUT, 4);
        $result = curl_exec($ch);
        if ($result === false) {
            Session::addMessageAfterRedirect(
                __('Monitoring framework communication failed: ', 'monitoring') .
                curl_error($ch) . '<br/>' . $url . ' ' . $fields_string,
                false, ERROR);
        } else {
            // Decode the JSON response
            $result = json_decode($result, true);
            PluginMonitoringToolbox::logIfDebug("command '$action' response: " . print_r($result, true));
        }
        curl_close($ch);

        return $result;
    }


    /**
     * The original Shinken post function
     * ----------------------------------
     * @param PluginMonitoringTag $tag
     * @param string $action
     * @param array $a_fields
     * @param string $fields_string
     *
     * @return bool
     */
    function postCommand($tag, $action, $a_fields, $fields_string = '')
    {
        Toolbox::deprecated('PluginMonitoringAlignakWS::postCommand method is deprecated');

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
