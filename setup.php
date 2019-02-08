<?php

/*
   ------------------------------------------------------------------------
   Plugin Monitoring for GLPI
   Copyright (C) 2011-2016 by the Plugin Monitoring for GLPI Development Team.

   https://forge.indepnet.net/projects/monitoring/
   ------------------------------------------------------------------------

   LICENSE

   This file is part of Plugin Monitoring project.

   Plugin Monitoring for GLPI is free software: you can redistribute it and/or modify
   it under the terms of the GNU Affero General Public License as published by
   the Free Software Foundation, either version 3 of the License, or
   (at your option) any later version.

   Plugin Monitoring for GLPI is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
   GNU Affero General Public License for more details.

   You should have received a copy of the GNU Affero General Public License
   along with Monitoring. If not, see <http://www.gnu.org/licenses/>.

   ------------------------------------------------------------------------

   @package   Plugin Monitoring for GLPI
   @author    David Durieux
   @co-author
   @comment
   @copyright Copyright (c) 2011-2016 Plugin Monitoring for GLPI team
   @license   AGPL License 3.0 or (at your option) any later version
              http://www.gnu.org/licenses/agpl-3.0-standalone.html
   @link      https://forge.indepnet.net/projects/monitoring/
   @since     2011

   ------------------------------------------------------------------------
 */

// ----------------------------------------------------------------------
// Original Author of file: Frederic Mohier
// Purpose of file: Plugin setup and configuration
// ----------------------------------------------------------------------

/*
 * Plugin global configuration variables
 */
define("PLUGIN_MONITORING_OFFICIAL_RELEASE", "0");
define('PLUGIN_MONITORING_VERSION', '9.3+0.1-dev');
define('PLUGIN_MONITORING_PHP_MIN_VERSION', '5.6');
define('PLUGIN_MONITORING_GLPI_MIN_VERSION', '9.2');
define('PLUGIN_MONITORING_NAME', 'monitoring plugin');
define('PLUGIN_MONITORING_LOG', 'plugin-monitoring');

if (!defined("PLUGIN_MONITORING_DIR")) {
    define("PLUGIN_MONITORING_DIR", GLPI_ROOT . "/plugins/monitoring");
}
if (!defined("PLUGIN_MONITORING_DOC_DIR")) {
    define("PLUGIN_MONITORING_DOC_DIR", GLPI_PLUGIN_DOC_DIR . "/monitoring");
}
if (!file_exists(PLUGIN_MONITORING_DOC_DIR)) {
    mkdir(PLUGIN_MONITORING_DOC_DIR);
}

define('_MPDF_TEMP_PATH', PLUGIN_MONITORING_DOC_DIR . '/pdf/');

// Used for use config values in 'cache'
$PM_CONFIG = array();
$PM_EXPORTFOMAT = 'boolean';

// Used to declare Alignak backend managed objects
$PM_ALIGNAK_ELEMENTS = array();

/**
 * Initialize the plugin hooks
 * @return array
 * @throws Exception
 */
function plugin_init_monitoring()
{
    global $PLUGIN_HOOKS, $PM_CONFIG, $PM_ALIGNAK_ELEMENTS;

    define("PLUGIN_MONITORING_SYSTEM", "alignak");

    $PLUGIN_HOOKS['csrf_compliant']['monitoring'] = true;

//   $PLUGIN_HOOKS['change_profile']['monitoring'] = array('PluginMonitoringProfile','changeprofile');

    $Plugin = new Plugin();
    if ($Plugin->isActivated('monitoring')) {

//        include __DIR__ . '/lib/alignak-backend-php-client/src/Client.php';

        // To be completed or stored in a table?
        $PM_ALIGNAK_ELEMENTS = array(
            'livestate' => __('Livestate', 'monitoring'),
            'command' => __('Commands', 'monitoring'),
            'timeperiod' => __('Timeperiods', 'monitoring'),
            'user' => __('Users', 'monitoring'),
            'realm' => __('Realms', 'monitoring'),
            'host' => __('Hosts', 'monitoring'),
            'service' => __('Services', 'monitoring')
        );

        // Classes registration
        Plugin::registerClass('PluginMonitoringEntity',
            array('addtabon' => array('Entity')));
        Plugin::registerClass('PluginMonitoringComponentscatalog',
            array('addtabon' => array('Central')));
        Plugin::registerClass('PluginMonitoringUser',
            array('addtabon' => array('User')));
        Plugin::registerClass('PluginMonitoringDisplayview',
            array('addtabon' => array('Central')));
        Plugin::registerClass('PluginMonitoringHost',
            array('addtabon' => array('Central', 'Computer', 'Device', 'Printer', 'NetworkEquipment')));
        Plugin::registerClass('PluginMonitoringService',
            array('addtabon' => array('Central')));
        Plugin::registerClass('PluginMonitoringProfile',
            array('addtabon' => array('Profile')));
        Plugin::registerClass('PluginMonitoringUnavailability',
            array('addtabon' => array('Computer', 'NetworkEquipment')));
        Plugin::registerClass('PluginMonitoringSystem',
            array('addtabon' => array('Central')));
        Plugin::registerClass('PluginMonitoringHostdailycounter',
            array('addtabon' => array('Computer')));
        Plugin::registerClass('PluginMonitoringServiceevent',
            array('addtabon' => array('Computer')));
        Plugin::registerClass('PluginMonitoringRedirecthome',
            array('addtabon' => array('User')));

        if (class_exists('PluginAppliancesAppliance')) {
            PluginAppliancesAppliance::registerType('PluginMonitoringServicescatalog');
        }

        // Load the plugin configuration
        PluginMonitoringConfig::loadConfiguration();

        $PLUGIN_HOOKS['use_massive_action']['monitoring'] = 1;

        $PLUGIN_HOOKS['add_css']['monitoring'] = array(
            "lib/nvd3/src/nv.d3.css",
            "lib/jqueryplugins/tagbox/css/jquery.tagbox.css",
//         "css/views.css",

//          "css/webui/bootstrap.css",
//          "css/webui/bootstrap-theme.min.css",
//          "css/webui/font-awesome.min.css",
//          "css/webui/alertify.min.css",
//          "css/webui/alertify.bootstrap.min.css",
//          "css/webui/alignak_webui.css",
//          "css/webui/alignak_webui-items.css",
//          "css/webui/datatables.min.css",
//          "css/webui/timeline.css",
        );
        $PLUGIN_HOOKS['add_javascript']['monitoring'] = array(
            "lib/jscolor/jscolor.min.js",
            "lib/jqueryplugins/tagbox/js/jquery.tagbox.min.js",
//          "lib/webui/bootstrap.min.js",
//          "lib/webui/datatables.min.js",
//          "lib/webui/alignak_webui-external.js",
//          "lib/webui/Chart.min.js"
        );

        // Plugin profiles management
        // todo: what is it for ?
        if (isset($_SESSION["glpiactiveprofile"])
            && isset($_SESSION["glpiactiveprofile"]["interface"])
            && $_SESSION["glpiactiveprofile"]["interface"] == "helpdesk") {
            $profile = new Profile();
            if ($profile->getFromDB($_SESSION['glpiactiveprofile']['id'])) {
                $prof = array();
                foreach ($profile->fields as $rname => $right) {
                    if (substr($rname, 0, 18) === 'plugin_monitoring_') {
                        $_SESSION['glpiactiveprofile'][$rname] = $right;
                    }
                }
            }
        }

        $PLUGIN_HOOKS['menu_toadd']['monitoring'] = array('plugins' => 'PluginMonitoringDashboard');
        if (Session::haveRight("plugin_monitoring_dashboard", READ)) {
            $PLUGIN_HOOKS["helpdesk_menu_entry"]['monitoring'] = '/front/dashboard.php';
        }

//        $PLUGIN_HOOKS['config_page']['monitoring'] = 'front/config.form.php';
//        $PLUGIN_HOOKS['submenu_entry']['monitoring']['config'] = 'front/config.form.php';
        // Display a menu entry ?
        if (Session::haveRight('config', UPDATE)) {
            // Configuration page
            $PLUGIN_HOOKS['config_page']['monitoring'] = 'config.php';

            // Add an entry to the Administration menu
            if (Session::haveRight('plugin_monitoring_menu', READ)) {
                $PLUGIN_HOOKS['menu_toadd']['monitoring'] = [
                    'admin' => 'PluginMonitoringMenu',
                    'config' => 'PluginMonitoringMenu'];
            }

            // No menu when on simplified interface
            $PLUGIN_HOOKS["helpdesk_menu_entry"]['monitoring'] = false;
        }


        // Tabs for each type
        $PLUGIN_HOOKS['headings']['monitoring'] = 'plugin_get_headings_monitoring';
        $PLUGIN_HOOKS['headings_action']['monitoring'] = 'plugin_headings_actions_monitoring';

        // Icons add, search...
        // Still useful to declare all that stuff ? Menu is ok without this ...
        $PLUGIN_HOOKS['submenu_entry']['monitoring']['add']['componentscatalog'] = 'front/componentscatalog.form.php?add=1';
        $PLUGIN_HOOKS['submenu_entry']['monitoring']['search']['componentscatalog'] = 'front/componentscatalog.php';

        $PLUGIN_HOOKS['submenu_entry']['monitoring']['add']['displayview'] = 'front/displayview.form.php?add=1';
        $PLUGIN_HOOKS['submenu_entry']['monitoring']['search']['displayview'] = 'front/displayview.php';

        $PLUGIN_HOOKS['submenu_entry']['monitoring']['add']['PluginMonitoringRealm'] = 'front/realm.form.php?add=1';
        $PLUGIN_HOOKS['submenu_entry']['monitoring']['search']['PluginMonitoringRealm'] = 'front/realm.php';

        $PLUGIN_HOOKS['submenu_entry']['monitoring']['add']['weathermap'] = 'front/weathermap.form.php?add=1';
        $PLUGIN_HOOKS['submenu_entry']['monitoring']['search']['weathermap'] = 'front/weathermap.php';

        $PLUGIN_HOOKS['submenu_entry']['monitoring']['add']['perfdata'] = 'front/perfdata.form.php?add=1';
        $PLUGIN_HOOKS['submenu_entry']['monitoring']['search']['perfdata'] = 'front/perfdata.php';

        $PLUGIN_HOOKS['submenu_entry']['monitoring']['add']['customitem_gauge'] = 'front/customitem_gauge.form.php?add=1';
        $PLUGIN_HOOKS['submenu_entry']['monitoring']['search']['customitem_gauge'] = 'front/customitem_gauge.php';

        $PLUGIN_HOOKS['submenu_entry']['monitoring']['add']['customitem_counter'] = 'front/customitem_counter.form.php?add=1';
        $PLUGIN_HOOKS['submenu_entry']['monitoring']['search']['customitem_counter'] = 'front/customitem_counter.php';

        $PLUGIN_HOOKS['submenu_entry']['monitoring']['search']['service'] = 'front/display.php';
        $PLUGIN_HOOKS['submenu_entry']['monitoring']['search']['service'] = 'front/host.php';

//        if (isset($_SESSION["glpiname"])) {
//            // Fil ariane
//            // Still useful to declare all that stuff ? Menu is ok without this ...
//            $PLUGIN_HOOKS['submenu_entry']['monitoring']['options']['componentscatalog']['title'] = __('Components catalog', 'monitoring');
//            $PLUGIN_HOOKS['submenu_entry']['monitoring']['options']['componentscatalog']['page'] = '/plugins/monitoring/front/componentscatalog.php';
//
//            $PLUGIN_HOOKS['submenu_entry']['monitoring']['options']['display']['title'] = __('Dashboard', 'monitoring');
//            $PLUGIN_HOOKS['submenu_entry']['monitoring']['options']['display']['page'] = '/plugins/monitoring/front/display_servicescatalog.php';
//
//            $PLUGIN_HOOKS['submenu_entry']['monitoring']['options']['displayview']['title'] = __('Views', 'monitoring');
//            $PLUGIN_HOOKS['submenu_entry']['monitoring']['options']['displayview']['page'] = '/plugins/monitoring/front/displayview.php';
//
//            $PLUGIN_HOOKS['submenu_entry']['monitoring']['options']['PluginMonitoringRealm']['title'] = __('Realms', 'monitoring');
//            $PLUGIN_HOOKS['submenu_entry']['monitoring']['options']['PluginMonitoringRealm']['page'] = '/plugins/monitoring/front/realm.php';
//
//            $PLUGIN_HOOKS['submenu_entry']['monitoring']['options']['weathermap']['title'] = __('Weathermap', 'monitoring');
//            $PLUGIN_HOOKS['submenu_entry']['monitoring']['options']['weathermap']['page'] = '/plugins/monitoring/front/weathermap.php';
//
//            $PLUGIN_HOOKS['submenu_entry']['monitoring']['options']['hostdailycounter']['title'] = __('Host daily counters', 'monitoring');
//            $PLUGIN_HOOKS['submenu_entry']['monitoring']['options']['hostdailycounter']['page'] = '/plugins/monitoring/front/hostdailycounter.php';
//
//            $PLUGIN_HOOKS['submenu_entry']['monitoring']['options']['unavailability']['title'] = __('Unavailabilities', 'monitoring');
//            $PLUGIN_HOOKS['submenu_entry']['monitoring']['options']['unavailability']['page'] = '/plugins/monitoring/front/unavailability.php';
//
//            $PLUGIN_HOOKS['submenu_entry']['monitoring']['options']['perfdata']['title'] = __('Graph templates', 'monitoring');
//            $PLUGIN_HOOKS['submenu_entry']['monitoring']['options']['perfdata']['page'] = '/plugins/monitoring/front/perfdata.php';
//
//            $PLUGIN_HOOKS['submenu_entry']['monitoring']['options']['dashboard']['title'] = __('Dashboard', 'monitoring');
//            $PLUGIN_HOOKS['submenu_entry']['monitoring']['options']['dashboard']['page'] = '/plugins/monitoring/front/dashboard.php';
//        }

        $rule_check = array('PluginMonitoringComponentscatalog_rule', 'isThisItemCheckRule');
        $rule_check_networkport = array('PluginMonitoringComponentscatalog_rule', 'isThisItemCheckRuleNetworkport');
        $PLUGIN_HOOKS['item_add']['monitoring'] =
            array('Computer' => $rule_check,
                'NetworkEquipment' => $rule_check,
                'Printer' => $rule_check,
                'Peripheral' => $rule_check,
                'Phone' => $rule_check,
                'PluginMonitoringNetworkport' => $rule_check_networkport,
                'PluginMonitoringComponentscatalog_rule' =>
                    array('PluginMonitoringComponentscatalog_rule', 'getItemsDynamicly'),
                'PluginMonitoringComponentscatalog_Host' =>
                    array('PluginMonitoringHost', 'addHost'));

        $PLUGIN_HOOKS['item_update']['monitoring'] =
            array('Computer' => $rule_check,
                'NetworkEquipment' => $rule_check,
                'Printer' => $rule_check,
                'Peripheral' => $rule_check,
                'Phone' => $rule_check,
                'PluginMonitoringComponentscatalog' =>
                    array('PluginMonitoringComponentscatalog', 'replayRulesCatalog'),
                'PluginMonitoringComponentscatalog_rule' =>
                    array('PluginMonitoringComponentscatalog_rule', 'getItemsDynamicly'));

        $PLUGIN_HOOKS['item_purge']['monitoring'] =
            array('Computer' => $rule_check,
                'NetworkEquipment' => $rule_check,
                'Printer' => $rule_check,
                'Peripheral' => $rule_check,
                'Phone' => $rule_check,
                'NetworkPort' => array('PluginMonitoringNetworkport', 'deleteNetworkPort'),
                'PluginMonitoringNetworkport' => $rule_check_networkport,
                'PluginMonitoringComponentscatalog_rule' =>
                    array('PluginMonitoringComponentscatalog_rule', 'getItemsDynamicly'),
                'PluginMonitoringComponentscatalog_Host' =>
                    array('PluginMonitoringComponentscatalog_Host', 'unlinkComponentsToItem'),
                'PluginMonitoringComponentscatalog' =>
                    array('PluginMonitoringComponentscatalog', 'removeCatalog'),
                'PluginMonitoringBusinessrulegroup' =>
                    array('PluginMonitoringBusinessrule', 'removeBusinessruleonDeletegroup'));

        if (!isset($_SESSION['glpi_plugin_monitoring']['_refresh'])) {
            $_SESSION['glpi_plugin_monitoring']['_refresh'] = '60';
        }
        $PLUGIN_HOOKS['post_init']['monitoring'] = 'plugin_monitoring_postinit';
        $PLUGIN_HOOKS['webservices']['monitoring'] = 'plugin_monitoring_registerMethods';

        if (!isset($PM_CONFIG['alignak_webui_url'])) {
            $pmConfig = new PluginMonitoringConfig();
            $pmConfig->load_alignak_url();
        }

//        if (isset($_SESSION['glpiID'])
//            && (strpos($_SERVER['PHP_SELF'], "alignak_element.php")
//                || strpos($_SERVER['PHP_SELF'], "dashboard.php"))) {
//            $url_js = $PM_CONFIG['alignak_webui_url'] . "/external/files/js_list";
//            $url_css = $PM_CONFIG['alignak_webui_url'] . "/external/files/css_list";
//
//            $abc = new Alignak_Backend_Client($PM_CONFIG['alignak_backend_url']);
//            $token = PluginMonitoringUser::myToken($abc);
//            if ($token != '') {
//                echo '<script>
//    var request = new XMLHttpRequest();
//    request.open("GET", "' . $url_js . '", false);
//    request.setRequestHeader("Authorization", "Basic " + btoa("' . $token . ':"));
//    request.send();
//    var answer = JSON.parse(request.responseText);
//    var list_js = answer["files"];
//    var arrayLength = list_js.length;
//    for (var i = 0; i < arrayLength; i++) {
//       if (list_js[i] != "/static/js/jquery-1.12.0.min.js") {
//          var x = document.createElement("script");
//          x.setAttribute("type", "text/javascript");
//          x.src = "' . $PM_CONFIG['alignak_webui_url'] . '" + list_js[i];
//          document.getElementsByTagName("head")[0].appendChild(x);
//       }
//    }
//  </script>';
//
//                echo '<script>
//    var request = new XMLHttpRequest();
//    request.open("GET", "' . $url_css . '", false);
//    request.setRequestHeader("Authorization", "Basic " + btoa("' . $token . ':"));
//    request.send();
//    var answer = JSON.parse(request.responseText);
//    var arrayLength = answer["files"].length;
//    for (var i = 0; i < arrayLength; i++) {
//       var x = document.createElement("link");
//       x.setAttribute("rel", "stylesheet");
//       x.setAttribute("type", "text/css");
//       x.setAttribute("href", "' . $PM_CONFIG['alignak_webui_url'] . '" + answer["files"][i]);
//       document.getElementsByTagName("head")[0].appendChild(x);
//    }
//    var glpi_css = ["/plugins/monitoring/css/views.css"];
//    var arrayLength = glpi_css.length;
//    for (var i = 0; i < arrayLength; i++) {
//       var x = document.createElement("link");
//       x.setAttribute("rel", "stylesheet");
//       x.setAttribute("type", "text/css");
//       x.setAttribute("href", "' . $CFG_GLPI["root_doc"] . '" + glpi_css[i]);
//       document.getElementsByTagName("head")[0].appendChild(x);
//    }
//
//    </script>';
//                $pmWebui = new PluginMonitoringWebui();
//                $pmWebui->authentication($abc->token);
//            }
//        }
    }
    return $PLUGIN_HOOKS;
}

// Name and Version of the plugin
function plugin_version_monitoring()
{
    // Use requirements (Glpi > 9.2)
    return [
        'name' => 'Alignak monitoring plugin',
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

    $version = rtrim(GLPI_VERSION, '-dev');
    if (version_compare($version, PLUGIN_MONITORING_GLPI_MIN_VERSION, 'lt')) {
        echo __('This plugin requires GLPI ' . PLUGIN_MONITORING_GLPI_MIN_VERSION, 'monitoring');

        return false;
    }

    return true;
}


function plugin_monitoring_check_config()
{
    return true;
}


function plugin_monitoring_haveTypeRight($type, $right)
{
    return true;
}

