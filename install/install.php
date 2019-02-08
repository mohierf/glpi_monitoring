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

class PluginMonitoringInstall
{
    /* @var Migration $migration */
    protected $migration;

    /**
     * Install the plugin
     * @param Migration $migration
     *
     * @return boolean
     */
    public function install(Migration $migration)
    {
        $this->migration = $migration;
        $_SESSION['plugin_monitoring_installation'] = true;

        // Drop existing tables if some exist
        $this->dropTables();

        $this->installSchema();
        $this->migrateInnoDb();

        $this->createProfile();

        $this->createFiles();

        $this->createItems();

        $this->createCronTasks();

        $this->createDefaultDisplayPreferences();

        Config::setConfigurationValues('monitoring', ['schema_version' => PLUGIN_MONITORING_VERSION]);

        unset($_SESSION['plugin_monitoring_installation']);

        return true;
    }

    /**
     * Upgrade the plugin
     * @param Migration $migration
     *
     * @return boolean
     */
    public function upgrade(Migration $migration)
    {
        $this->migration = $migration;
        $fromSchemaVersion = $this->getSchemaVersion();

        $_SESSION['plugin_monitoring_installation'] = true;

        $this->installSchema();

        // All cases are run starting from the one matching the current schema version
        switch ($fromSchemaVersion) {
            case '0.0':
            case '1.0':
                // Any schema version below or equal 1.0
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

        unset($_SESSION['plugin_monitoring_installation']);

        return true;
    }

    /**
     * Find the version of the plugin
     *
     * @return string|null
     */
    protected function getSchemaVersion()
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

        Toolbox::logInFile(PLUGIN_MONITORING_LOG, "Dropping the plugin tables:");

        // Drop tables of the plugin if they exist
        $result = $DB->query("SHOW TABLES LIKE 'glpi_plugin_monitoring_%'");
        while ($data = $DB->fetch_array($result)) {
            Toolbox::logInFile(PLUGIN_MONITORING_LOG, "- dropping: {$data[0]}");
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
        if (! $user->getFromDBByCrit(['name' => "monitoring"])) {
            $this->migration->displayMessage("- monitoring user");
            $input = array();
            $input['name'] = 'monitoring';
            $input['comment'] = 'Created by the monitoring plugin';
            $user->add($input);
        } else {
            $this->migration->displayMessage("- monitoring user is still existing");
        }

        $calendar = new Calendar();
        if (! $calendar->getFromDBByCrit(['name' => "24x7"])) {
            $this->migration->displayMessage("- calendar 24x7");
            $input = array();
            $input['name'] = '24x7';
            $input['comment'] = 'Created by the monitoring plugin';
            $input['is_recursive'] = 1;
            $calendars_id = $calendar->add($input);

            $calendarSegment = new CalendarSegment();
            $input = array();
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
    }

    /**
     * Create cron tasks
     */
    protected function createCronTasks()
    {

        $this->migration->displayMessage("Creating plugin tasks");

        // TODO: some other are to be registered !

        CronTask::Register('PluginMonitoringLog', 'cleanlogs', '96400',
            array('mode' => 2, 'allowmode' => 3, 'logs_lifetime' => 30));
        CronTask::Register('PluginMonitoringUnavailability', 'unavailability', '300',
            array('mode' => 2, 'allowmode' => 3, 'logs_lifetime' => 30));
        CronTask::Register('PluginMonitoringDisplayview_rule', 'replayallviewrules', '1200',
            array('mode' => 2, 'allowmode' => 3, 'logs_lifetime' => 30));

        CronTask::Register('PluginMonitoringAlignak', 'AlignakBuild', DAY_TIMESTAMP, [
            'comment' => __('Alignak - to be developed...', 'alignak'),
            'mode' => CronTask::MODE_EXTERNAL,
            'state' => CronTask::STATE_DISABLE,
            'param' => 50
        ]);
        CronTask::Register('PluginMonitoringComputerTemplate', 'AlignakComputerTemplate', DAY_TIMESTAMP, [
            'comment' => __('Alignak Send Counters-...', 'alignak'),
            'mode' => CronTask::MODE_EXTERNAL,
            'state' => CronTask::STATE_DISABLE,
            'param' => 50
        ]);
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