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

class PluginMonitoringTag extends CommonDropdown
{
    const URL_PING = "/ping";
    const URL_STATUS = "/status";

    public $display_dropdowntitle = false;

//    public $first_level_menu = "plugins";
//    public $second_level_menu = "pluginmonitoringmenu";
//    public $third_level_menu = "tag";

    static $rightname = 'plugin_monitoring_tag';

    /**
     * Get name of this type
     *
     * @param int $nb
     * @return string name of this type by language of the user connected
     *
     */
    static function getTypeName($nb = 0)
    {
        return __('Tag', 'monitoring');
    }


    function getRights($interface = 'central')
    {
        $values = parent::getRights();
        unset($values[CREATE]);

        return $values;
    }


    function showForm($items_id, $options = array(), $copy = array())
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
        if (self::canCreate()) {
            Dropdown::showYesNo('is_active', $this->fields['is_active']);
        } else {
            echo Dropdown::getYesNo($this->fields['is_active']);
        }
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Monitoring framework web services URI', 'monitoring') . " :</td>";
        echo "<td>";
        if (! $this->fields["url"]) {
            $url = "http://" . $this->fields["tag"] . ':7770';
            echo '<span class="red">' . __('Default URL: ', 'monitoring'). $url . '</span>';
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


    function setIP($ip, $tag='', $name='')
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
        if (! $this->getFromDBByCrit(['tag' => $tag])) {
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


    function getUrl($tag)
    {
        if ($this->getFromDBByCrit(['tag' => $tag])) {
            if (empty($this->getField('url'))) {
                return "http://" . $this->getField('tag') . ':7770';
            }
        }
        return '';
    }


    function getAuth($tag)
    {

        $a_tags = $this->find("`tag`='" . $tag . "'", '', 1);
        if (count($a_tags) == 1) {
            $a_tag = current($a_tags);
            return $a_tag['username'] . ":" . $a_tag['password'];
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

        return $this->add(array('tag' => $tag));
    }


    function servers_status()
    {

        $servers = $this->find();

        echo "<table class='tab_cadre' width='950'>";
        foreach ($servers as $data) {
            PluginMonitoringToolbox::log("- " . $data['tag']);

            $url = $data["url"];
            if (! $data["url"]) {
                $url = "http://" . $data["tag"] . ':7770';
            }
            PluginMonitoringToolbox::log("-> " . $url . self::URL_STATUS);

            echo "<tr class='tab_bg_1'>";
            echo "<th colspan='2'>";
            echo $data['ip'];
            echo "</th>";
            echo "</tr>";

            echo "<tr class='tab_bg_1'>";
            echo "<td>";
            echo "ping :";
            echo "</td>";
            echo "<td>";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url . self::URL_PING);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 4);
            curl_setopt($ch, CURLOPT_TIMEOUT, 4);
            $ret = curl_exec($ch);
            curl_close($ch);
            echo $ret;
            echo "</td>";
            echo "</tr>";

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url . self::URL_STATUS);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 4);
            curl_setopt($ch, CURLOPT_TIMEOUT, 4);
            $ret = curl_exec($ch);
            curl_close($ch);
            if ($ret != '') {
                foreach (json_decode($ret) as $module => $dataret) {
                    echo "<tr class='tab_bg_1'>";
                    echo "<td>";
                    echo $module;
                    echo "</td>";
                    echo "<td>";
                    if ($dataret[0]->alive == 1) {
                        echo "<div class='service serviceOK' style='float : left;'></div>";
                    } else {
                        echo "<div class='service serviceCRITICAL' style='float : left;'></div>";
                    }
                    echo "</td>";
                    echo "</tr>";
                }
            }
        }
        echo "</table>";
    }


    static function get_servers_status()
    {
        $ok = true;
        $pmTag = new self();
        $servers = $pmTag->find("`is_active`='1'");

        PluginMonitoringToolbox::logIfDebug("PluginMonitoringTag::get servers status");

        foreach ($servers as $data) {
            PluginMonitoringToolbox::log("- " . $data['tag']);

            $url = $data["url"];
            if (! $data["url"]) {
                $url = "http://" . $data["tag"] . ':7770';
            }
            PluginMonitoringToolbox::log("-> " . $url . self::URL_STATUS);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url . self::URL_STATUS);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 4);
            curl_setopt($ch, CURLOPT_TIMEOUT, 4);
            $ret = curl_exec($ch);
            curl_close($ch);
            PluginMonitoringToolbox::log("= " . $ret);

            if (strstr($ret, 'Not found')) {
                $ok = false;
            } else if ($ret != '') {
                foreach (json_decode($ret) as $module => $dataret) {
                    if ($dataret[0]->alive != 1) {
                        $ok = false;
                    }
                }
            } else {
                $ok = false;
            }
        }
        return $ok;
    }
}
