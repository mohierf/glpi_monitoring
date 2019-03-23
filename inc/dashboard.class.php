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

class PluginMonitoringDashboard extends CommonDBTM {

   static $rightname = 'plugin_monitoring_dashboard';

    /**
     * Get name of this type
     *
     * @param int $nb
     * @return string name of this type by language of the user connected
     *
     */
    static function getTypeName($nb = 0)
    {
        return __('Dashboard', 'monitoring');
    }


    /**
     * Display the dashboard part of the main page
     *
     * @param bool $refresh
     */
    function showMenu($refresh = true)
    {
        global $CFG_GLPI;

        $redirect = FALSE;
        $a_url = [];

        echo "<table class='tab_cadre' style='margin-bottom: 20px;'>";
        echo "<tr>";

        /*
         * Add monitoring framework restart commands if necessary
         */
        if (Session::haveRight("plugin_monitoring_command_fmwk", CREATE)) {
            echo "<td style='width: 27%; padding: 1%;'>";

            $changed = PluginMonitoringLog::hasConfigurationChanged(true);
            if ($changed) {
                $pmAlignakWS = new PluginMonitoringAlignakWS();
                $pmAlignakWS->ReloadRequest();
            }

            PluginMonitoringLog::isFrameworkRunning(true);

            PluginMonitoringDashboard::restartFramework();

            echo "</td>";
            echo "<td style='width: 67%; padding: 1%;'>";
        } else {
            echo "<td style='width: 97%; padding: 1%;'>";
        }

        echo "<table class='tab_cadre_fixe' width='950'>";
        echo "<tr class='tab_bg_3'>";
        echo "<td>";

        if (Session::haveRight("plugin_monitoring_system_status", PluginMonitoringProfile::DASHBOARD)
            or Session::haveRight("plugin_monitoring_host_status", PluginMonitoringProfile::DASHBOARD)
            or Session::haveRight("plugin_monitoring_service_status", PluginMonitoringProfile::DASHBOARD)) {
            echo "<table class='tab_cadre_fixe'>";
            echo "<tr class='tab_bg_1'>";

            if (Session::haveRight("plugin_monitoring_system_status",
                PluginMonitoringProfile::DASHBOARD)) {
                // Do not display nor get the servers status (use the last known)
                $status = PluginMonitoringServer::getServersStatus();
                echo "<th colspan='2'>";
                $class = "dashboard-part";
                if ($this->includePartMarker('system')) {
                    if ($status != 'ok') {
                        if ($status == 'none') {
                            $class .= " dashboard-part-grey";
                        } else {
                            $class .= " dashboard-part-red";
                        }
                    } else {
                        $class .= " dashboard-part-green";
                    }
                }
                echo '<div class="' . $class . '">';
                echo '<a href="' . $CFG_GLPI['root_doc'] . '/plugins/monitoring/front/system.php">';
                echo __('System status', 'monitoring');
                echo "</a>";
                echo '</div>';
                $a_url[] = $CFG_GLPI['root_doc'] . "/plugins/monitoring/front/system.php";
                echo "</th>";
            }

            if (Session::haveRight("plugin_monitoring_host_status",
                PluginMonitoringProfile::DASHBOARD)) {
                echo "<th colspan='2'>";
                $class = "dashboard-part";
                if ($this->includePartMarker('host')) {
                    $class .= " dashboard-part-grey";
                }
                echo '<div class="' . $class . '">';
                echo "<a href='" . $CFG_GLPI['root_doc'] . "/plugins/monitoring/front/host.php'>";
                echo __('Hosts status', 'monitoring');
                echo "</a>";
                echo '</div>';
                $a_url[] = $CFG_GLPI['root_doc'] . "/plugins/monitoring/front/host.php";
                echo "</th>";
            }

            if (Session::haveRight("plugin_monitoring_service_status",
                PluginMonitoringProfile::DASHBOARD)) {
                echo "<th colspan='2'>";
                $class = "dashboard-part";
                if ($this->includePartMarker('service')) {
                    $class .= " dashboard-part-grey";
                }
                echo '<div class="' . $class . '">';
                echo "<a href='" . $CFG_GLPI['root_doc'] . "/plugins/monitoring/front/service.php'>";
                echo __('Services status', 'monitoring');
                echo "</a>";
                echo '</div>';
                $a_url[] = $CFG_GLPI['root_doc'] . "/plugins/monitoring/front/service.php";
                echo "</th>";
            }

            echo "</tr>";
            echo "</table>";
        } else {
            if (basename($_SERVER['PHP_SELF']) == 'service.php') {
                $redirect = true;
            } else if (basename($_SERVER['PHP_SELF']) == 'host.php') {
                $redirect = true;
            }
        }

        if ($refresh) {
            $this->refreshPage();
        }

        echo "</td>";
        echo "</tr>";
        echo "</table>";

        echo "</td>";
        echo "</tr>";
        echo "</table>";

        if ($redirect) {
            Html::redirect(array_shift($a_url));
        }
    }


    function getHostsCounters($display = true, $reduced = false)
    {
        global $CFG_GLPI;

        $play_sound = false;

        $up = PluginMonitoringHost::countWhere([
            'state' => 'UP',
            'state_type' => 'HARD'
        ]);
        $up_soft = PluginMonitoringHost::countWhere([
            'state' => 'UP',
            'state_type' => 'SOFT'
        ]);
        $unreachable = PluginMonitoringHost::countWhere([
            'state' => 'UNREACHABLE',
            'state_type' => 'HARD',
            'is_acknowledged' => '0'
        ]);
        $unreachable_soft = PluginMonitoringHost::countWhere([
            'state' => 'UNREACHABLE',
            'state_type' => 'SOFT',
            'is_acknowledged' => '0'
        ]);
        $unknown = PluginMonitoringHost::countWhere([
            'state' => 'UNKNOWN',
            'state_type' => 'HARD',
            'is_acknowledged' => '0'
        ]);
        $unknown_soft = PluginMonitoringHost::countWhere([
            'state' => 'UNKNOWN',
            'state_type' => 'SOFT',
            'is_acknowledged' => '0'
        ]);
        $down = PluginMonitoringHost::countWhere([
            'state' => 'DOWN',
            'state_type' => 'HARD',
            'is_acknowledged' => '0'
        ]);
        $down_soft = PluginMonitoringHost::countWhere([
            'state' => 'DOWN',
            'state_type' => 'SOFT',
            'is_acknowledged' => '0'
        ]);
        $acknowledge = PluginMonitoringHost::countWhere([
            'is_acknowledged' => '1'
        ]);

        // Manage play sound if down increased since last refresh
        if (isset($_SESSION['plugin_monitoring']['dashboard_hosts_down'])) {
            if ($down > $_SESSION['plugin_monitoring']['dashboard_hosts_down']) {
                $play_sound = true;
            }
        }
        $_SESSION['plugin_monitoring']['dashboard_hosts_down'] = $down;

        $a_states = [];
        if (!$display) {
            $a_states['up'] = strval($up);
            $a_states['up_soft'] = strval($up_soft);
            $a_states['unreachable'] = strval($unreachable);
            $a_states['unreachable_soft'] = strval($unreachable_soft);
            $a_states['unknown'] = strval($unknown);
            $a_states['unknown_soft'] = strval($unknown_soft);
            $a_states['down'] = strval($down);
            $a_states['down_soft'] = strval($down_soft);
            $a_states['acknowledge'] = strval($acknowledge);

            return $a_states;
        } else {
            $a_states['up'] = [
                'counter' => $up, 'soft' => $up_soft, 'label' => __('Up', 'monitoring')
            ];
            $a_states['unreachable'] = [
                'counter' => $unreachable, 'soft' => $unreachable_soft,
                'label' => __('Unreachable', 'monitoring')
            ];
            $a_states['unknown'] = [
                'counter' => $unknown, 'soft' => $unknown_soft,
                'label' => __('Unknown', 'monitoring')
            ];
            $a_states['down'] = [
                'counter' => $down, 'soft' => $down_soft,
                'label' => __('Down', 'monitoring')
            ];
            $a_states['acknowledge'] = [
                'counter' => $acknowledge, 'soft' => -1,
                'label' => __('Acknowledged', 'monitoring')
            ];
        }

        // Hosts counters table
        $font_size_label = $reduced ? "visibility: hidden" : "font-size: 12px;font-weight: bold;";
        $font_size_counter = $reduced ? "font-size: 24px;font-weight: bold;" : "font-size: 52px;font-weight: bold;";
        echo "<table class='center tab_cadre' style='width: 80%; margin-left: 10%; margin-top: 5px;'>";
        echo "<tr>";
        foreach ($a_states as $state => $status) {
            $link = $CFG_GLPI['root_doc'] .
                "/plugins/monitoring/front/host.php?hidesearch=1"
                . "&criteria[0][field]=5"
                . "&criteria[0][searchtype]=contains"
                . "&criteria[0][value]=" . strtoupper($state)

                . "&itemtype=PluginMonitoringHost"
                . "&start=0'";
            echo "<td class='center' style='width: 20%'>";
            if (!$reduced) {
                echo "<a href='" . $link . ">";
                echo "<span class='font-$state' style='$font_size_label'>" . $status['label'] . "</span>";
                echo "</a>";
                echo "<br>";
            }
            echo "<a href='" . $link . ">";
            echo "<span class='font-$state' style='$font_size_counter'>" . $status['counter'] . "</span>";
            echo "</a>";
            echo "<br>";
            if (!$reduced and $status['soft'] >= 0) {
                echo "<em style='font-size: 10px;font-weight: bold;'>" . __('Soft state : ', 'monitoring') . $status['soft'] . "</em>";
            }
            echo "</td>";
        }
        echo "</tr>";
        echo "</table>";

        // ** play sound
        if ($play_sound) {
            echo '<audio autoplay="autoplay">
                 <source src="../audio/star-trek.ogg" type="audio/ogg" />
                 Your browser does not support the audio element.
               </audio>';
        }

        return [];
    }


    function getServicesCounters($display = true, $reduced = false, $a_query=[])
    {
        global $CFG_GLPI;

        PluginMonitoringToolbox::logIfDebug("Extra query: " . print_r($a_query, true));

        $play_sound = false;

        // Get counters
        $ok = PluginMonitoringService::countWhere(array_merge($a_query, [
            'state' => 'OK',
            'state_type' => 'HARD'
        ]));
        $ok_soft = PluginMonitoringService::countWhere(array_merge($a_query, [
            'state' => 'OK',
            'state_type' => 'SOFT'
        ]));
        $unreachable = PluginMonitoringService::countWhere(array_merge($a_query, [
            'state' => 'UNREACHABLE',
            'state_type' => 'HARD',
            'is_acknowledged' => '0'
        ]));
        $unreachable_soft = PluginMonitoringService::countWhere(array_merge($a_query, [
            'state' => 'UNREACHABLE',
            'state_type' => 'SOFT',
            'is_acknowledged' => '0'
        ]));
        $unknown = PluginMonitoringService::countWhere(array_merge($a_query, [
            'state' => 'UNKNOWN',
            'state_type' => 'HARD',
            'is_acknowledged' => '0'
        ]));
        $unknown_soft = PluginMonitoringService::countWhere(array_merge($a_query, [
            'state' => 'UNKNOWN',
            'state_type' => 'SOFT',
            'is_acknowledged' => '0'
        ]));
        $critical = PluginMonitoringService::countWhere(array_merge($a_query, [
            'state' => 'CRITICAL',
            'state_type' => 'HARD',
            'is_acknowledged' => '0'
        ]));
        $critical_soft = PluginMonitoringService::countWhere(array_merge($a_query, [
            'state' => 'CRITICAL',
            'state_type' => 'SOFT',
            'is_acknowledged' => '0'
        ]));
        $warning = PluginMonitoringService::countWhere(array_merge($a_query, [
            'state' => 'WARNING',
            'state_type' => 'HARD',
            'is_acknowledged' => '0'
        ]));
        $warning_soft = PluginMonitoringService::countWhere(array_merge($a_query, [
            'state' => 'WARNING',
            'state_type' => 'SOFT',
            'is_acknowledged' => '0'
        ]));
        $acknowledge = PluginMonitoringService::countWhere(array_merge($a_query, [
            'is_acknowledged' => '1'
        ]));

        // Manage play sound if critical increased since last refresh
        if (isset($_SESSION['plugin_monitoring']['dashboard_services_critical'])) {
            if ($critical > $_SESSION['plugin_monitoring']['dashboard_services_critical']) {
                $play_sound = true;
            }
        }
        $_SESSION['plugin_monitoring']['dashboard_services_critical'] = $critical;

        $a_states = [];
        if (!$display) {
            $a_states['ok'] = strval($ok);
            $a_states['ok_soft'] = strval($ok_soft);
            // Group unknown and unreachable
//            $a_states['unreachable'] = strval($unreachable);
//            $a_states['unreachable_soft'] = strval($unreachable_soft);
            $a_states['unknown'] = strval($unknown + $unreachable);
            $a_states['unknown_soft'] = strval($unknown_soft + $unreachable_soft);
            $a_states['critical'] = strval($critical);
            $a_states['critical_soft'] = strval($critical_soft);
            $a_states['warning'] = strval($warning);
            $a_states['warning_soft'] = strval($warning_soft);
            $a_states['acknowledge'] = strval($acknowledge);

            return $a_states;
        } else {
            $a_states['ok'] = [
                'counter' => $ok, 'soft' => $ok_soft, 'label' => __('Ok', 'monitoring')
            ];
            $a_states['warning'] = [
                'counter' => $warning, 'soft' => $warning_soft, 'label' => __('Warning', 'monitoring')
            ];
            $a_states['critical'] = [
                'counter' => $critical, 'soft' => $critical_soft, 'label' => __('Critical', 'monitoring')
            ];
            // Group unknown and unreachable
//            $a_states['unreachable'] = [
//                'counter' => $unreachable, 'soft' => $unreachable_soft, 'label' => __('Unreachable', 'monitoring')
//            ];
            $a_states['unknown'] = [
                'counter' => $unknown + $unreachable, 'soft' => $unknown_soft + $unreachable_soft,
                'label' => __('Unknown', 'monitoring')
            ];
            $a_states['acknowledge'] = [
                'counter' => $acknowledge, 'soft' => -1, 'label' => __('Acknowledged', 'monitoring')
            ];
        }

        // Services counters table
        $font_size_label = $reduced ? "visibility: hidden" : "font-size: 12px;font-weight: bold;";
        $font_size_counter = $reduced ? "font-size: 24px;font-weight: bold;" : "font-size: 52px;font-weight: bold;";
        echo "<table class='center tab_cadre' style='width: 80%; margin-left: 10%; margin-top: 5px;'>";
        echo "<tr>";
        foreach ($a_states as $state => $status) {
            $link = $CFG_GLPI['root_doc'] .
                "/plugins/monitoring/front/service.php?hidesearch=1"
//              . "&reset=reset"
                . "&criteria[0][field]=5"
                . "&criteria[0][searchtype]=contains"
                . "&criteria[0][value]=" . strtoupper($state)

                . "&itemtype=PluginMonitoringHost"
                . "&start=0'";
            echo "<td class='center' style='width: 20%'>";
            if (!$reduced) {
                echo "<a href='" . $link . ">";
                echo "<span class='font-$state' style='$font_size_label'>" . $status['label'] . "</span>";
                echo "</a>";
                echo "<br>";
            }
            echo "<a href='" . $link . ">";
            echo "<span class='font-$state' style='$font_size_counter'>" . $status['counter'] . "</span>";
            echo "</a>";
            echo "<br>";
            if (!$reduced and $status['soft'] >= 0) {
                echo "<em style='font-size: 10px;font-weight: bold;'>" . __('Soft state : ', 'monitoring') . $status['soft'] . "</em>";
            }
            echo "</td>";
        }
        echo "</tr>";
        echo "</table>";

        // ** play sound
        if ($play_sound) {
            echo '<audio autoplay="autoplay">
                 <source src="../audio/star-trek.ogg" type="audio/ogg" />
                 Your browser does not support the audio element.
               </audio>';
        }

        return [];
    }


    /**
     * Display list of services
     *
     * @param string $width
     * @param bool $perfdatas
     * @param array $params
     */
    function showResourcesBoard($width = '', $perfdatas = false, $params = [])
    {
        if (!isset($_SESSION['plugin_monitoring']['reduced_interface'])) {
            $_SESSION['plugin_monitoring']['reduced_interface'] = false;
        }

        /*
         * Fields index is defined in hot.class.php (rawSearchOptions function). As of now:
         * entity, name, host_name,
         * state, state type, last check time, check output, performance data
         * latency, execution time, acknowledge state
         */
        /*
            [0] => Entity
            [1] => Host name
            [2] => Name
            [3] => Component
            [4] => Source
            [5] => State
            [6] => State type
            [7] => Last check result
            [8] => Last check output
            [9] => Last check performance data
            [10] => Last check latency
        */
        $search_columns = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11];
        // Will not be displayed
        $ignored_columns = [4];

        $data = Search::prepareDatasForSearch('PluginMonitoringService', $params, $search_columns);
        $data['tocompute'] = $data['toview'];
        Search::constructSQL($data);
        Search::constructData($data);
        PluginMonitoringToolbox::logIfDebug("-> " . print_r($data, true));
        /*
         * $data['data'] contains:
         * - execution_time
         * - current_user
         * - count
         * - totalcount
         * - begin
         * - end
         * - items : index in rows
         * - cols: array of columns
         * - rows: array of result lines
         * - itemtype and item
         */
        $columns = [];
        if (isset($data['data']['cols'])) {
            PluginMonitoringToolbox::logIfDebug("Columns: " . print_r($data['data']['cols'], true));
            foreach ($data['data']['cols'] as $index => $col) {
                $columns[] = $col['name'];
            }
        }

        // Build pager parameters
        $globallinkto = Toolbox::append_params([
            'criteria' => Toolbox::stripslashes_deep($data['search']['criteria']),
            'metacriteria' => Toolbox::stripslashes_deep($data['search']['metacriteria'])], '&amp;');
        $parameters = "sort=" . $data['search']['sort'] . "&amp;order=" . $data['search']['order'] . '&amp;' . $globallinkto;

        if (isset($_GET['_in_modal'])) {
            $parameters .= "&amp;_in_modal=1";
        }

        // If the beginning of the view is before the number of items
        $begin_display = $data['data']['begin'];
        if (isset($data['data']['count']) and $data['data']['count'] > 0) {
            // Display pager only for HTML
            if ($data['display_type'] == Search::HTML_OUTPUT) {
                Html::printPager($data['search']['start'], $data['data']['totalcount'],
                    $data['search']['target'], $parameters);
            }
        } else {
            echo "<table class='tab_cadre'>";
            echo "<tr><th style='height:80px'>";
            echo __('No results matched your query.', 'monitoring');
            echo "</th></tr>";
            echo "</table>";
        }

        if ($perfdatas) {
            echo "<table class='tab_cadre'>";
            echo "<tr><th style='height:80px'>";
            echo "<span class='red" . __('No performance data display yet!.', 'monitoring') . "</span>";
            echo "</th></tr>";
            echo "</table>";
        }
        if ($width == '') {
            echo "<table class='tab_cadrehov' style='width:100%;'>";
        } else {
            echo "<table class='tab_cadrehov' style='width:" . $width . "px;'>";
        }
        $num = 0;

        PluginMonitoringToolbox::logIfDebug("Columns: " . print_r($columns, true));

        echo "<tr class='tab_bg_1'>";
        foreach ($columns as $index => $column) {
            // Ignore some specific fields
            if (in_array($index, $ignored_columns)) {
                continue;
            }

            $this->showHeaderItem(
                $column, $index, $num, $begin_display, $globallinkto,
                'service.php', 'PluginMonitoringService');
        }
        echo "</tr>";

        PluginMonitoringToolbox::logIfDebug("Display {$data['data']['count']} service lines:");

        $start = 0;
        $total_count = 0;
        $target = '';
        if (isset($data['data']['rows'])) {
            foreach ($data['data']['rows'] as $row) {
                // Reduced array or not ?
                if ($_SESSION['plugin_monitoring']['reduced_interface']
                    and $row[PluginMonitoringService::COLUMN_STATE]['displayname'] == 'OK') {
                    continue;
                }

                $this->displayServiceLine($row, $columns, $ignored_columns);
            }
        }
        echo "</table>";
        echo "<br/>";

        if (isset($data['data']['count']) and $data['data']['count'] > 0) {
            Html::printPager($start, $total_count, $target, $parameters);
        }
    }


    /**
     * Display list of hosts
     *
     * @param array $params
     * @param string $width
     */
    function showHostsBoard($params, $width = '')
    {
        if (!isset($_SESSION['plugin_monitoring']['reduced_interface'])) {
            $_SESSION['plugin_monitoring']['reduced_interface'] = false;
        }

        /*
         * Fields index is defined in host.class.php (rawSearchOptions function). As of now:
         * entity, name, item type, item id, host_name,
         * state, state type, last check time, check output, performance data
         * latency, execution time, acknowledge state
         */
        /*
            [0] => Entity
            [1] => Name
            [2] => Item type
            [3] => Item identifier
            [4] => Monitoring host name
            [5] => Source
            [6] => State
            [7] => State type
            [8] => Last check result
            [9] => Last check output
            [10] => Last check performance data
            [11] => Host resources state
            [12] => IP address
            [13] => Host action
         */
        $search_columns = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11];
        // Will not be displayed
        $ignored_columns = [2, 3, 4, 5];

        $data = Search::prepareDatasForSearch('PluginMonitoringHost', $params, $search_columns);
        $data['tocompute'] = $data['toview'];
        Search::constructSQL($data);
        Search::constructData($data);
        PluginMonitoringToolbox::logIfDebug("-> " . print_r($data, true));
        /*
         * $data['data'] contains:
         * - execution_time
         * - current_user
         * - count
         * - totalcount
         * - begin
         * - end
         * - items : index in rows
         * - cols: array of columns
         * - rows: array of result lines
         * - itemtype and item
         */
        $columns = [];
        if (isset($data['data']['cols'])) {
            PluginMonitoringToolbox::logIfDebug("Columns: " . print_r($data['data']['cols'], true));
            foreach ($data['data']['cols'] as $index => $col) {
                $columns[] = $col['name'];
            }
            $columns[] = __('Host resources state', 'monitoring');
            $columns[] = __('IP address', 'monitoring');
        }

        // Build pager parameters
        $globallinkto = Toolbox::append_params([
            'criteria' => Toolbox::stripslashes_deep($data['search']['criteria']),
            'metacriteria' => Toolbox::stripslashes_deep($data['search']['metacriteria'])], '&amp;');
        $parameters = "sort=" . $data['search']['sort'] . "&amp;order=" . $data['search']['order'] . '&amp;' . $globallinkto;

        if (isset($_GET['_in_modal'])) {
            $parameters .= "&amp;_in_modal=1";
        }

        // If the beginning of the view is before the number of items
        $begin_display = isset($data['data']['begin']) ? $data['data']['begin'] : 0;
        if (isset($data['data']['count']) and $data['data']['count'] > 0) {
            // Display pager only for HTML output
            if ($data['display_type'] == Search::HTML_OUTPUT) {
                Html::printPager($data['search']['start'], $data['data']['totalcount'],
                    $data['search']['target'], $parameters);
            }
        } else {
            echo "<table class='tab_cadre'>";
            echo "<tr><th style='height:80px'>";
            echo __('No results matched your query.', 'monitoring');
            echo "</th></tr>";
            echo "</table>";
        }

        if (empty($width)) {
            echo "<table class='tab_cadre' style='width:100%;'>";
        } else {
            echo "<table class='tab_cadre' style='width:" . $width . "px;'>";
        }
        $num = 0;

        // Host action command...
        $host_action = false;
        $host_command_command = "";
        $host_command_name = "";
        $plugin = new Plugin();
        if (Session::haveRight("plugin_monitoring_host_actions", CREATE)) {
            if ($plugin->isInstalled('fusioninventory')
                and $plugin->isActivated('fusioninventory')) {
                $pmCommand = new PluginMonitoringCommand();
                if ($pmCommand->getFromDBByCrit(['command_name' => 'host_action'])) {
                    $host_action = true;
                    $host_command_name = $pmCommand->getName();
                    $host_command_command = $pmCommand->getField('command_line');
                    $columns[] = __('Host action', 'monitoring');
                }
            } else {
                PluginMonitoringToolbox::log(
                    "Plugin Fusion Inventory is not installed or activated. Host command feature is disabled.");
            }
        }

        PluginMonitoringToolbox::logIfDebug("Columns: " . print_r($columns, true));

        echo "<tr class='tab_bg_1'>";
        foreach ($columns as $index => $column) {
            // Ignore some specific fields
            if (in_array($index, $ignored_columns)) continue;

            $this->showHeaderItem(
                $column, $index, $num, $begin_display, $globallinkto,
                'host.php', 'PluginMonitoringHost');
        }
        echo "</tr>";

        $start = 0;
        $total_count = 0;
        $target = '';
        if (isset($data['data']['rows'])) {
            $start = $data['data']['begin'];
            $total_count = $data['data']['totalcount'];
            foreach ($data['data']['rows'] as $row) {
                // Reduced array or not ?
                if ($_SESSION['plugin_monitoring']['reduced_interface']
                    and $row[PluginMonitoringHost::COLUMN_STATE]['displayname'] == 'UP') {
                    continue;
                }

                // Get all host services except if state is ok or is already acknowledged ...
                $a_ret = PluginMonitoringHost::getServicesState(
                    $row[4]['displayname'],
                    "`state` != 'OK' AND `is_acknowledged` = '0'");
                $row[] = [
                    'count' => 1,
                    [
                        'name' => $a_ret[1]
                    ],
                    'displayname' => $a_ret[0]
                ];

                // Get host first IP address
                // items_id / itemtype are 3rd and 2nd entries!
                $ip = PluginMonitoringHostaddress::getIp(
                    $row[3]['displayname'],
                    $row[2]['displayname'], '');
                $row[] = [
                    'count' => 1,
                    [
                        'name' => $ip
                    ],
                    'displayname' => empty($ip) ? __('Unknown IP address', 'monitoring') : $ip
                ];

                if ($host_action) {
                    $row[] = [
                        'count' => 1,
                        [
                            'name' =>  $host_command_command
                        ],
                        'displayname' => $host_command_name
                    ];
                }

                $this->displayHostLine($row, $columns, $ignored_columns);
            }
        }
        echo "</table>";
        echo "<br/>";

        if (isset($data['data']['count']) and $data['data']['count'] > 0) {
            Html::printPager($start, $total_count, $target, $parameters);
        }
    }


    /**
     * Manage header of list
     *
     * @param $title
     * @param $numoption
     * @param $num
     * @param $start
     * @param $globallinkto
     * @param $page
     * @param $itemtype
     */
    function showHeaderItem($title, $numoption, &$num, $start, $globallinkto, $page, $itemtype)
    {
        global $CFG_GLPI;

        // Set display type for export if define
        $output_type = Search::HTML_OUTPUT;
        if (isset($_GET["display_type"])) {
            $output_type = $_GET["display_type"];
        }

        $order = "ASC";
        if (isset($_GET["order"])) {
            $order = $_GET["order"];
        }

        $linkto = '';
        if ($numoption >= 0) {
            $linkto = $CFG_GLPI['root_doc'] .
                "/plugins/monitoring/front/$page?itemtype=$itemtype&amp;sort=$numoption&amp;order=". (($order == "ASC") ? "DESC" : "ASC") ."&amp;start=$start&amp;$globallinkto";
        }
        $is_sorted = false;
        if (isset($_GET['sort']) and $_GET['sort'] == $numoption) {
            $is_sorted = true;
        }
        echo Search::showHeaderItem($output_type, $title, $num, $linkto, $is_sorted, $order);
    }


    static function displayServiceLine($data, $columns, $ignored_columns)
    {
        global $CFG_GLPI;

        $pm_Service = new PluginMonitoringService();
        $pm_Service->getFromDB($data['id']);
        $pm_Component = new PluginMonitoringComponent();
        $pm_Component->getFromDB($data[2][0]['id']);

        PluginMonitoringToolbox::logIfDebug("- row: " . print_r($data, true));

        echo "<tr class='" . $pm_Service->getClass(true) . "'>";
        foreach ($columns as $index => $column) {
            // Ignore some specific fields
            if (in_array($index, $ignored_columns)) continue;

            $class = '';
            /*
            if ($index == 1 or $index == 5 or $index == 6) {
                $class = $pm_Host->getClass();
            }
            */

            // Host Name
            if ($index == PluginMonitoringService::COLUMN_HOST_NAME and !empty($data[$index][0]['name'])) {
                $link = $CFG_GLPI['root_doc'] .
                    "/plugins/monitoring/front/host.php?"
                    . "&criteria[0][field]=" . 2
                    . "&criteria[0][searchtype]=contains"
                    . "&criteria[0][value]=^" . $data[$index][0]['name'] . "$"
                    . "&itemtype=PluginMonitoringHost"
                    . "&start=0'";
                $data[$index]['displayname'] = Html::link($data[$index]['displayname'], $link);
            }
            // State and state type
            if ($index == PluginMonitoringService::COLUMN_STATE and empty($data[$index][0]['name'])) {
                $data[$index]['displayname'] = __('Not yet known', 'monitoring');
            }
            if ($index == PluginMonitoringService::COLUMN_STATE_TYPE and empty($data[$index][0]['name'])) {
                $data[$index]['displayname'] = __('Not yet known', 'monitoring');
            }
//            if ($index == 7 and !empty($data[$index][0]['name'])) {
//                $data[$index]['displayname'] = $data[$index]['displayname'] . "&nbsp;" . Html::showToolTip($data[$index][0]['name'], ['display' => false]);
//            }
//            // Host services states synthesis
//            if ($index == 11 and !empty($data[$index][0]['name'])) {
//                $data[$index]['displayname'] = $data[$index]['displayname'] . "&nbsp;" . Html::showToolTip($data[$index][0]['name'], ['display' => false]);
//            }
//
            echo '<td class="' . $class . '">';
            echo $data[$index]['displayname'];
            echo '</td>';
        }
        echo "</tr>";
    }


    static function displayHostLine($data, $columns, $ignored_columns)
    {
        global $CFG_GLPI;

        $pm_Host = new PluginMonitoringHost();
        $pm_Host->getFromDB($data['id']);

        PluginMonitoringToolbox::logIfDebug("- row: " . print_r($data, true));

        echo "<tr class='" . $pm_Host->getClass(true) . "'>";
        foreach ($columns as $index => $column) {
            // Ignore some specific fields
            if (in_array($index, $ignored_columns)) continue;

            $class = '';
            /*
            if ($index == 1 or $index == 5 or $index == 6) {
                $class = $pm_Host->getClass();
            }
            */

            // Name
            if ($index == 1 and !empty($data[$index][0]['name'])) {
                $data[$index]['displayname'] = "<span>" . $pm_Host->getLink() . "</span>";
            }
            // State and state type
            if ($index == PluginMonitoringHost::COLUMN_STATE and empty($data[$index][0]['name'])) {
                $data[$index]['displayname'] = __('Not yet known', 'monitoring');
            }
            if ($index == PluginMonitoringHost::COLUMN_STATE_TYPE and empty($data[$index][0]['name'])) {
                $data[$index]['displayname'] = __('Not yet known', 'monitoring');
            }
            if ($index == 7 and !empty($data[$index][0]['name'])) {
                $data[$index]['displayname'] = $data[$index]['displayname'] . "&nbsp;" . Html::showToolTip($data[$index][0]['name'], ['display' => false]);
            }
            // Host services states synthesis
            if ($index == 11 and !empty($data[$index][0]['name'])) {
                $link = $CFG_GLPI['root_doc'] .
                    "/plugins/monitoring/front/service.php?"
                    . "&criteria[0][field]=" . 2
                    . "&criteria[0][searchtype]=contains"
                    . "&criteria[0][value]=^" . $pm_Host->getField('host_name') . "$"
                    . "&itemtype=PluginMonitoringService"
                    . "&start=0'";
                $data[$index]['displayname'] = Html::link($data[$index]['displayname'], $link) . "&nbsp;" . Html::showToolTip($data[$index][0]['name'], ['display' => false]);
            }
            // Host action
            if ($index == 13 and !empty($data[$index][0]['name'])) {
                echo "<td class='center'>";
                echo "<form name='form' method='post' 
                action='" . $CFG_GLPI['root_doc'] .
                    "/plugins/monitoring/front/" . $data[$index][0]['name'] . ".php'>";

                echo Html::hidden('glpi_users_id',
                    ['value' => Session::getLoginUserID()]);

                echo Html::hidden('host_id',
                    ['value' => $data[1][0]['id']]);
                echo Html::hidden('host_name',
                    ['value' => $data[1][0]['name']]);
                echo Html::hidden('state',
                    ['value' => $data[6][0]['name']]);
                echo Html::hidden('statetype',
                    ['value' => $data[7][0]['name']]);
                echo Html::hidden('host_last_check',
                    ['value' => $data[8][0]['name']]);
                echo Html::hidden('host_output',
                    ['value' => $data[9][0]['name']]);
                echo Html::hidden('host_perfdata',
                    ['value' => $data[10][0]['name']]);

                echo "<input type='submit' name='host_command' 
                    value=\"" . $data[$index]['displayname'] . "\" class='submit'>";

                Html::closeForm();

                echo "</td>";
                continue;
            }
            echo '<td class="' . $class . '">';
            echo $data[$index]['displayname'];
            echo '</td>';
        }
        echo "</tr>";

        return;
    }


    /**
     * Restart Monitoring framework buttons :
     * - on main Monitoring plugin page
     * - one button per each declared Monitoring framework tag
     * - one button to restart all Monitoring framework instances
     *
     * @global $CFG_GLPI
     */
    static function restartFramework()
    {
        global $CFG_GLPI;

        $pmTag = new PluginMonitoringServer();
        $a_raw_tags = $pmTag->find();

        $a_tags = [];
        foreach ($a_raw_tags as $data) {
            $url = $data['url'];
            if (empty($url)) {
                $url = "http://" . $data["tag"] . ':7770';
            }
            if (!isset($a_tags[$url])) {
                $a_tags[$url] = $data;
            }
        }

        if (count($a_tags) > 0) {
            $fmwk_commands = [
                'reload' => [
                    'command' => 'reload',
                    'title' => __('Reload monitoring configuration from the Glpi database', 'monitoring'),
                    'button' => __('Reload monitoring', 'monitoring'),
                ],
                'restart' => [
                    'command' => 'restart',
                    'title' => __('Restart monitoring', 'monitoring'),
                    'button' => __('Restart monitoring', 'monitoring'),
                ],
            ];
            if (PLUGIN_MONITORING_SYSTEM == 'alignak') {
                $fmwk_commands = [
                    'reload_configuration' => [
                        'command' => 'reload_configuration',
                        'title' => __('Reload monitoring configuration from the Glpi database', 'monitoring'),
                        'button' => __('Reload monitoring', 'monitoring'),
                    ]
                ];
            }

            // Configuration reload happened in the last 30 minutes?
            $recent_restarts = PluginMonitoringLog::isRestartRecent(1800);

            foreach ($fmwk_commands as $command) {
                echo '<table class="tab_cadre">';
                echo '<tr>';
                echo '<td style="width: 100px" onClick="$(\'#list_'. $command['command'] .'\').toggle();">';
                echo '<button title="'. $command['title'] .'">' . $command['button'] . '</button>';
                echo '</td>';
                echo '<td id="list_'. $command['command'] .'" style="display:none;">';
                echo "<table><tr>";

                $url = $CFG_GLPI['root_doc'] . "/plugins/monitoring/front/restart_fmwk.form.php?action=" . $command['command'];

                echo '<td><a class="monitoring-button" href="'. $url .'">';
                echo __("All instances", "monitoring");
                echo '</a></td>';
                if (count($a_tags) > 0) {
                    foreach ($a_tags as $taginfo => $data) {
                        $button_class = "msgboxmonit msgboxmonit-green";
                        if ($recent_restarts) {
                            $tag_name = $data['name'];
                            foreach ($recent_restarts as $restart) {
                                if ($restart['value'] == $tag_name) {
                                    $button_class = "msgboxmonit msgboxmonit-red";
                                    break;
                                }
                            }
                        }
                        $url = $CFG_GLPI['root_doc'] . "/plugins/monitoring/front/restart_fmwk.form.php?action=" . $command['command'] . "&tag=" . $data['id'];

                        echo '<td><a class="monitoring-button'. $button_class .'" href="'. $url .'">';
                        echo $taginfo;
                        echo '</a></td>';
                    }
                }
                echo '</tr></table>';
                echo '</tr></table>';
            }
        }
    }


    /**
     * Include code to change the current page refresh behaviour
     *
     * @param bool $onlyreduced
     */
    function refreshPage($onlyreduced = false)
    {
        $reduced_interface = false;
        if (isset($_SESSION['plugin_monitoring']['reduced_interface'])) {
            $reduced_interface = $_SESSION['plugin_monitoring']['reduced_interface'];
        }

        if (!$onlyreduced) {
            if (isset($_POST['_refresh'])) {
                $_SESSION['plugin_monitoring']['_refresh'] = $_POST['_refresh'];
            }
            echo '<meta http-equiv ="refresh" content="' . $_SESSION['plugin_monitoring']['_refresh'] . '">';
        }

        echo "<form name='form' method='post' action='" . $_SERVER["PHP_SELF"] . "' >";
        echo "<table class='tab_cadre_fixe' width='950'>";
        echo "<tr class='tab_bg_1'>";
        if (!$onlyreduced) {
            echo "<td>";
            echo __('Page refresh (in seconds)', 'monitoring') . " : ";
            echo "</td>";
            echo "<td width='120'>";
            Dropdown::showNumber("_refresh", [
                    'value' => $_SESSION['plugin_monitoring']['_refresh'],
                    'min' => 30,
                    'max' => 1000,
                    'step' => 10]
            );
            echo "</td>";
        }
        echo "<td>";
        if ($reduced_interface) {
            echo "<strong>";
        }
        echo __('Reduced interface', 'monitoring') . " : ";
        if ($reduced_interface) {
            echo "&nbsp;" . Html::showToolTip(__('Only dispays the items that are not OK/UP.', 'monitoring'));
            echo "</strong>";
        }
        echo "</td>";
        echo "<td width='80'>";
        Dropdown::showYesNo("reduced_interface", $_SESSION['plugin_monitoring']['reduced_interface']);
        echo "</td>";
        echo "<td align='center'>";
        echo "<input type='submit' name='sessionupdate' class='submit' value=\"" . __('Post') . "\">";
        echo "</td>";
        echo "</tr>";

        echo "</table>";
        Html::closeForm();
    }


    /**
     * Include an image marker in front of the current dashboard part
     *
     * @param string $dashboard_part
     *
     * @return bool true if marker was inserted
     */
    function includePartMarker($dashboard_part)
    {
        global $CFG_GLPI;

        $split = explode("/", $_SERVER['PHP_SELF']);
        if ($split[(count($split) - 1)] == $dashboard_part . ".php") {
            echo "<img src='" . $CFG_GLPI['root_doc'] . "/" . PLUGIN_MONITORING_PATH . "/pics/arrow-right.png' /> ";
            return true;
        }

        return false;
    }
}