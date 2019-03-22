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

class PluginMonitoringMenu extends CommonGLPI
{

    static $rightname = 'plugin_monitoring_configuration';

    /**
     * Get menu name
     *
     * @return string - menu name
     **/
    static function getMenuName()
    {
        return __("Monitoring", "monitoring");
    }


    /**
     * get menu content
     *
     * Do not use this function if you intend to have some breadcrumbs menu for the plugin objects
     * This function is onlyt interesting for some extra items in the Glpi main menu
     *
     * @return array for menu content
     **/

    /**
     * Get additional menu options and breadcrumb
     *
     * @global array $CFG_GLPI
     * @return array
     */
    static function getAdditionalMenuOptions()
    {
        global $PLUGIN_HOOKS, $CFG_GLPI;

        $elements = [
            'config' => 'PluginMonitoringConfig',
            'realm' => 'PluginMonitoringRealm',
            'tag' => 'PluginMonitoringTag',
            'check' => 'PluginMonitoringCheck',
            'command' => 'PluginMonitoringCommand',
            'eventhandler' => 'PluginMonitoringEventhandler',
            'notificationcommand' => 'PluginMonitoringNotificationcommand',

            'host_template' => 'PluginMonitoringHosttemplate',
            'contact_template' => 'PluginMonitoringContacttemplate',
            'hn_template' => 'PluginMonitoringHostnotificationtemplate',
            'sn_template' => 'PluginMonitoringServicenotificationtemplate',

            'dashboard' => 'PluginMonitoringDashboard',

            'entity' => 'PluginMonitoringEntity',
            'host' => 'PluginMonitoringHost',
            'service' => 'PluginMonitoringService',
            'component' => 'PluginMonitoringComponent',

            'componentscatalog' => 'PluginMonitoringComponentscatalog'
//            'mail_notification' => 'PluginMonitoringMailNotification',
//            'monitoring_template' => 'PluginMonitoringMonitoringTemplate',
//            'computer_counters_template' => 'PluginMonitoringCountersTemplate',
//            'counters_template' => 'PluginMonitoringCountersTemplate',
//            'counter' => 'PluginMonitoringCounter'
        ];

        // List of the elements which must have some breadcrumb items
        $options = [];

        $options['menu']['title'] = self::getTypeName();
        $options['menu']['page'] = self::getSearchURL(false);
        if (Session::haveRight('plugin_monitoring_configuration', READ)) {
            $options['menu']['links']['config'] = "/plugins/monitoring/" . $PLUGIN_HOOKS['config_page']['monitoring'];
        }
        foreach ($elements as $type => $itemtype) {
            $options[$type]['title'] = $itemtype::getTypeName();
            $options[$type]['page'] = $itemtype::getSearchURL(false);
            $options[$type]['links']['search'] = $itemtype::getSearchURL(false);
            if ($itemtype::canCreate()) {
                $options[$type]['links']['add'] = $itemtype::getFormURL(false);
            }
            if (Session::haveRight('plugin_monitoring_configuration', UPDATE)) {
                $options[$type]['links']['config'] = PluginMonitoringConfig::getFormURL(false);
            }
        }
        // hack for config
        $options['config']['page'] = PluginMonitoringConfig::getFormURL(false);

        // Add icon for documentation
        $img = Html::image($CFG_GLPI["root_doc"] . "/plugins/monitoring/pics/books.png",
            ['alt' => __('Import', 'alignak')]);
        $options['menu']['links'][$img] = '/plugins/monitoring/front/documentation.php';

        PluginMonitoringToolbox::logIfDebug("getAdditionalMenuOptions, " . print_r($options, true) . "\n");
        return $options;
    }
}

