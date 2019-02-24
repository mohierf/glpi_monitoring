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

class PluginMonitoringConfig extends CommonDBTM
{
    static $rightname = 'config';

    static function getTypeName($nb = 1)
    {
        return _n('Configuration', 'Configurations', $nb, 'monitoring');
    }

    function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        if (!$withtemplate) {
            if ($item->getType() == 'Config') {
                return __('Monitoring plugin');
            }
        }
        return '';
    }

    static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {

        //      if (($item->getType() == 'Config')
        //         && ($item->getID() > 0)
        //         && Session::haveRight('plugin_alignak_configuration', READ)) {
        //         $config = new self();
        //         $config->showForm();
        //      }
        $config = new self();
        $config->showForm();
    }

    /**
     * Load the plugin configuration in a global variable $PM_CONFIG
     *
     * Test if the table exists before loading cache
     * The only case where table does not exists is when you click on
     * uninstall the plugin and it's already uninstalled
     *
     * @global array $PM_CONFIG
     */
    static function loadConfiguration()
    {
        global $DB, $PM_CONFIG;

//        PluginMonitoringToolbox::logIfDebug("Loading configuration...");

        $table = self::getTable();
        if (!$DB->tableExists($table)) {
            PluginMonitoringToolbox::log("Not found any configuration parameters table!");
            die("The plugin is not correctly installed because the configuration table does not exist in the database!");
        }

        $PM_CONFIG = [];
        $pluginConfig = new self();
        if (!$pluginConfig->getFromDB(1)) {
            PluginMonitoringToolbox::log("Not found any configuration parameters!");
            $pluginConfig->addConfiguration(true);
            $pluginConfig->getFromDB(1);
        }
        $PM_CONFIG = $pluginConfig->fields;
//        PluginMonitoringToolbox::logIfDebug("Configuration: " . print_r($PM_CONFIG, true));
    }

    /**
     * Initialize the database with the default configuration parameters
     *
     * This should never be used if the plugin is installed correctly, but
     * if a problem happens the loadConfiguration function may call this
     * initialization function
     *
     * If a global $PM_CONFIG still exists and the $forced parameter is not
     * set, no new parameters are inserted
     *
     * @global object $DB
     *
     * @param $forced boolean
     *
     * @return integer identifier of the new configuration row
     */
    function addConfiguration($forced = false)
    {
        global $PM_CONFIG;

        if (isset($PM_CONFIG) and !$forced) {
            PluginMonitoringToolbox::log("Do not create a new configuration!");
            return 0;
        }

        $input = [];
        $input['timezones'] = '["0"]';
        $input['log_retention'] = 30;
        $input['extra_debug'] = 0;
        $input['build_files'] = 1;
        $input['alignak_backend_url'] = 'http://127.0.0.1:5000';
        $input['alignak_webui_url'] = 'http://127.0.0.1:5001';
        $input['graphite_url'] = 'http://127.0.0.1:8080';
        $input['graphite_prefix'] = '';
        $id = $this->add($input);
        PluginMonitoringToolbox::log("Created a new configuration row: $id.");
        return $id;
    }

    /**
     * Get a configuration value
     *
     * @global array $PM_CONFIG
     * @param  string $name name in configuration
     * @return null|string|integer
     */
    static function getValue($name)
    {
        global $PM_CONFIG;

        if (!isset($PM_CONFIG)) {
            self::loadConfiguration();
        }
        if (isset($PM_CONFIG[$name])) {
            return $PM_CONFIG[$name];
        }

        return null;
    }

    /**
     * Update a configuration value
     *
     * @param  string $name name of configuration
     * @param  string $value
     * @return boolean
     */
    function updateValue($name, $value)
    {
        global $PM_CONFIG;

        $result = false;
        if (!isset($PM_CONFIG)) {
            $this->loadConfiguration();
        }
        if (isset($PM_CONFIG[$name])) {
            $PM_CONFIG[$name] = $value;
            $result = $this->update(['id' => $PM_CONFIG['id'], $name => $value]);
        }
        return $result;
    }

    static function configUpdate($input)
    {
        $input['configuration'] = 1 - $input['configuration'];
        return $input;
    }

    function showForm($ID = 0, $options = [])
    {

        $this->initForm($ID, $options);
        $this->showFormHeader($options);

        echo "<tr><th colspan='2'>" . __('Monitoring plugin setup') . "</th></tr>";
        echo "<td >" . __('Log extra debug:') . "</td>";
        echo "<td colspan='3'>";
        Dropdown::showYesNo("extra_debug", $this->fields['extra_debug']);
        echo "</td></tr>";

        echo '<tr class="tab_bg_1">';
        echo "<td >" . __('Build configuration files:') . "</td>";
        echo "<td colspan='3'>";
        Dropdown::showYesNo("build_files", $this->fields['build_files']);
        echo "</td></tr>";

        echo '<tr class="tab_bg_1">';
        echo '<td>';
        echo __('Alignak backend URL', 'alignak');
        echo '</td>';
        echo '<td>';
        Html::autocompletionTextField($this, 'alignak_backend_url', ['value' => $this->fields['alignak_backend_url']]);
        echo '</td>';
        echo '</tr>';

        echo '<tr class="tab_bg_1">';
        echo '<td>';
        echo __('Alignak Web UI URL', 'alignak');
        echo '</td>';
        echo '<td>';
        Html::autocompletionTextField($this, 'alignak_webui_url', ['value' => $this->fields['alignak_webui_url']]);
        echo '</td>';
        echo '</tr>';

        echo "<tr class='tab_bg_2'>";
        echo "<td colspan='4' class='center'>";
        echo "<input type='submit' name='update' class='submit' value=\"" . _sx('button', 'Save') . "\">";
        echo "</td></tr>";

        echo "</table>";
        echo "</div>";
        Html::closeForm();
    }


    static function getTimezones()
    {
        $a_timezones = [];
        $a_timezones['0'] = "GMT";
        $a_timezones['+1'] = "GMT+1";
        $a_timezones['+2'] = "GMT+2";
        $a_timezones['+3'] = "GMT+3";
        $a_timezones['+4'] = "GMT+4";
        $a_timezones['+5'] = "GMT+5";
        $a_timezones['+6'] = "GMT+6";
        $a_timezones['+7'] = "GMT+7";
        $a_timezones['+8'] = "GMT+8";
        $a_timezones['+9'] = "GMT+9";
        $a_timezones['+10'] = "GMT+10";
        $a_timezones['+11'] = "GMT+11";
        $a_timezones['+12'] = "GMT+12";
        $a_timezones['-1'] = "GMT-1";
        $a_timezones['-2'] = "GMT-2";
        $a_timezones['-3'] = "GMT-3";
        $a_timezones['-4'] = "GMT-4";
        $a_timezones['-5'] = "GMT-5";
        $a_timezones['-6'] = "GMT-6";
        $a_timezones['-7'] = "GMT-7";
        $a_timezones['-8'] = "GMT-8";
        $a_timezones['-9'] = "GMT-9";
        $a_timezones['-10'] = "GMT-10";
        $a_timezones['-11'] = "GMT-11";

        ksort($a_timezones);
        return $a_timezones;

    }


    function rrmdir($dir)
    {

        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (filetype($dir . "/" . $object) == "dir") {
                        $this->rrmdir($dir . "/" . $object);
                    } else {
                        unlink($dir . "/" . $object);
                    }
                }
            }
            reset($objects);
            rmdir($dir);
        }
    }


    static function load_alignak_url()
    {
        global $PM_CONFIG;

        PluginMonitoringToolbox::log("Configuration: " . print_r($PM_CONFIG), true);

        $config = new PluginMonitoringConfig();
        $config->getFromDB(1);
        PluginMonitoringToolbox::log("Configuration: " . print_r($config->fields), true);
        $PM_CONFIG['alignak_webui_url'] = $config->fields['alignak_webui_url'];
        $PM_CONFIG['alignak_backend_url'] = $config->fields['alignak_backend_url'];
    }

}