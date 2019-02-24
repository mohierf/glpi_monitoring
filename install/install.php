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

class PluginMonitoringInstall
{
    /* @var Migration $migration */
    protected $migration;

    /**
     * From the very smart form_creator plugin upgrade process! Thanks ;)
     * ---------------
     * array of upgrade steps key => value
     * key   is the version to upgrade from
     * value is the version to upgrade to
     *
     * Exemple: an entry '2.0' => '2.1' tells that versions 2.0
     * are upgradable to 2.1
     *
     * The key version is located in the glpi_plugin_configs table
     *
     * @var array
     */
    private $upgradeSteps = [
        '9_3_0_1_dev'    => '9.3+0.1'
    ];

    /**
     * Install the plugin
     *
     * @param Migration $migration
     *
     * @return boolean
     */
    public function install(Migration $migration)
    {
        $this->migration = $migration;
        $_SESSION['plugin_monitoring']['installation'] = true;

        // Drop existing tables if some exist
        $this->dropTables();

        $this->installSchema();
        $this->migrateInnoDb();

        $this->createProfile();

        $this->createFiles();

        $this->createItems();

        $this->createCronTasks();

        $this->createDefaultDisplayPreferences();

        Config::setConfigurationValues('monitoring', [
            'schema_version' => PLUGIN_MONITORING_VERSION]);

        unset($_SESSION['plugin_monitoring']['installation']);

        return true;
    }

    /**
     * Upgrade the plugin
     *
     * @param Migration $migration
     *
     * @return bool
     */
    public function upgrade(Migration $migration) {
        $_SESSION['plugin_monitoring']['installation'] = true;

        $this->migration = $migration;
        if (isset($_SESSION['plugin_monitoring']['cli'])
            and $_SESSION['plugin_monitoring']['cli'] == 'force-upgrade') {
            // Might return false
            $fromSchemaVersion = array_search(PLUGIN_MONITORING_VERSION, $this->upgradeSteps);
        } else {
            $fromSchemaVersion = $this->getSchemaVersion();
        }
        $version = str_replace('+', '_', $fromSchemaVersion);
        $version = str_replace('.', '_', $version);
        $version = str_replace('-', '_', $version);

        $this->migration->displayMessage("From: $version");
        while ($fromSchemaVersion && isset($this->upgradeSteps[$version])) {
            $this->upgradeOneStep($this->upgradeSteps[$version]);
            $fromSchemaVersion = $this->upgradeSteps[$fromSchemaVersion];
        }

        $this->migration->executeMigration();

        // if the schema contains new tables
        $this->installSchema();
        $this->createDefaultDisplayPreferences();
        $this->createCronTasks();
        Config::setConfigurationValues('monitoring', [
            'schema_version' => PLUGIN_MONITORING_VERSION]);

        unset($_SESSION['plugin_monitoring']['installation']);

        return true;
    }

    /**
     * Proceed to upgrade of the plugin to the given version
     *
     * @param string $toVersion
     */
    protected function upgradeOneStep($toVersion) {
        ini_set("max_execution_time", "0");
        ini_set("memory_limit", "-1");

        $this->migration->displayMessage("Request an upgrade to version: " . $toVersion);
        $toVersion = str_replace('+', '_', $toVersion);
        $toVersion = str_replace('.', '_', $toVersion);
        $toVersion = str_replace('-', '_', $toVersion);
        $includeFile = __DIR__ . "/upgrade_to_$toVersion.php";
        $this->migration->displayMessage("Include file: ". $includeFile);
        if (is_readable($includeFile) && is_file($includeFile)) {
            include_once $includeFile;
            $updateClass = "PluginFormcreatorUpgradeTo$toVersion";
            $this->migration->addNewMessageArea("Upgrade to $toVersion");
            $upgradeStep = new $updateClass();
            $upgradeStep->upgrade($this->migration);
            $this->migration->executeMigration();
            $this->migration->displayMessage('Done');
        }
    }

    /**
     * Upgrade the plugin
     *
     * @param Migration $migration
     *
     * @return boolean
     */
    public function upgrade_old(Migration $migration)
    {
        $this->migration = $migration;
        $fromSchemaVersion = $this->getSchemaVersion();

        $_SESSION['plugin_monitoring']['installation'] = true;

        $this->installSchema();

        // All cases are run starting from the one matching the current schema version
        switch ($fromSchemaVersion) {
            case '0.0':
            case '1.0':
                // Any schema version below or equal 1.0
//                require_once(__DIR__ . '/update_0.0_1.0.php');
//                plugin_alignak_update_1_0($this->migration);
                break;

            case '9.3+0.1-dev':
                // From the very first installed version
//                require_once(__DIR__ . '/update_0.0_1.0.php');
//                plugin_alignak_update_1_0($this->migration);
                break;

            default:
                // Must be the last case
                if ($this->endsWith(PLUGIN_MONITORING_VERSION, "-dev")) {
                    if (is_readable(__DIR__ . "/update_dev.php") && is_file(__DIR__ . "/update_dev.php")) {
                        include_once __DIR__ . "/update_dev.php";
                        $updateDevFunction = 'plugin_alignak_update_dev';
                        if (function_exists($updateDevFunction)) {
                            $updateDevFunction($this->migration);
                        }
                    }
                }
        }
        $this->migration->executeMigration();

        $this->createCronTasks();
        Config::setConfigurationValues('monitoring', ['schema_version' => PLUGIN_MONITORING_VERSION]);

        unset($_SESSION['plugin_monitoring']['installation']);

        return true;
    }

    /**
     * Find the version of the plugin
     *
     * @return string|null
     */
    public function getSchemaVersion()
    {
        if ($this->isPluginInstalled()) {
            return $this->getSchemaVersionFromGlpiConfig();
        }

        return null;
    }

    /**
     * Find version of the plugin in GLPI configuration
     *
     * @return string
     */
    protected function getSchemaVersionFromGlpiConfig()
    {
        $config = Config::getConfigurationValues('monitoring', ['schema_version']);
        if (!isset($config['schema_version'])) {
            // No schema version in GLPI config, then this is an old version...
            return '0.0';
        }

        // Version found in GLPI config
        return $config['schema_version'];
    }

    /**
     * is the plugin already installed ?
     *
     * @return boolean
     */
    public function isPluginInstalled()
    {
        global $DB;

        $result = $DB->query("SHOW TABLES LIKE 'glpi_plugin_monitoring_%'");
        if ($result and $DB->numrows($result) > 0) {
            return true;
        }

        return false;
    }

    /**
     * Install the DB schea for the plugin
     */
    protected function installSchema()
    {
        global $DB;

        $this->migration->displayMessage("Creating database schema");

        $dbFile = __DIR__ . '/mysql/plugin_monitoring-empty.sql';
        if (!$DB->runFile($dbFile)) {
            $this->migration->displayWarning("Error creating tables : " . $DB->error(), true);
            die('Giving up!');
        }
    }

    /**
     * http://stackoverflow.com/questions/834303/startswith-and-endswith-functions-in-php
     * @param string $haystack
     * @param string $needle
     *
     * @return boolean
     */
    protected function endsWith($haystack, $needle)
    {
        // search foreward starting from end minus needle length characters
        return $needle === '' || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== false);
    }

    /**
     * @param bool $drop_tables
     */
    public function uninstall($drop_tables = false)
    {
        $config = new Config();
        $config->deleteByCriteria(['context' => 'alignak']);

        // Clean display preferences
        $pref = new DisplayPreference;
        $pref->deleteByCriteria(['itemtype' => ['LIKE', 'PluginAlignak%']]);

        $this->cleanProfile();

        if ($drop_tables) {
            $this->dropTables();
        }
    }

    /**
     * Drop all the plugin tables
     */
    protected function dropTables()
    {
        global $DB;

        PluginMonitoringToolbox::log("Dropping the plugin tables:");

        // Drop tables of the plugin if they exist
        $result = $DB->query("SHOW TABLES LIKE 'glpi_plugin_monitoring_%'");
        while ($data = $DB->fetch_array($result)) {
            PluginMonitoringToolbox::log("- dropping: {$data[0]}");
            $DB->query("DROP TABLE " . $data[0] . " ");
        }
    }

    /**
     * Create files and directories
     */
    protected function createFiles()
    {
        $this->migration->displayMessage("Creating directories and files");

        if (!is_dir(PLUGIN_MONITORING_DOC_DIR)) {
            mkdir(PLUGIN_MONITORING_DOC_DIR);
        }
        if (!is_dir(PLUGIN_MONITORING_DOC_DIR . '/templates')) {
            mkdir(PLUGIN_MONITORING_DOC_DIR . "/templates");
        }
        if (!is_dir(PLUGIN_MONITORING_DOC_DIR . '/configuration_files')) {
            mkdir(PLUGIN_MONITORING_DOC_DIR . "/configuration_files");
        }
//        if (!is_dir(PLUGIN_MONITORING_DOC_DIR . '/weathermapbg')) {
//            mkdir(PLUGIN_MONITORING_DOC_DIR . "/weathermapbg");
//        }
    }

    /**
     * Create database items:
     * - users,
     * - calendars,
     * - ...
     */
    protected function createItems()
    {
        $this->migration->displayMessage("Creating database items:");

        $user = new User();
        if (!$user->getFromDBByCrit(['name' => "monitoring"])) {
            $this->migration->displayMessage("- monitoring user");
            $input = [];
            $input['name'] = 'monitoring';
            $input['comment'] = 'Created by the monitoring plugin';
            $user->add($input);
        } else {
            $this->migration->displayMessage("- monitoring user is still existing");
        }

        $calendar = new Calendar();
        if (!$calendar->getFromDBByCrit(['name' => "24x7"])) {
            $this->migration->displayMessage("- calendar 24x7");
            $input = [];
            $input['name'] = "24x7";
            $input['comment'] = "Always (24 hours a day, seven days a week)\nCreated by the monitoring plugin";
            $input['is_recursive'] = 1;
            $calendars_id = $calendar->add($input);

            $calendarSegment = new CalendarSegment();
            $input = [];
            $input['calendars_id'] = $calendars_id;
            $input['is_recursive'] = 1;
            $input['begin'] = '00:00:00';
            $input['end'] = '24:00:00';
            $input['day'] = '0';
            $calendarSegment->add($input);
            $input['day'] = '1';
            $calendarSegment->add($input);
            $input['day'] = '2';
            $calendarSegment->add($input);
            $input['day'] = '3';
            $calendarSegment->add($input);
            $input['day'] = '4';
            $calendarSegment->add($input);
            $input['day'] = '5';
            $calendarSegment->add($input);
            $input['day'] = '6';
            $calendarSegment->add($input);
        } else {
            $this->migration->displayMessage("- calendar 24x7 is still existing");
        }

        $calendar = new Calendar();
        if (!$calendar->getFromDBByCrit(['name' => "monitoring-default"])) {
            $this->migration->displayMessage("- calendar monitoring-default");
            $input = [];
            $input['name'] = "monitoring-default";
            $input['comment'] = "Default monitoring check period (Every day, 08:00 - 20:00))\nCreated by the monitoring plugin";
            $input['is_recursive'] = 1;
            $calendars_id = $calendar->add($input);

            $calendarSegment = new CalendarSegment();
            $input = [];
            $input['calendars_id'] = $calendars_id;
            $input['is_recursive'] = 1;
            $input['begin'] = '08:00:00';
            $input['end'] = '20:00:00';
            $input['day'] = '0';
            $calendarSegment->add($input);
            $input['day'] = '1';
            $calendarSegment->add($input);
            $input['day'] = '2';
            $calendarSegment->add($input);
            $input['day'] = '3';
            $calendarSegment->add($input);
            $input['day'] = '4';
            $calendarSegment->add($input);
            $input['day'] = '5';
            $calendarSegment->add($input);
            $input['day'] = '6';
            $calendarSegment->add($input);
        } else {
            $this->migration->displayMessage("- calendar monitoring-default is still existing");
        }

        // Create default entities tags
        $this->migration->displayMessage("- default entities tags");
        require_once GLPI_ROOT . "/plugins/monitoring/inc/entity.class.php";
        $pmCommand = new PluginMonitoringEntity();
        $pmCommand->initialize($this->migration);

        // Create default realms
        $this->migration->displayMessage("- default realms");
        require_once GLPI_ROOT . "/plugins/monitoring/inc/realm.class.php";
        $pmCommand = new PluginMonitoringRealm();
        $pmCommand->initialize($this->migration);

        // Create default check strategies
        $this->migration->displayMessage("- default check strategies");
        require_once GLPI_ROOT . "/plugins/monitoring/inc/check.class.php";
        $pmCommand = new PluginMonitoringCheck();
        $pmCommand->initialize($this->migration);

        // Create default commands
        $this->migration->displayMessage("- default commands");
        require_once GLPI_ROOT . "/plugins/monitoring/inc/command.class.php";
        $pmCommand = new PluginMonitoringCommand();
        $pmCommand->initialize($this->migration);

        // Create default notification commands
        $this->migration->displayMessage("- default notification commands");
        require_once GLPI_ROOT . "/plugins/monitoring/inc/notificationcommand.class.php";
        $pmCommand = new PluginMonitoringNotificationcommand();
        $pmCommand->initialize($this->migration);

        // Create default host templates
        $this->migration->displayMessage("- default hosts templates");
        require_once GLPI_ROOT . "/plugins/monitoring/inc/hosttemplate.class.php";
        $pmCommand = new PluginMonitoringHosttemplate();
        $pmCommand->initialize($this->migration);

        // Create default contact templates
        $this->migration->displayMessage("- default contacts templates");
        require_once GLPI_ROOT . "/plugins/monitoring/inc/contacttemplate.class.php";
        $pmCommand = new PluginMonitoringContacttemplate();
        $pmCommand->initialize($this->migration);

        // Create default host notifications templates
        $this->migration->displayMessage("- default host notifications templates");
        require_once GLPI_ROOT . "/plugins/monitoring/inc/hostnotificationtemplate.class.php";
        $pmCommand = new PluginMonitoringHostnotificationtemplate();
        $pmCommand->initialize($this->migration);

        // Create default service notifications templates
        $this->migration->displayMessage("- default service notifications templates");
        require_once GLPI_ROOT . "/plugins/monitoring/inc/servicenotificationtemplate.class.php";
        $pmCommand = new PluginMonitoringServicenotificationtemplate();
        $pmCommand->initialize($this->migration);

        // Create default monitoring contact
        $this->migration->displayMessage("- default monitoring contact");
        require_once GLPI_ROOT . "/plugins/monitoring/inc/contact.class.php";
        $pmCommand = new PluginMonitoringContact();
        $pmCommand->initialize($this->migration);

        // Create default components
        $this->migration->displayMessage("- default components");
        require_once GLPI_ROOT . "/plugins/monitoring/inc/component.class.php";
        $pmCommand = new PluginMonitoringComponent();
        $pmCommand->initialize($this->migration);

        // Create default host configuration
        $this->migration->displayMessage("- default host configuration");
        require_once GLPI_ROOT . "/plugins/monitoring/inc/hostconfig.class.php";
        $pmCommand = new PluginMonitoringHostconfig();
        $pmCommand->initialize($this->migration);
    }

    /**
     * Create cron tasks
     */
    protected function createCronTasks()
    {

        $this->migration->displayMessage("Creating plugin tasks");

        // TODO: some other are to be registered !

        CronTask::Register('PluginMonitoringLog', 'cleanlogs',
            DAY_TIMESTAMP,
            [
                'comment' => __('Clean the monitoring log.', 'monitoring'),
                'mode' => CronTask::MODE_EXTERNAL,
                'allowmode' => CronTask::MODE_EXTERNAL | CronTask::MODE_INTERNAL,
                'hourmin' => 0, 'hourmax' => 24,
                'logs_lifetime' => 30
            ]
        );
        // Fred: do not manage unavailability
//        CronTask::Register('PluginMonitoringUnavailability', 'unavailability',
//            MINUTE_TIMESTAMP * 5,
//            [
//                'mode' => CronTask::MODE_EXTERNAL,
//                'allowmode' => CronTask::MODE_EXTERNAL | CronTask::MODE_INTERNAL,
//                'hourmin' => 0, 'hourmax' => 24,
//                'logs_lifetime' => 30
//            ]
//        );
        CronTask::Register('PluginMonitoringDisplayview_rule', 'replayallviewrules',
            MINUTE_TIMESTAMP * 30,
            [
                'comment' => __('Run the rules engine to compute information.', 'monitoring'),
                'mode' => CronTask::MODE_EXTERNAL,
                'allowmode' => CronTask::MODE_EXTERNAL | CronTask::MODE_INTERNAL,
                'hourmin' => 0, 'hourmax' => 24,
                'logs_lifetime' => 30
            ]
        );

//        CronTask::Register('PluginMonitoringAlignak', 'AlignakBuild',
//            DAY_TIMESTAMP,
//            [
//                'comment' => __('Alignak - to be developed...', 'monitoring'),
//                'mode' => CronTask::MODE_EXTERNAL,
//                'allowmode' => CronTask::MODE_EXTERNAL | CronTask::MODE_INTERNAL,
//                'hourmin' => 0, 'hourmax' => 24,
//                'param' => 50
//            ]
//        );
//        CronTask::Register('PluginMonitoringComputerTemplate', 'AlignakComputerTemplate',
//            DAY_TIMESTAMP,
//            [
//                'comment' => __('Alignak Send Counters-...', 'monitoring'),
//                'mode' => CronTask::MODE_EXTERNAL,
//                'allowmode' => CronTask::MODE_EXTERNAL | CronTask::MODE_INTERNAL,
//                'hourmin' => 0, 'hourmax' => 24,
//                'param' => 50
//            ]
//        );
    }

    /**
     * Create profile rights
     */
    protected function createProfile()
    {

        $this->migration->displayMessage("Creating plugin profile");

        require_once(GLPI_ROOT . "/plugins/monitoring/inc/profile.class.php");
        PluginMonitoringProfile::initProfile();
        $this->migration->displayMessage("created.");
    }

    /**
     * Clean profile rights
     */
    protected function cleanProfile()
    {
        require_once(GLPI_ROOT . "/plugins/monitoring/inc/profile.class.php");

        // Remove information related to profiles from the session (to clean menu and breadcrumb)
        PluginMonitoringProfile::removeRightsFromSession();
        // Remove profiles rights
        PluginMonitoringProfile::uninstallProfile();
    }

    /**
     * Migrate tables to InnoDB engine if Glpi > 9.3
     */
    protected function migrateInnoDb()
    {
        global $DB;

        $this->migration->displayMessage("Migrating tables engine");

        $version = rtrim(GLPI_VERSION, '-dev');
        if (version_compare($version, '9.3', '>=')) {
            $to_migrate = $DB->getMyIsamTables();

            while ($table = $to_migrate->next()) {
                $this->migration->displayMessage("- migrating: {$table['TABLE_NAME']}");
                $DB->queryOrDie("ALTER TABLE {$table['TABLE_NAME']} ENGINE = InnoDB");
            }
        }
    }

    /**
     * Create default display preferences
     */
    protected function createDefaultDisplayPreferences()
    {
//        global $DB;
        $this->migration->displayMessage("create default display preferences");

        /*
        // Create standard display preferences
        $displayprefs = new DisplayPreference();
        $found_dprefs = $displayprefs->find("`itemtype` = 'PluginMonitoringAlignak'");
        if (count($found_dprefs) == 0) {
            $query = "INSERT IGNORE INTO `glpi_displaypreferences`
                   (`id`, `itemtype`, `num`, `rank`, `users_id`) VALUES
                   (NULL, 'PluginMonitoringAlignak', 3, 1, 0),
                   (NULL, 'PluginMonitoringAlignak', 4, 2, 0),
                   (NULL, 'PluginMonitoringAlignak', 5, 3, 0)";
            $DB->query($query) or die ($DB->error());
        }

        $displayprefs = new DisplayPreference;
        $found_dprefs = $displayprefs->find("`itemtype` = 'PluginMonitoringMonitoringTemplate'");
        if (count($found_dprefs) == 0) {
            $query = "INSERT IGNORE INTO `glpi_displaypreferences`
                   (`id`, `itemtype`, `num`, `rank`, `users_id`) VALUES
                   (NULL, 'PluginMonitoringMonitoringTemplate', 2, 1, 0);";
            $DB->query($query) or die ($DB->error());
        }
        */
    }
}