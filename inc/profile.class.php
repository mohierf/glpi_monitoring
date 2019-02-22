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

class PluginMonitoringProfile extends Profile
{
    // Specific rights
    const HOMEPAGE = 1024;
    const DASHBOARD = 2048;


    /**
     * The right name for this class
     *
     * @var string
     */
    static $rightname = "config";


    /**
     * Get the tab name used for item
     *
     * @param  CommonGLPI $item      the item object
     * @param  integer $withtemplate 1 if it is a template form
     *
     * @return string name of the tab
     */
    function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        return self::createTabEntry('Monitoring');
    }


    /**
     * show Tab content
     *
     * @param CommonGLPI $item      Item on which the tab need to be displayed
     * @param integer $tabnum       tab number (default 1)
     * @param integer $withtemplate is a template object ? (default 0)
     *
     * @return boolean
     **/
    static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        /* @var CommonDBTM $item */
        if ($item->getType() == 'Profile') {
            $self_service = ($item->getField('interface') != 'central');

            $pmProfile = new self();
            $pmProfile->showForm($item->getID(), true, true, $self_service);
        }
        return true;
    }


    /**
     * Delete profiles
     */
    static function uninstallProfile()
    {
        $pmProfile = new self();
        PluginMonitoringToolbox::log("Removing plugin rights from the database");
        foreach ($pmProfile->getAllRights() as $data) {
            ProfileRight::deleteProfileRights([$data['field']]);
        }
    }


    /**
     * Get all rights
     *
     * @param $self_service : true if currently using the self-service profile
     *
     * @return array
     */
    function getAllRights($self_service = false)
    {
        $a_rights = [];
        $a_rights = array_merge($a_rights, $this->getRightsGeneral($self_service));
        $a_rights = array_merge($a_rights, $this->getRightsMonitoring($self_service));
        return $a_rights;
    }


    /**
     * Get general rights
     * - plugin_monitoring_central: display information on the central page
     *
     * @param bool $self_service
     *
     * @return array
     */
    function getRightsGeneral($self_service = false)
    {
        $rights = [
            ['rights' => [READ => __('Read')],
                'label' => __('Central page', 'monitoring'),
                'field' => 'plugin_monitoring_central'],
        ];
        if (!$self_service) {
            // Add a menu in the Administration menu
            $rights[] = [
                'rights' => [READ => __('Read')],
                'label' => __('Menu', 'monitoring'),
                'field' => 'plugin_monitoring_menu'
            ];

            $rights[] = [
                'rights' => [READ => __('Read'), UPDATE => __('Update')],
                'itemtype' => 'PluginMonitoringConfig',
                'label' => __('Configuration', 'monitoring'),
                'field' => 'plugin_monitoring_configuration'
            ];

            $rights[] = [
                'rights' => [self::DASHBOARD => __('Dashboard')],
                'itemtype' => 'PluginMonitoringDashboard',
                'label' => __('Dashboards', 'monitoring'),
                'field' => 'plugin_monitoring_dashboard'
            ];
        }

        return $rights;
    }

    /**
     * Get rights for the plugin monitoring features
     *
     * @param bool $self_service
     *
     * @return array
     */
    function getRightsMonitoring($self_service = false)
    {
        $rights = [
            ['rights' => [READ => __('Read')],
                'label' => __('Dashboard', 'monitoring'),
                'field' => 'plugin_monitoring_dashboard'
            ],
            ['rights' => [READ => __('Read')],
                'label' => __('Homepage', 'monitoring'),
                'field' => 'plugin_monitoring_homepage'
            ],

            ['itemtype' => 'PluginMonitoringAcknowledge',
                'label' => __('Acknowledge', 'monitoring'),
                'field' => 'plugin_monitoring_acknowledge'
            ],
            ['itemtype' => 'PluginMonitoringDowntime',
                'label' => __('Downtime', 'monitoring'),
                'field' => 'plugin_monitoring_downtime'
            ],
            ['itemtype' => 'PluginMonitoringDisplayview',
                'label' => __('Views', 'monitoring'),
                'field' => 'plugin_monitoring_displayview'
            ],
//            ['itemtype' => 'PluginMonitoringSlider',
//                'label' => __('Slider', 'monitoring'),
//                'field' => 'plugin_monitoring_slider'
//            ],
//            ['itemtype' => 'PluginMonitoringServicescatalog',
//                'label' => __('Services catalog', 'monitoring'),
//                'field' => 'plugin_monitoring_servicescatalog'
//            ],
            ['itemtype' => 'PluginMonitoringComponentscatalog',
                'label' => __('Components catalog', 'monitoring'),
                'field' => 'plugin_monitoring_componentscatalog'
            ],
            ['itemtype' => 'PluginMonitoringComponent',
                'label' => __('Component', 'monitoring'),
                'field' => 'plugin_monitoring_component'
            ],
            ['itemtype' => 'PluginMonitoringContacttemplate',
                'label' => __('Contacts', 'monitoring'),
                'field' => 'plugin_monitoring_contact'
            ],
            ['itemtype' => 'PluginMonitoringHostnotificationtemplate',
                'label' => __('Notifications', 'monitoring'),
                'field' => 'plugin_monitoring_notification'
            ],
            ['itemtype' => 'PluginMonitoringServicenotificationtemplate',
                'label' => __('Notifications', 'monitoring'),
                'field' => 'plugin_monitoring_notification'
            ],
            ['itemtype' => 'PluginMonitoringCommand',
                'label' => __('Command', 'monitoring'),
                'field' => 'plugin_monitoring_command'
            ],
//            ['itemtype' => 'PluginMonitoringPerfdata',
//                'label' => __('Performance data', 'monitoring'),
//                'field' => 'plugin_monitoring_perfdata'
//            ],
//            ['itemtype' => 'PluginMonitoringEventhandler',
//                'label' => __('Event handler', 'monitoring'),
//                'field' => 'plugin_monitoring_eventhandler'
//            ],
            ['itemtype' => 'PluginMonitoringRealm',
                'label' => __('Reamls', 'monitoring'),
                'field' => 'plugin_monitoring_realm'
            ],
            ['itemtype' => 'PluginMonitoringTag',
                'label' => __('Tag', 'monitoring'),
                'field' => 'plugin_monitoring_tag'
            ],
            ['rights' => [UPDATE => __('Update'), CREATE => __('Create')],
                'label' => __('Host configuration', 'monitoring'),
                'field' => 'plugin_monitoring_hostconfig'
            ],
            ['rights' => [CREATE => __('Create')],
                'label' => __('Restart Shinken', 'monitoring'),
                'field' => 'plugin_monitoring_command_fmwk'
            ],
            ['itemtype' => 'PluginMonitoringService',
                'label' => __('Services (ressources)', 'monitoring'),
                'field' => 'plugin_monitoring_service'
            ],
            ['rights' => [self::DASHBOARD => __('Dashboard')],
                'itemtype' => 'PluginMonitoringSystem',
                'label' => __('System status', 'monitoring'),
                'field' => 'plugin_monitoring_systemstatus'
            ],
            ['rights' => [self::DASHBOARD => __('Dashboard')],
                'itemtype' => 'PluginMonitoringHost',
                'label' => __('Host status', 'monitoring'),
                'field' => 'plugin_monitoring_hoststatus'
            ],
            ['rights' => [CREATE => __('Create')],
                'label' => __('Host actions', 'monitoring'),
                'field' => 'plugin_monitoring_host_actions'
            ],
        ];
        return $rights;
    }


    /**
     * Display profile form
     *
     * @param  integer $profiles_id
     * @param  boolean $openform
     * @param  boolean $closeform
     * @param  boolean $self_service : true if the profile is the self-service profile
     *
     * @return true
     */
    function showForm($profiles_id = 0, $openform = true, $closeform = true, $self_service = false)
    {
        echo "<div class='firstbloc $self_service'>";
        if (($canedit = Session::haveRightsOr(self::$rightname, [CREATE, UPDATE, PURGE])) && $openform) {
            $profile = new Profile();
            echo "<form method='post' action='" . $profile->getFormURL() . "'>";
        }

        $profile = new Profile();
        $profile->getFromDB($profiles_id);

        $rights = $this->getRightsGeneral($self_service);
        if (!empty($rights)) {
            $profile->displayRightsChoiceMatrix(
                $rights, ['canedit' => $canedit,
                    'default_class' => 'tab_bg_2',
                    'title' => __('General', 'monitoring')]
            );
        }

        $rights = $this->getRightsMonitoring($self_service);
        if (!empty($rights)) {
            $profile->displayRightsChoiceMatrix(
                $rights, ['canedit' => $canedit,
                    'default_class' => 'tab_bg_2',
                    'title' => __('Monitoring system', 'monitoring')]
            );
        }

        if ($canedit && $closeform) {
            echo '<div class="center">';
            echo Html::hidden('id', ['value' => $profiles_id]);
            echo Html::submit(_sx('button', 'Save'), ['name' => 'update']);
            echo '</div>';
            Html::closeForm();
        }
        echo '</div>';

        $this->showLegend();
        return true;
    }


    /**
     * Delete rights stored in the session
     */
    static function removeRightsFromSession()
    {
        // Get current profile
        $profile = new self();
        PluginMonitoringToolbox::log("Removing plugin rights from the session\n");
        foreach ($profile->getAllRights() as $right) {
            if (isset($_SESSION['glpiactiveprofile'][$right['field']])) {
                unset($_SESSION['glpiactiveprofile'][$right['field']]);
            }
        }
    }


    /**
     * Init profiles during installation:
     * - add rights in profile table for the current user's profile
     * - current profile has all rights on the plugin
     */
    static function initProfile()
    {
        PluginMonitoringToolbox::log("Initializing plugin profile rights:\n");
        // Add all plugin rights to the current user profile
        if (isset($_SESSION['glpiactiveprofile']) && isset($_SESSION['glpiactiveprofile']['id'])) {
            // Set the plugin profile rights for the currently used profile
            self::createFirstAccess($_SESSION['glpiactiveprofile']['id']);
        } else {
            // No current profile!
            PluginMonitoringToolbox::log("No current profile in the session!\n");
        }
    }


    /**
     * Create first access (so default profile)
     *
     * @param integer $profiles_id id of the profile
     */
    static function createFirstAccess($profiles_id)
    {
        include_once PLUGIN_MONITORING_DIR . "/inc/profile.class.php";
        $profile = new self();
        foreach ($profile->getAllRights() as $right) {
            self::addDefaultProfileInfos($profiles_id, [$right['field'] => ALLSTANDARDRIGHT]);
        }
    }


    /**
     * Add the default profile rights
     *
     * @param integer $profiles_id
     * @param array $rights
     */
    static function addDefaultProfileInfos($profiles_id, $rights)
    {
        $profileRight = new ProfileRight();

        // Get current profile
        $profile = new Profile();
        $profile->getFromDB($profiles_id);
        PluginMonitoringToolbox::log("Add default rights for the profile: {$profile->getName()}\n");

        foreach ($rights as $right => $value) {
            // If it does not yet exists...
            if ($profileRight->getFromDBByCrit(["WHERE" => "`profiles_id`='$profiles_id' AND `name`='$right'"])) {
                // Update the profile right
                $myright['rights'] = $value;
                $profileRight->update($myright);
                PluginMonitoringToolbox::log("- updating: $right = $value\n");
            } else {
                // Create the profile right
                $myright['profiles_id'] = $profiles_id;
                $myright['name'] = $right;
                $myright['rights'] = $value;
                $profileRight->add($myright);
                PluginMonitoringToolbox::log("- added: $right = $value\n");
            }

            // Update right in the current session
            $_SESSION['glpiactiveprofile'][$right] = $value;
        }
    }
}
