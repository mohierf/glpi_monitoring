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

// ----------------------------------------------------------------------
// Original Author of file: Frederic Mohier
// Purpose of file: Plugin setup and configuration
// ----------------------------------------------------------------------

/*
 * Plugin global configuration variables
 */
define("PLUGIN_MONITORING_OFFICIAL_RELEASE", "0");
define('PLUGIN_MONITORING_VERSION', '9.3+0.2');
define('PLUGIN_MONITORING_PHP_MIN_VERSION', '5.6');
define('PLUGIN_MONITORING_GLPI_MIN_VERSION', '9.2');
define('PLUGIN_MONITORING_NAME', 'monitoring plugin');
define('PLUGIN_MONITORING_LOG', 'plugin-monitoring');

/*
 * Set to:
 * - shinken for Shinken
 * - alignak for Algnak (configuration files)
 * - alignak_backend for Alignak backend connection (removed from initial source code!)
 */
define("PLUGIN_MONITORING_SYSTEM", "alignak");

if (!defined("PLUGIN_MONITORING_DIR")) {
    define("PLUGIN_MONITORING_DIR", GLPI_ROOT . "/plugins/monitoring");
}
if (!defined("PLUGIN_MONITORING_DOC_DIR")) {
    define("PLUGIN_MONITORING_DOC_DIR", GLPI_PLUGIN_DOC_DIR . "/monitoring");
}
if (!file_exists(PLUGIN_MONITORING_DOC_DIR)) {
    mkdir(PLUGIN_MONITORING_DOC_DIR);
}
if (!defined("PLUGIN_MONITORING_CFG_DIR")) {
    define("PLUGIN_MONITORING_CFG_DIR", PLUGIN_MONITORING_DOC_DIR . '/configuration_files');
}
if (!file_exists(PLUGIN_MONITORING_CFG_DIR)) {
    mkdir(PLUGIN_MONITORING_CFG_DIR);
}

define('_MPDF_TEMP_PATH', PLUGIN_MONITORING_DOC_DIR . '/pdf/');

// Used for cached configuration values
$PM_CONFIG = [];

// Used for cached monitoring framework livestate
$PM_LIVESTATE = [
    'hosts_total' => 0,
    'hosts_not_monitored' => 0,
    'hosts_up_hard' => 0,
    'hosts_up_soft' => 0,
    'hosts_down_hard' => 0,
    'hosts_down_soft' => 0,
    'hosts_unreachable_hard' => 0,
    'hosts_unreachable_soft' => 0,
    'hosts_problems' => 0,
    'hosts_acknowledged' => 0,
    'hosts_in_downtime' => 0,
    'hosts_flapping' => 0,
    'services_total' => 0,
    'services_not_monitored' => 0,
    'services_ok_hard' => 0,
    'services_ok_soft' => 0,
    'services_warning_hard' => 0,
    'services_warning_soft' => 0,
    'services_critical_hard' => 0,
    'services_critical_soft' => 0,
    'services_unknown_hard' => 0,
    'services_unknown_soft' => 0,
    'services_unreachable_hard' => 0,
    'services_unreachable_soft' => 0,
    'services_problems' => 0,
    'services_acknowledged' => 0,
    'services_in_downtime' => 0,
    'services_flapping' => 0,
];

/**
 * Initialize the plugin hooks
 * @return array
 * @throws Exception
 */
function plugin_init_monitoring()
{
    global $PLUGIN_HOOKS;

    $PLUGIN_HOOKS['csrf_compliant']['monitoring'] = true;

//   $PLUGIN_HOOKS['change_profile']['monitoring'] = array('PluginMonitoringProfile','changeprofile');

    $Plugin = new Plugin();
    if ($Plugin->isActivated('monitoring')) {

        // Classes registration
        Plugin::registerClass('PluginMonitoringCommmand');
        Plugin::registerClass('PluginMonitoringNotificationcommand');
        Plugin::registerClass('PluginMonitoringEventhandler');
        Plugin::registerClass('PluginMonitoringComponent');

        Plugin::registerClass('PluginMonitoringContact',
            ['addtabon' => ['User']]);

        Plugin::registerClass('PluginMonitoringEntity',
            ['addtabon' => ['Entity']]);

        Plugin::registerClass('PluginMonitoringHost',
            ['addtabon' => ['Central', 'Computer']]);
        Plugin::registerClass('PluginMonitoringHostaddress',
            ['addtabon' => ['Computer']]);
        Plugin::registerClass('PluginMonitoringService',
            ['addtabon' => ['Central']]);
        Plugin::registerClass('PluginMonitoringServiceevent',
            ['addtabon' => ['Computer']]);

        Plugin::registerClass('PluginMonitoringProfile',
            ['addtabon' => ['Profile']]);
        Plugin::registerClass('PluginMonitoringSystem',
            ['addtabon' => ['Central']]);

        Plugin::registerClass('PluginMonitoringRedirecthome',
            ['addtabon' => ['User']]);

        // Load the plugin configuration
        PluginMonitoringConfig::loadConfiguration();

        $PLUGIN_HOOKS['use_massive_action']['monitoring'] = 1;

        $PLUGIN_HOOKS['add_css']['monitoring'] = [
            "lib/nvd3/src/nv.d3.css",
            "lib/jqueryplugins/tagbox/css/jquery.tagbox.css",
            "css/views.css",
        ];
        $PLUGIN_HOOKS['add_javascript']['monitoring'] = [
            "lib/jscolor/jscolor.min.js",
            "lib/jqueryplugins/tagbox/js/jquery.tagbox.min.js",
        ];

        // Plugin profiles management
        // todo: what is it for ?
        if (isset($_SESSION["glpiactiveprofile"])
            and isset($_SESSION["glpiactiveprofile"]["interface"])
            and $_SESSION["glpiactiveprofile"]["interface"] == "helpdesk") {
            $profile = new Profile();
            if ($profile->getFromDB($_SESSION['glpiactiveprofile']['id'])) {
                foreach ($profile->fields as $rname => $right) {
                    if (substr($rname, 0, 18) === 'plugin_monitoring_') {
                        $_SESSION['glpiactiveprofile'][$rname] = $right;
                    }
                }
            }
        }

        if (Session::haveRight("plugin_monitoring_dashboard", READ)) {
            $PLUGIN_HOOKS["helpdesk_menu_entry"]['monitoring'] = '/front/dashboard.php';
        }

        // Display a menu entry ?
        if (Session::haveRight('config', UPDATE)) {
            // Configuration page
            $PLUGIN_HOOKS['config_page']['monitoring'] = 'front/config.form.php';

            // Add an entry to the Administration menu
            if (Session::haveRight('plugin_monitoring_configuration', READ)) {
                $PLUGIN_HOOKS['menu_toadd']['monitoring'] = [
//                    'admin' => 'PluginMonitoringMenu',
                    'config' => 'PluginMonitoringMenu'
                ];
            }

            // No menu when on simplified interface
            $PLUGIN_HOOKS["helpdesk_menu_entry"]['monitoring'] = false;
        }


        // Tabs for each type
        $PLUGIN_HOOKS['headings']['monitoring'] = 'plugin_get_headings_monitoring';
        $PLUGIN_HOOKS['headings_action']['monitoring'] = 'plugin_headings_actions_monitoring';

//        // Icons add, search...
//        // Still useful to declare all that stuff ? Menu is ok without this ...
//        $PLUGIN_HOOKS['submenu_entry']['monitoring']['add']['componentscatalog'] = 'front/componentscatalog.form.php?add=1';
//        $PLUGIN_HOOKS['submenu_entry']['monitoring']['search']['componentscatalog'] = 'front/componentscatalog.php';
//
//        $PLUGIN_HOOKS['submenu_entry']['monitoring']['add']['displayview'] = 'front/displayview.form.php?add=1';
//        $PLUGIN_HOOKS['submenu_entry']['monitoring']['search']['displayview'] = 'front/displayview.php';
//
//        $PLUGIN_HOOKS['submenu_entry']['monitoring']['add']['PluginMonitoringRealm'] = 'front/realm.form.php?add=1';
//        $PLUGIN_HOOKS['submenu_entry']['monitoring']['search']['PluginMonitoringRealm'] = 'front/realm.php';
//
//        $PLUGIN_HOOKS['submenu_entry']['monitoring']['add']['perfdata'] = 'front/perfdata.form.php?add=1';
//        $PLUGIN_HOOKS['submenu_entry']['monitoring']['search']['perfdata'] = 'front/perfdata.php';
//
//        $PLUGIN_HOOKS['submenu_entry']['monitoring']['add']['customitem_gauge'] = 'front/customitem_gauge.form.php?add=1';
//        $PLUGIN_HOOKS['submenu_entry']['monitoring']['search']['customitem_gauge'] = 'front/customitem_gauge.php';
//
//        $PLUGIN_HOOKS['submenu_entry']['monitoring']['add']['customitem_counter'] = 'front/customitem_counter.form.php?add=1';
//        $PLUGIN_HOOKS['submenu_entry']['monitoring']['search']['customitem_counter'] = 'front/customitem_counter.php';
//
//        $PLUGIN_HOOKS['submenu_entry']['monitoring']['search']['service'] = 'front/display.php';
//        $PLUGIN_HOOKS['submenu_entry']['monitoring']['search']['service'] = 'front/host.php';

        $rule_check = [
            'PluginMonitoringComponentscatalog_rule', 'doesThisItemVerifyRule'
        ];
        $PLUGIN_HOOKS['item_add']['monitoring'] = [
            'Computer' => $rule_check,
            'PluginMonitoringComponentscatalog_rule' => [
                'PluginMonitoringComponentscatalog_rule', 'getItemsDynamically'],
            'PluginMonitoringComponentscatalog_Host' => [
                'PluginMonitoringHost', 'addHost']];
        $PLUGIN_HOOKS['item_update']['monitoring'] = [
            'Computer' => $rule_check,
            'PluginMonitoringComponentscatalog' => [
                'PluginMonitoringComponentscatalog', 'replayRulesCatalog'],
            'PluginMonitoringComponentscatalog_rule' => [
                'PluginMonitoringComponentscatalog_rule', 'getItemsDynamically']];
        $PLUGIN_HOOKS['item_purge']['monitoring'] = [
            'Computer' => $rule_check,
            'PluginMonitoringComponentscatalog_rule' => [
                'PluginMonitoringComponentscatalog_rule', 'getItemsDynamically'],
            'PluginMonitoringComponentscatalog_Host' => [
                'PluginMonitoringComponentscatalog_Host', 'unlinkComponents'],
            'PluginMonitoringComponentscatalog' => [
                'PluginMonitoringComponentscatalog', 'removeCatalog'],
//            'PluginMonitoringBusinessrulegroup' => [
//                'PluginMonitoringBusinessrule', 'removeBusinessruleonDeletegroup']
        ];

        if (!isset($_SESSION['plugin_monitoring']['_refresh'])) {
            $_SESSION['plugin_monitoring']['_refresh'] = '60';
        }
        $PLUGIN_HOOKS['post_init']['monitoring'] = 'plugin_monitoring_postinit';

        // Register Web services
        if (class_exists('PluginWebservicesClient')) {
            $PLUGIN_HOOKS['webservices']['monitoring'] = 'plugin_monitoring_registerMethods';
        } else {
            PluginMonitoringToolbox::log("The Web services plugin is not installed! 
            You should install to use all the available features.");
        }
    }
    return $PLUGIN_HOOKS;
}

// Name and Version of the plugin
function plugin_version_monitoring()
{
    // Use requirements (Glpi > 9.2)
    return [
        'name' => 'Monitoring plugin',
        'version' => PLUGIN_MONITORING_VERSION,
        'author' => 'Frédéric Mohier & <a href="http://alignak.net" target="_blank">Alignak Team</a >',
        'license' => '<a href="../plugins/monitoring/LICENSE" target="_blank">AGPLv3</a>',
        'homepage' => 'https://github.com/mohierf/glpi_monitoring',
        'requirements' => [
            'php' => [
                'min' => PLUGIN_MONITORING_PHP_MIN_VERSION
            ],
            'glpi' => [
                'min' => PLUGIN_MONITORING_GLPI_MIN_VERSION,
                'max' => '9.4',
                'dev' => (PLUGIN_MONITORING_OFFICIAL_RELEASE == 0)
            ],
            /* Required Glpi parameters
            'params' => [

            ],
            */
            /* Required installed and enabled plugins
            'plugins' => [

            ]
            */
        ]
    ];
}


/**
 * Check pre-requisites before install
 * OPTIONAL, but recommended
 *
 * @return boolean
 */
function plugin_monitoring_check_prerequisites()
{
    $return = true;

    $plugin = new Plugin();
    if (!$plugin->isInstalled('webservices')) {
        echo __("This plugin requires the 'Web services' plugin to be installed and activated", "kiosks");
        $return = false;
    } elseif (!$plugin->isActivated('webservices')) {
        echo __("This plugin requires the 'Web services' plugin to be activated", "kiosks");
        $return = false;
    }

    $version = rtrim(GLPI_VERSION, '-dev');
    if (version_compare($version, PLUGIN_MONITORING_GLPI_MIN_VERSION, 'lt')) {
        echo __('This plugin requires GLPI ' . PLUGIN_MONITORING_GLPI_MIN_VERSION, 'monitoring');

        $return = false;
    }

    return $return;
}


function plugin_monitoring_check_config()
{
    return true;
}
