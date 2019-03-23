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

class PluginMonitoringServer extends CommonDropdown
{
    const URL_PING = "/ping";
    const URL_STATUS = "/status";

    public $display_dropdowntitle = false;

    static $rightname = 'plugin_monitoring_server';

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
        return __('Server', 'monitoring');
    }


    function getRights($interface = 'central')
    {
        $values = parent::getRights();
        unset($values[CREATE]);

        return $values;
    }


    static function cronInfo($name)
    {
        switch ($name) {
            case 'frameworkStatus':
                return [
                    'description' => __('Get the monitoring framework status', 'monitoring')
                ];
                break;
        }
        return [];
    }


    /**
     * @param CronTask $task
     *
     * @return bool
     */
    static function cronFrameworkStatus($task)
    {
        if (!isset($_SESSION['plugin_monitoring']['reduced_interface'])) {
            $_SESSION['plugin_monitoring']['reduced_interface'] = false;
        }

        PluginMonitoringToolbox::logIfDebug("PluginMonitoringServer::get servers status");

        $pmTag = new self();
        $pmAlignakWS = new PluginMonitoringAlignakWS();

        $ok = true;
        $servers = $pmTag->find();
        $task->log("Found " . count($servers) . " monitoring server instances.");
        foreach ($servers as $data) {
            $pmTag->getFromDB($data['id']);
            $task->log("Get status for: " . $pmTag->getName() . ", url: " . $pmTag->getUrl());

            $result = $pmAlignakWS->getStatus($data['id']);
            if ($result !== false) {
                $task->log($pmTag->getName() . " is : " . $result);
                if ($result !== 'up') {
                    $ok = false;
                }
                PluginMonitoringLog::logEvent(
                    "status", $result, "", "PluginMonitoringServer", $pmTag->getID());
            } else {
                $ok = false;
                $task->log($pmTag->getName() . " is not responding.");
            }
        }
        if ($ok) {
            $task->log("Global status is ok. At least one monitoring server is up and running");
        }

        return $ok;
    }


    function rawSearchOptions()
    {

        $tab = [];

        $tab[] = [
            'id' => 'common',
            'name' => __('Monitoring servers', 'monitoring')
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
            'id' => $index++,
            'table' => $this->getTable(),
            'field' => 'tag',
            'name' => __('Tag'),
        ];

        $tab[] = [
            'id' => $index++,
            'table' => $this->getTable(),
            'field' => 'comment',
            'name' => __('Comment'),
        ];

        $tab[] = [
            'id' => $index++,
            'table' => $this->getTable(),
            'field' => 'is_active',
            'datatype' => 'bool',
            'name' => __('Active?', 'monitoring'),
        ];

        $tab[] = [
            'id' => $index++,
            'table' => $this->getTable(),
            'field' => 'auto_restart',
            'datatype' => 'bool',
            'name' => __('Automatic restart ?', 'monitoring'),
        ];

        $tab[] = [
            'id' => $index++,
            'table' => $this->getTable(),
            'field' => 'url',
            'name' => __('URL', 'kiosks'),
        ];

        $tab[] = [
            'id' => $index,
            'table' => $this->getTable(),
            'field' => 'ip',
            'name' => __('Address', 'kiosks'),
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


    function showForm($items_id, $options = [], $copy = [])
    {
        if (count($copy) > 0) {
            foreach ($copy as $key => $value) {
                $this->fields[$key] = stripslashes($value);
            }
        }

        $this->initForm($items_id, $options);
        $this->showFormHeader($options);

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Tag', 'monitoring') . " :</td>";
        echo "<td>";
        echo $this->fields["tag"];
        echo "</td>";
        echo "<td>" . __('Name') . " :</td>";
        echo "<td>";
        echo "<input type='text' name='name' value='" . $this->fields["name"] . "' size='30'/>";
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Comment', 'monitoring') . "</td>";
        echo "<td colspan='3'>";
        echo "<textarea cols='80' rows='4' name='comment' >" . $this->fields['comment'] . "</textarea>";
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Active ?', 'monitoring') . "</td>";
        echo "<td>";
        if (self::canUpdate()) {
            Dropdown::showYesNo('is_active', $this->fields['is_active']);
        } else {
            echo Dropdown::getYesNo($this->fields['is_active']);
        }
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Monitoring framework web services URI', 'monitoring') . " :</td>";
        echo "<td>";
        if (!$this->fields["url"]) {
            $url = "http://" . $this->fields["tag"] . ':7770';
            echo '<span class="red">' . __('Default URL: ', 'monitoring') . $url . '</span>';
        }
        Html::autocompletionTextField($this, 'url');
        echo "</td>";
        echo "<td>" . __('Username (web service)', 'monitoring') . "&nbsp;:</td>";
        echo "<td>";
        Html::autocompletionTextField($this, 'username');
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Monitoring framework IP address', 'monitoring') . " :</td>";
        echo "<td>";
        Html::autocompletionTextField($this, 'ip');
        echo "</td>";
        echo "<td>" . __('Password (web service)', 'monitoring') . "&nbsp;:</td>";
        echo "<td>";
        Html::autocompletionTextField($this, 'password');
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Lock IP address', 'monitoring') . " :</td>";
        echo "<td>";
        Dropdown::showYesNo('locked_ip', $this->fields["locked_ip"]);
        echo "</td>";

        echo "<td>" . __('Automatic restart on configuration change', 'monitoring') . " :</td>";
        echo "<td>";
        Dropdown::showYesNo('auto_restart', $this->fields["auto_restart"]);
        echo "</td>";
        echo "</tr>";

        $this->showFormButtons($options);

        return true;
    }


    function setIP($ip, $tag = '', $name = '')
    {
        // Tag value
        if (empty($tag)) {
            if (empty($name)) {
                $tag = $ip;
            } else {
                $tag = $name;
            }
        }
        // Tag exist?
        if (!$this->getFromDBByCrit(['tag' => $tag])) {
            $this->add([
                'tag' => $tag,
                'name' => $name,
                'ip' => $ip
            ]);
            PluginMonitoringToolbox::log("Created a new server declaration: $tag ($name) @$ip");
        }
        $this->getFromDBByCrit(['tag' => $tag]);

        if (!$this->getField('locked_ip') and $this->fields['ip'] != $ip) {
            $input = [];
            $input['id'] = $this->getID();
            $input['ip'] = $ip;
            $this->update($input);
            PluginMonitoringToolbox::log("Updated a server address: $tag @$ip");
        }
    }


    function getIP($tag)
    {
        $a_tags = $this->find("`tag`='" . $tag . "'", '', 1);
        if (count($a_tags) > 0) {
            $a_tag = current($a_tags);
            return $a_tag['ip'];
        }
        return '';
    }


    function getPort($tag)
    {
        $a_tags = $this->find("`tag`='" . $tag . "'", '', 1);
        if (count($a_tags) > 0) {
            $a_tag = current($a_tags);
            return $a_tag['port'];
        }
        return '';
    }


    function getUrl($tag = '')
    {
        if (!empty($tag)) {
            if ($this->getFromDBByCrit(['tag' => $tag])) {
                return $this->getUrl();
            }
        } else {
            if (empty($this->getField('url'))) {
                return "http://" . $this->getField('tag') . ':7770';
            }
            return $this->getField('url');
        }
        return '';
    }


    function getAuth($tag = '')
    {
        if (!empty($tag)) {
            if ($this->getFromDBByCrit(['tag' => $tag])) {
                return $this->getAuth();
            }
        } else {
            return $this->fields['username'] . ":" . $this->fields['password'];
        }
        return '';
    }


    function getTagID($tag)
    {
        $a_tags = $this->find("`tag`='" . $tag . "'", '', 1);
        if (count($a_tags) > 0) {
            $a_tag = current($a_tags);
            return $a_tag['id'];
        }

        return $this->add(['tag' => $tag]);
    }


    /**
     * @param bool $display    set to display the servers status
     *
     * @param bool $get_status set to get the servers status, else it will use the last known status
     *
     * @return string
     */
    static function getServersStatus($display = false, $get_status = false)
    {
        if ($get_status) {
            // Update servers status
            $task = new CronTask();
            if ($task->getFromDBByCrit(['name' => 'FrameworkStatus'])) {
                self::cronFrameworkStatus($task);
            }
        }

        if ($display) {
            echo "<table class='tab_cadre' width='950'>";
        }

        $pmTag = new self();
        $servers = $pmTag->find();

        $overall_state = 'ok';

        $active_servers = 0;
        foreach ($servers as $data) {
            $pmTag->getFromDB($data['id']);

            $status = explode(PHP_EOL, $data['last_status']);
            $state = $status[0];
            $class_state = 'background-greyed';
            if ($data['is_active']) {
                $active_servers ++;
                $class_state = 'background-' . strtolower($state);
            }

            if ($data['is_active'] == '1') {
                switch ($state) {
                    case 'critical':
                        if ($overall_state != $state) $overall_state = $state;
                        break;

                    case 'warning':
                    case 'unknown':
                        if ($overall_state == 'ok') $overall_state = $state;
                        break;
                }
            }

            if ($display) {
                echo "<tr class='tab_bg_1'>";
                echo "<th colspan='5'>";
                echo $pmTag->getName();
                echo "</th>";
                echo "</tr>";

                echo "<tr class='$class_state'>";
                echo "<td>";
                echo Dropdown::getYesNo($data['is_active']);
                echo "</td>";
                echo "<td>";
                echo $data['ip'];
                echo "</td>";
                echo "<td>";
                echo $data['tag'];
                echo "</td>";
                echo "<td>";
                echo "</strong>$state</strong>";
                echo "</td>";
                echo "<td>";
                echo nl2br($data['last_status']);
                echo "</td>";
                echo "</tr>";
            }
        }
        if ($display) {
            echo "</table>";
        }

        if ($active_servers == 0) {
            $overall_state = 'none';
        }

        return $overall_state;
    }
}
