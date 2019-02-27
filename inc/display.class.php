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

class PluginMonitoringDisplay extends CommonDBTM
{
    static $ar_counterTypes;

    function dashboard($refresh = false)
    {
        global $CFG_GLPI;

        $redirect = FALSE;
        $a_url = [];

        echo "<table class='tab_cadre'>";
        echo "<tr>";

        /*
         * Add monitoring framework restart commands if necessary
         */
        if (Session::haveRight("plugin_monitoring_command_fmwk", CREATE)) {
            echo "<td style='width: 17%; padding: 1%;'>";

            PluginMonitoringDisplay::restartFramework();

            echo "</td>";
            echo "<td style='width: 77%; padding: 1%;'>";
        } else {
            echo "<td style='width: 97%; padding: 1%;'>";
        }

        echo "<table class='tab_cadre_fixe' width='950'>";
        echo "<tr class='tab_bg_3'>";
        echo "<td>";

        if (Session::haveRight("plugin_monitoring_systemstatus", PluginMonitoringProfile::DASHBOARD)) {
            echo "<table class='tab_cadre_fixe'>";
            echo "<tr class='tab_bg_1'>";

            echo "<th colspan='2'>";
            $this->displayPuce('display_system_status');
            echo "<a href='" . $CFG_GLPI['root_doc'] . "/plugins/monitoring/front/status_system.php'>";
            echo __('System status', 'monitoring');
            echo "</a>";
            $pmTag = new PluginMonitoringTag();
            $servers = 'OK';
            if (!$pmTag->get_servers_status()) {
                $servers = 'CRITICAL';
            }
            echo "<div class='service service" . $servers . "' style='float : left;'></div>";
            $a_url[] = $CFG_GLPI['root_doc'] . "/plugins/monitoring/front/status_system.php";
            echo "</th>";


            echo "<th colspan='2'>";
            $this->displayPuce('host');
            echo "<a href='" . $CFG_GLPI['root_doc'] . "/plugins/monitoring/front/host.php'>";
            echo __('Hosts status', 'monitoring');
            echo "</a>";
            $a_url[] = $CFG_GLPI['root_doc'] . "/plugins/monitoring/front/host.php";
            echo "</th>";


            echo "<th colspan='2'>";
            $this->displayPuce('service');
            echo "<a href='" . $CFG_GLPI['root_doc'] . "/plugins/monitoring/front/service.php'>";
            echo __('All resources', 'monitoring');
            echo "</a>";
            $a_url[] = $CFG_GLPI['root_doc'] . "/plugins/monitoring/front/service.php";
            echo "</th>";

            echo "</tr>";
            echo "</table>";
        } else {
            if (basename($_SERVER['PHP_SELF']) == 'display_servicescatalog.php') {
                $redirect = true;
            } else if (basename($_SERVER['PHP_SELF']) == 'display_componentscatalog.php') {
                $redirect = true;
            } else if (basename($_SERVER['PHP_SELF']) == 'service.php') {
                $redirect = true;
            } else if (basename($_SERVER['PHP_SELF']) == 'host.php') {
                $redirect = true;
            }
        }

        if (Session::haveRight("plugin_monitoring_displayview", PluginMonitoringProfile::DASHBOARD)) {
            $i = 1;
            $pmDisplayview = new PluginMonitoringDisplayview();
            $a_views = $pmDisplayview->getViews();
            if (count($a_views) > 0) {
                echo "<table class='tab_cadre_fixe' width='950'>";
                echo "<tr class='tab_bg_1'>";

                foreach ($a_views as $views_id => $name) {
                    $pmDisplayview->getFromDB($views_id);
                    if ($pmDisplayview->haveVisibilityAccess()) {
                        if ($i == 6) {
                            echo "</tr>";
                            echo "<tr class='tab_bg_1'>";
                            $i = 1;
                        }
                        echo "<th width='20%'>";
                        $this->displayPuce('display_view', $views_id);
                        echo "<a href='" . $CFG_GLPI['root_doc'] . "/plugins/monitoring/front/display_view.php?id=" . $views_id . "'>";
                        echo htmlentities($name);
                        echo "</a>";
                        echo "</th>";
                        $i++;
                        $a_url[] = $CFG_GLPI['root_doc'] . "/plugins/monitoring/front/display_view.php?id=" . $views_id;
                    }
                }
                // Fred : what is it for ?
                // It's to finish properly the table
                /** @noinspection PhpExpressionResultUnusedInspection */
                for ($i; $i < 6; $i++) {
                    echo "<td width='20%'>";
                    echo "</td>";
                }
                echo "</tr>";
                echo "</table>";
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


    function defineTabs($options = [])
    {
        PluginMonitoringToolbox::logIfDebug("********** defineTabs ... no more used function ?");

        if (isset($_GET['glpi_tab'])) {
            Session::setActiveTab("PluginMonitoringDisplay", $_GET['glpi_tab']);
        }

        $pmDisplayview = new PluginMonitoringDisplayview();

        $ong = [];
        if (Session::haveRight("plugin_monitoring_systemstatus", PluginMonitoringSystem::DASHBOARD)) {
            $ong[1] = __('System status', 'monitoring');
        }
        if (Session::haveRight("plugin_monitoring_hoststatus", PluginMonitoringHost::DASHBOARD)) {
            $ong[2] = __('Hosts status', 'monitoring');
        }
//        if (Session::haveRight("plugin_monitoring_servicescatalog", PluginMonitoringServicescatalog::DASHBOARD)) {
//            $ong[3] = __('Services catalog', 'monitoring');
//        }
        if (Session::haveRight("plugin_monitoring_componentscatalog", PluginMonitoringComponentscatalog::DASHBOARD)) {
            $ong[4] = __('Components catalog', 'monitoring');
        }
        if (Session::haveRight("plugin_monitoring_service", READ)) {
            $ong[5] = __('All resources', 'monitoring');
        }
        $ong[6] = __('Dependencies;', 'monitoring');
        if (Session::haveRight("plugin_monitoring_displayview", PluginMonitoringDisplayview::DASHBOARD)) {
            $i = 7;
            $a_views = $pmDisplayview->getViews();
            foreach ($a_views as $name) {
                $ong[$i] = htmlentities($name);
                $i++;
            }
        }
        return $ong;
    }


    function showTabs($options = [])
    {
        global $CFG_GLPI;

        PluginMonitoringToolbox::log(
            "********** showTabs ... no more used function ?\n"
        );

        // for objects not in table like central
        $ID = 0;
        if (isset($this->fields['id'])) {
            $ID = $this->fields['id'];
        }

        $target = $_SERVER['PHP_SELF'];
        $extraparamhtml = "";
        $extraparam = "";
        $withtemplate = "";

        if (is_array($options) && count($options)) {
            if (isset($options['withtemplate'])) {
                $withtemplate = $options['withtemplate'];
            }
            foreach ($options as $key => $val) {
                $extraparamhtml .= "&amp;$key=$val";
                $extraparam .= "&$key=$val";
            }
        }

        if (empty($withtemplate) && $ID && $this->getType() && $this->displaylist) {
            $glpilistitems =& $_SESSION['glpilistitems'][$this->getType()];
            $glpilisttitle =& $_SESSION['glpilisttitle'][$this->getType()];
            $glpilisturl =& $_SESSION['glpilisturl'][$this->getType()];

            if (empty($glpilisturl)) {
                $glpilisturl = $this->getSearchURL();
            }

            echo "<div id='menu_navigate'>";

            $next = $prev = $first = $last = -1;
            $current = false;
            if (is_array($glpilistitems)) {
                $current = array_search($ID, $glpilistitems);
                if ($current !== false) {

                    if (isset($glpilistitems[$current + 1])) {
                        $next = $glpilistitems[$current + 1];
                    }

                    if (isset($glpilistitems[$current - 1])) {
                        $prev = $glpilistitems[$current - 1];
                    }

                    $first = $glpilistitems[0];
                    if ($first == $ID) {
                        $first = -1;
                    }

                    $last = $glpilistitems[count($glpilistitems) - 1];
                    if ($last == $ID) {
                        $last = -1;
                    }

                }
            }
            $cleantarget = Html::cleanParametersURL($target);
            echo "<ul>";
            echo "<li><a href=\"javascript:showHideDiv('tabsbody','tabsbodyimg','" . $CFG_GLPI["root_doc"] .
                "/pics/deplier_down.png','" . $CFG_GLPI["root_doc"] . "/pics/deplier_up.png')\">";
            echo "<img alt='' name='tabsbodyimg' src=\"" . $CFG_GLPI["root_doc"] . "/pics/deplier_up.png\">";
            echo "</a></li>";

            echo "<li><a href=\"" . $glpilisturl . "\">";

            if ($glpilisttitle) {
                if (Toolbox::strlen($glpilisttitle) > $_SESSION['glpidropdown_chars_limit']) {
                    $glpilisttitle = Toolbox::substr($glpilisttitle, 0,
                            $_SESSION['glpidropdown_chars_limit']) . "&hellip;";
                }
                echo $glpilisttitle;

            } else {
                echo __('List');
            }
            echo "</a>&nbsp;:&nbsp;</li>";

            if ($first > 0) {
                echo "<li><a href='$cleantarget?id=$first$extraparamhtml'><img src='" .
                    $CFG_GLPI["root_doc"] . "/pics/first.png' alt=\"" . __('First') .
                    "\" title=\"" . __('First') . "\"></a></li>";
            } else {
                echo "<li><img src='" . $CFG_GLPI["root_doc"] . "/pics/first_off.png' alt=\"" .
                    __('First') . "\" title=\"" . __('First') . "\"></li>";
            }

            if ($prev > 0) {
                echo "<li><a href='$cleantarget?id=$prev$extraparamhtml'><img src='" .
                    $CFG_GLPI["root_doc"] . "/pics/left.png' alt=\"" . __('Previous') .
                    "\" title=\"" . __('Previous') . "\"></a></li>";
            } else {
                echo "<li><img src='" . $CFG_GLPI["root_doc"] . "/pics/left_off.png' alt=\"" .
                    __('Previous') . "\" title=\"" . __('Previous') . "\"></li>";
            }

            if ($current !== false) {
                echo "<li>" . ($current + 1) . "/" . count($glpilistitems) . "</li>";
            }

            if ($next > 0) {
                echo "<li><a href='$cleantarget?id=$next$extraparamhtml'><img src='" .
                    $CFG_GLPI["root_doc"] . "/pics/right.png' alt=\"" . __('Next') .
                    "\" title=\"" . __('Next') . "\"></a></li>";
            } else {
                echo "<li><img src='" . $CFG_GLPI["root_doc"] . "/pics/right_off.png' alt=\"" .
                    __('Next') . "\" title=\"" . __('Next') . "\"></li>";
            }

            if ($last > 0) {
                echo "<li><a href='$cleantarget?id=$last$extraparamhtml'><img alt=\"Last\" src=\"" .
                    $CFG_GLPI["root_doc"] . "/pics/last.png\" alt=\"" . __('Last') .
                    "\" title=\"" . __('Last') . "\"></a></li>";
            } else {
                echo "<li><img src='" . $CFG_GLPI["root_doc"] . "/pics/last_off.png' alt=\"" .
                    __('Last') . "\" title=\"" . __('Last') . "\"></li>";
            }
            echo "</ul></div>";
            echo "<div class='sep'></div>";
        }

        echo "<div id='tabspanel' class='center-h'></div>";

        $onglets = $this->defineTabs($options);
        $display_all = true;
        if (isset($onglets['no_all_tab'])) {
            $display_all = false;
            unset($onglets['no_all_tab']);
        }
        $class = $this->getType();
        if ($_SESSION['glpi_use_mode'] == Session::DEBUG_MODE
            && ($ID > 0 || $this->showdebug)
            && (method_exists($class, 'showDebug')
                || in_array($class, $CFG_GLPI["infocom_types"])
                || in_array($class, $CFG_GLPI["reservation_types"]))) {

            $onglets[-2] = __('Debug');
        }

        if (count($onglets)) {
            $tabpage = $this->getTabsURL();
            $tabs = [];

            foreach ($onglets as $key => $val) {
                $tabs[$key] = ['title' => $val,
                    'url' => $tabpage,
                    'params' => "target=$target&itemtype=" . $this->getType() .
                        "&glpi_tab=$key&id=$ID$extraparam"];
            }

            $plug_tabs = Plugin::getTabs($target, $this, $withtemplate);
            $tabs += $plug_tabs;
            // Not all tab for templates and if only 1 tab
            if ($display_all && empty($withtemplate) && count($tabs) > 1) {
                $tabs[-1] = ['title' => __('All'),
                    'url' => $tabpage,
                    'params' => "target=$target&itemtype=" . $this->getType() .
                        "&glpi_tab=-1&id=$ID$extraparam"];
            }
            Ajax::createTabs('tabspanel', 'tabcontent', $tabs, $this->getType(), "'100%'");
        }
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
        $ignored_columns = [2, 3, 4];

        $data = Search::prepareDatasForSearch($params['itemtype'], $params, $search_columns);
        $data['tocompute'] = $data['toview'];
        Search::constructSQL($data);
        PluginMonitoringToolbox::log("-> " . print_r($data, true));
//        die("test");
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

        PluginMonitoringToolbox::log("Columns: " . print_r($columns, true));

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

        PluginMonitoringDisplay::$ar_counterTypes = [];
        PluginMonitoringToolbox::loadLib();
        PluginMonitoringToolbox::log("Display {$data['data']['count']} service lines:");

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

                $this->displayLine($row, $columns, $ignored_columns, true, $perfdatas);
            }
        }
        echo "</table>";
        echo "<br/>";

        if (isset($data['data']['count']) and $data['data']['count'] > 0) {
            Html::printPager($start, $total_count, $target, $parameters);
        }

        if ($perfdatas) {
            foreach (PluginMonitoringDisplay::$ar_counterTypes as $counter_id => $counter_name) {
                PluginMonitoringToolbox::log("Counter type +++ : $counter_id => $counter_name");
            }
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
        PluginMonitoringToolbox::log("-> " . print_r($data, true));
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
        if (Session::haveRight("plugin_monitoring_host_actions", CREATE)) {
            $pmCommand = new PluginMonitoringCommand();
            if ($pmCommand->getFromDBByCrit(['command_name' => 'host_action'])) {
                $host_action = true;
                $host_command_name = $pmCommand->getName();
                $host_command_command = $pmCommand->getField('command_line');
                $columns[] = __('Host action', 'monitoring');
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
                // todo: check for improvements!
                $a_ret = PluginMonitoringHost::getServicesState(
                    $row['id'],
                    "`glpi_plugin_monitoring_services`.`state` != 'OK' 
                    AND `glpi_plugin_monitoring_services`.`is_acknowledged` = '0'");
                $row[] = [
                    'count' => 1,
                    [
                        'name' => "test" . $a_ret[1]
                    ],
                    'displayname' => "test" . $a_ret[0]
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
                            'name' => $host_command_command
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


    static function displayLine($data, $columns, $ignored_columns, $displayhost = true, $displayCounters = true)
    {
        global $CFG_GLPI;

        $pm_Service = new PluginMonitoringService();
        $pm_Service->getFromDB($data['id']);
        $pm_Component = new PluginMonitoringComponent();
        $pm_Component->getFromDB($data[2][0]['id']);

        PluginMonitoringToolbox::log("- row: " . print_r($data, true));

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
//                    . "hidesearch=1"
//                    . "&reset=reset"
                    . "&criteria[0][field]=" . 5
                    . "&criteria[0][searchtype]=contains"
                    . "&criteria[0][value]=^" . $data[$index][0]['name'] . "$"
                    . "&itemtype=PluginMonitoringHost"
                    . "&start=0'";
                $data[$index]['displayname'] = Html::link($data[$index]['displayname'], $link);
            }
            // http://127.0.0.1/glpi-9.3/plugins/monitoring/front/host.php?as_map=0&
            //criteria%5B0%5D%5Bfield%5D=5&criteria%5B0%5D%5Bsearchtype%5D=contains&
            //criteria%5B0%5D%5Bvalue%5D=%5Eek3k-cnam-0001%24&search=Search&itemtype=PluginMonitoringHost&start=0&_glpi_csrf_token=e0e51cbcc503f81afdafd71c03879516
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
                $scriptName = $CFG_GLPI['root_doc'] . "/plugins/monitoring/scripts/" . $data[$index][0]['name'];
                // Host name and IP
                $scriptArgs = $data[1][0]['name'] . " " . $data[12][0]['name'];

                echo "<td class='center'>";
                echo "<form name='form' method='post'
        action='" . $CFG_GLPI['root_doc'] . "/plugins/monitoring/front/" . $data[$index][0]['name'] . ".php'>";

//                echo "<input type='hidden' name='host_id' value='" . $data[9]['displayname'] . "' />";
//                echo "<input type='hidden' name='host_name' value='" . $data[2]['displayname'] . "' />";
//                echo "<input type='hidden' name='host_ip' value='" . $data['ip'] . "' />";
//                echo "<input type='hidden' name='host_state' value='" . $data[3]['displayname'] . "' />";
//                echo "<input type='hidden' name='host_statetype' value='" . $data[4]['displayname'] . "' />";
//                echo "<input type='hidden' name='host_event' value='" . $data[6]['displayname'] . "' />";
//                echo "<input type='hidden' name='host_perfdata' value='" . $data[7]['displayname'] . "' />";
//                echo "<input type='hidden' name='host_last_check' value='" . $data[5]['displayname'] . "' />";
                echo "<input type='hidden' name='glpi_users_id' value='" . $_SESSION['glpiID'] . "' />";

                echo "<input type='submit' name='host_command' value=\"" . $data[$index][0]['name'] . "\" class='submit'>";
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


    function displayGraphs($itemtype, $items_id)
    {
        echo "<h1>NOT IMPLEMENTED !!!!</h1>";

    }


    function displayCounters($type, $display = true)
    {
        global $DB, $CFG_GLPI;

        $ok = 0;
        $warningdata = 0;
        $warningconnection = 0;
        $critical = 0;
        $ok_soft = 0;
        $warningdata_soft = 0;
        $warningconnection_soft = 0;
        $critical_soft = 0;
        $acknowledge = 0;

        $play_sound = 0;

        if ($type == 'Ressources') {
            $ok = $this->countServicesQuery("
               `glpi_plugin_monitoring_services`.`state_type`='HARD'
               AND `glpi_plugin_monitoring_services`.`state`='OK'
               AND `glpi_plugin_monitoring_hosts`.`is_acknowledged`='0'
               AND `glpi_plugin_monitoring_services`.`is_acknowledged`='0'");

            $warningdata = $this->countServicesQuery("
               `glpi_plugin_monitoring_services`.`state_type`='HARD'
               AND (
                     (`glpi_plugin_monitoring_services`.`state`='WARNING' AND `glpi_plugin_monitoring_services`.`output` IS NOT NULL AND `glpi_plugin_monitoring_services`.`output` <> '') OR
                     (`glpi_plugin_monitoring_services`.`state`='RECOVERY') OR
                     (`glpi_plugin_monitoring_services`.`state`='FLAPPING')
               )
               AND `glpi_plugin_monitoring_hosts`.`is_acknowledged`='0'
               AND `glpi_plugin_monitoring_services`.`is_acknowledged`='0'");

            $warningconnection = $this->countServicesQuery("
               `glpi_plugin_monitoring_services`.`state_type`='HARD'
               AND (
                     (`glpi_plugin_monitoring_services`.`state`='WARNING' AND `glpi_plugin_monitoring_services`.`output` IS NULL) OR
                     (`glpi_plugin_monitoring_services`.`state`='UNKNOWN') OR
                     (`glpi_plugin_monitoring_services`.`state` IS NULL)
               )
               AND `glpi_plugin_monitoring_hosts`.`is_acknowledged`='0'
               AND `glpi_plugin_monitoring_services`.`is_acknowledged`='0'");

            $critical = $this->countServicesQuery("
               `glpi_plugin_monitoring_services`.`state_type`='HARD'
               AND `glpi_plugin_monitoring_services`.`state`='CRITICAL'
               AND `glpi_plugin_monitoring_hosts`.`is_acknowledged`='0'
               AND `glpi_plugin_monitoring_services`.`is_acknowledged`='0'");


            $ok_soft = $this->countServicesQuery("
               `glpi_plugin_monitoring_services`.`state_type`!='HARD'
               AND `glpi_plugin_monitoring_services`.`state`='OK'
               AND `glpi_plugin_monitoring_hosts`.`is_acknowledged`='0'
               AND `glpi_plugin_monitoring_services`.`is_acknowledged`='0'");

            $warningdata_soft = $this->countServicesQuery("
               `glpi_plugin_monitoring_services`.`state_type`!='HARD'
               AND (
                     (`glpi_plugin_monitoring_services`.`state`='WARNING' AND `glpi_plugin_monitoring_services`.`output` IS NOT NULL) OR
                     (`glpi_plugin_monitoring_services`.`state`='RECOVERY') OR
                     (`glpi_plugin_monitoring_services`.`state`='FLAPPING')
               )
               AND `glpi_plugin_monitoring_hosts`.`is_acknowledged`='0'
               AND `glpi_plugin_monitoring_services`.`is_acknowledged`='0'");

            $warningconnection_soft = $this->countServicesQuery("
               `glpi_plugin_monitoring_services`.`state_type`!='HARD'
               AND (
                     (`glpi_plugin_monitoring_services`.`state`='WARNING' AND `glpi_plugin_monitoring_services`.`output` IS NULL) OR
                     (`glpi_plugin_monitoring_services`.`state`='UNKNOWN') OR
                     (`glpi_plugin_monitoring_services`.`state` IS NULL)
               )
               AND `glpi_plugin_monitoring_hosts`.`is_acknowledged`='0'
               AND `glpi_plugin_monitoring_services`.`is_acknowledged`='0'");

            $critical_soft = $this->countServicesQuery("
               `glpi_plugin_monitoring_services`.`state_type`!='HARD'
               AND `glpi_plugin_monitoring_services`.`state`='CRITICAL'
               AND `glpi_plugin_monitoring_hosts`.`is_acknowledged`='0'
               AND `glpi_plugin_monitoring_services`.`is_acknowledged`='0'");


            $acknowledge = $this->countServicesQuery("
               `glpi_plugin_monitoring_services`.`is_acknowledged`='1'");
            // `glpi_plugin_monitoring_hosts`.`is_acknowledged`='1'
            // OR `glpi_plugin_monitoring_services`.`is_acknowledged`='1'");

            // ** Manage play sound if critical increase since last refresh
            if (isset($_SESSION['plugin_monitoring_dashboard_Ressources'])) {
                if ($critical > $_SESSION['plugin_monitoring_dashboard_Ressources']) {
                    $play_sound = 1;
                }
            }
            $_SESSION['plugin_monitoring_dashboard_Ressources'] = $critical;

        } else if ($type == 'Componentscatalog') {
            $pmComponentscatalog_Host = new PluginMonitoringComponentscatalog_Host();
            $queryCat = "SELECT * FROM `glpi_plugin_monitoring_componentscatalogs`";
            $resultCat = $DB->query($queryCat);
            while ($data = $DB->fetch_array($resultCat)) {

                $query = "SELECT COUNT(*) AS cpt FROM `" . $pmComponentscatalog_Host->getTable() . "`
               LEFT JOIN `glpi_plugin_monitoring_services`
                  ON `plugin_monitoring_componentscatalogs_hosts_id`=`" . $pmComponentscatalog_Host->getTable() . "`.`id`
               WHERE `plugin_monitoring_componentscatalogs_id`='" . $data['id'] . "'
                  AND (`state`='DOWN' OR `state`='UNREACHABLE' OR `state`='CRITICAL' OR `state`='DOWNTIME')
                  AND `state_type`='HARD'
                  AND `entities_id` IN (" . $_SESSION['glpiactiveentities_string'] . ")
                  AND `is_acknowledged`='0'";
//            PluginMonitoringToolbox::log("Query critical - $query\n");
                $result = $DB->query($query);
                $data2 = $DB->fetch_assoc($result);
                if ($data2['cpt'] > 0) {
                    $critical++;
                } else {
                    $query = "SELECT COUNT(*) AS cpt, `glpi_plugin_monitoring_services`.`state`
                     FROM `" . $pmComponentscatalog_Host->getTable() . "`
                  LEFT JOIN `glpi_plugin_monitoring_services`
                     ON `plugin_monitoring_componentscatalogs_hosts_id`=`" . $pmComponentscatalog_Host->getTable() . "`.`id`
                  WHERE `plugin_monitoring_componentscatalogs_id`='" . $data['id'] . "'
                     AND (`state`='WARNING' OR `state`='UNKNOWN' OR `state`='RECOVERY' OR `state`='FLAPPING' OR `state` IS NULL)
                     AND `state_type`='HARD'
                     AND `entities_id` IN (" . $_SESSION['glpiactiveentities_string'] . ")
                     AND `is_acknowledged`='0'";
                    $result = $DB->query($query);
                    $data2 = $DB->fetch_assoc($result);
                    if ($data2['cpt'] > 0) {
                        $warningdata++;
                    } else {
                        $query = "SELECT COUNT(*) AS cpt FROM `" . $pmComponentscatalog_Host->getTable() . "`
                     LEFT JOIN `glpi_plugin_monitoring_services`
                        ON `plugin_monitoring_componentscatalogs_hosts_id`=`" . $pmComponentscatalog_Host->getTable() . "`.`id`
                     WHERE `plugin_monitoring_componentscatalogs_id`='" . $data['id'] . "'
                     AND (`state`='OK' OR `state`='UP') AND `state_type`='HARD'
                     AND `entities_id` IN (" . $_SESSION['glpiactiveentities_string'] . ")
                     AND `is_acknowledged`='0'";
                        $result = $DB->query($query);
                        $data2 = $DB->fetch_assoc($result);
                        if ($data2['cpt'] > 0) {
                            $ok++;
                        }
                    }
                }

                $query = "SELECT COUNT(*) AS cpt FROM `" . $pmComponentscatalog_Host->getTable() . "`
               LEFT JOIN `glpi_plugin_monitoring_services`
                  ON `plugin_monitoring_componentscatalogs_hosts_id`=`" . $pmComponentscatalog_Host->getTable() . "`.`id`
               WHERE `plugin_monitoring_componentscatalogs_id`='" . $data['id'] . "'
                  AND (`state`='DOWN' OR `state`='UNREACHABLE' OR `state`='CRITICAL' OR `state`='DOWNTIME')
                  AND `state_type`='SOFT'
                  AND `entities_id` IN (" . $_SESSION['glpiactiveentities_string'] . ")
                  AND `is_acknowledged`='0'";
                $result = $DB->query($query);
                $data2 = $DB->fetch_assoc($result);
                if ($data2['cpt'] > 0) {
                    $critical_soft++;
                } else {
                    $query = "SELECT COUNT(*) AS cpt FROM `" . $pmComponentscatalog_Host->getTable() . "`
                  LEFT JOIN `glpi_plugin_monitoring_services`
                     ON `plugin_monitoring_componentscatalogs_hosts_id`=`" . $pmComponentscatalog_Host->getTable() . "`.`id`
                  WHERE `plugin_monitoring_componentscatalogs_id`='" . $data['id'] . "'
                     AND (`state`='WARNING' OR `state`='UNKNOWN' OR `state`='RECOVERY' OR `state`='FLAPPING' OR `state` IS NULL)
                     AND `state_type`='SOFT'
                     AND `entities_id` IN (" . $_SESSION['glpiactiveentities_string'] . ")
                     AND `is_acknowledged`='0'";
                    $result = $DB->query($query);
                    $data2 = $DB->fetch_assoc($result);
                    if ($data2['cpt'] > 0) {
                        $warningdata_soft++;
                    } else {
                        $query = "SELECT COUNT(*) AS cpt FROM `" . $pmComponentscatalog_Host->getTable() . "`
                     LEFT JOIN `glpi_plugin_monitoring_services`
                        ON `plugin_monitoring_componentscatalogs_hosts_id`=`" . $pmComponentscatalog_Host->getTable() . "`.`id`
                     WHERE `plugin_monitoring_componentscatalogs_id`='" . $data['id'] . "'
                        AND (`state`='OK' OR `state`='UP') AND `state_type`='SOFT'
                        AND `entities_id` IN (" . $_SESSION['glpiactiveentities_string'] . ")
                        AND `is_acknowledged`='0'";
                        $result = $DB->query($query);
                        $data2 = $DB->fetch_assoc($result);
                        if ($data2['cpt'] > 0) {
                            $ok_soft++;
                        }
                    }
                }
            }

            // ** Manage play sound if critical increase since last refresh
            if (isset($_SESSION['plugin_monitoring_dashboard_Componentscatalog'])) {
                if ($critical > $_SESSION['plugin_monitoring_dashboard_Componentscatalog']) {
                    $play_sound = 1;
                }
            }
            $_SESSION['plugin_monitoring_dashboard_Componentscatalog'] = $critical;

        } else if ($type == 'Businessrules') {
            $ok = countElementsInTable("glpi_plugin_monitoring_servicescatalogs",
                "(`state`='OK' OR `state`='UP') AND `state_type`='HARD'
                 AND `entities_id` IN (" . $_SESSION['glpiactiveentities_string'] . ")
                 AND `is_acknowledged`='0'");

            $warningdata = countElementsInTable("glpi_plugin_monitoring_servicescatalogs",
                "(`state`='WARNING' OR `state`='UNKNOWN'
                        OR `state`='RECOVERY' OR `state`='FLAPPING')
                    AND `state_type`='HARD'
                    AND `entities_id` IN (" . $_SESSION['glpiactiveentities_string'] . ")
                    AND `is_acknowledged`='0'");

            $critical = countElementsInTable("glpi_plugin_monitoring_servicescatalogs",
                "(`state`='DOWN' OR `state`='UNREACHABLE' OR `state`='CRITICAL' OR `state`='DOWNTIME')
                    AND `state_type`='HARD'
                    AND `entities_id` IN (" . $_SESSION['glpiactiveentities_string'] . ")
                    AND `is_acknowledged`='0'");

            $warningdata_soft = countElementsInTable("glpi_plugin_monitoring_servicescatalogs",
                "(`state`='WARNING' OR `state`='UNKNOWN'
                        OR `state`='RECOVERY' OR `state`='FLAPPING')
                    AND `state_type`='SOFT'
                    AND `entities_id` IN (" . $_SESSION['glpiactiveentities_string'] . ")
                    AND `is_acknowledged`='0'");

            $critical_soft = countElementsInTable("glpi_plugin_monitoring_servicescatalogs",
                "(`state`='DOWN' OR `state`='UNREACHABLE' OR `state`='CRITICAL' OR `state`='DOWNTIME')
                    AND `state_type`='SOFT'
                    AND `entities_id` IN (" . $_SESSION['glpiactiveentities_string'] . ")
                    AND `is_acknowledged`='0'");

            $ok_soft = countElementsInTable("glpi_plugin_monitoring_servicescatalogs",
                "(`state`='OK' OR `state`='UP') AND `state_type`='SOFT'
                  AND `entities_id` IN (" . $_SESSION['glpiactiveentities_string'] . ")
                    AND `is_acknowledged`='0'");

            // ** Manage play sound if critical increase since last refresh
            if (isset($_SESSION['plugin_monitoring_dashboard_Businessrules'])) {
                if ($critical > $_SESSION['plugin_monitoring_dashboard_Businessrules']) {
                    $play_sound = 1;
                }
            }
            $_SESSION['plugin_monitoring_dashboard_Businessrules'] = $critical;

        }
        if (!$display) {
            $a_return = [];
            $a_return['ok'] = strval($ok);
            $a_return['ok_soft'] = strval($ok_soft);
            $a_return['warningdata'] = strval($warningdata);
            $a_return['warningconnection'] = strval($warningconnection);
            $a_return['warningdata_soft'] = strval($warningdata_soft);
            $a_return['warningconnection_soft'] = strval($warningconnection_soft);
            $a_return['critical'] = strval($critical);
            $a_return['critical_soft'] = strval($critical_soft);
            $a_return['acknowledge'] = strval($acknowledge);
            return $a_return;
        }

        $critical_link = $CFG_GLPI['root_doc'] .
            "/plugins/monitoring/front/service.php?hidesearch=1"
//              . "&reset=reset"
            . "&criteria[0][field]=3"
            . "&criteria[0][searchtype]=contains"
            . "&criteria[0][value]=CRITICAL"

            . "&criteria[1][link]=AND"
            . "&criteria[1][field]=7"
            . "&criteria[1][searchtype]=equals"
            . "&criteria[1][value]=0"

            . "&criteria[2][link]=AND"
            . "&criteria[2][field]=8"
            . "&criteria[2][searchtype]=equals"
            . "&criteria[2][value]=0"
            . "&search=Search"
            . "&itemtype=PluginMonitoringService"
            . "&start=0"
            . "&glpi_tab=3'";
        //_glpi_csrf_token=
        $warning_link = $CFG_GLPI['root_doc'] .
            "/plugins/monitoring/front/service.php?hidesearch=1"
//              . "&reset=reset"
            . "&criteria[0][field]=3"
            . "&criteria[0][searchtype]=contains"
            . "&criteria[0][value]=FLAPPING"

            . "&criteria[1][link]=AND"
            . "&criteria[1][field]=7"
            . "&criteria[1][searchtype]=equals"
            . "&criteria[1][value]=0"

            . "&criteria[2][link]=AND"
            . "&criteria[2][field]=8"
            . "&criteria[2][searchtype]=equals"
            . "&criteria[2][value]=0"

            . "&criteria[3][link]=OR"
            . "&criteria[3][field]=3"
            . "&criteria[3][searchtype]=contains"
            . "&criteria[3][value]=RECOVERY"

            . "&criteria[4][link]=AND"
            . "&criteria[4][field]=7"
            . "&criteria[4][searchtype]=equals"
            . "&criteria[4][value]=0"

            . "&criteria[5][link]=AND"
            . "&criteria[5][field]=8"
            . "&criteria[5][searchtype]=equals"
            . "&criteria[5][value]=0"

            . "&criteria[6][link]=OR"
            . "&criteria[6][field]=3"
            . "&criteria[6][searchtype]=contains"
            . "&criteria[6][value]=UNKNOWN"

            . "&criteria[7][link]=AND"
            . "&criteria[7][field]=7"
            . "&criteria[7][searchtype]=equals"
            . "&criteria[7][value]=0"

            . "&criteria[8][link]=AND"
            . "&criteria[8][field]=8"
            . "&criteria[8][searchtype]=equals"
            . "&criteria[8][value]=0"

            . "&criteria[9][link]=AND"
            . "&criteria[9][field]=3"
            . "&criteria[9][searchtype]=contains"
            . "&criteria[9][value]=WARNING"

            . "&criteria[10][link]=AND"
            . "&criteria[10][field]=7"
            . "&criteria[10][searchtype]=equals"
            . "&criteria[10][value]=0"

            . "&criteria[11][link]=AND"
            . "&criteria[11][field]=8"
            . "&criteria[11][searchtype]=equals"
            . "&criteria[11][value]=0"

            . "&search=Search"
            . "&itemtype=PluginMonitoringService"
            . "&start=0"
            . "&glpi_tab=3'";
        $warningdata_link = $CFG_GLPI['root_doc'] .
            "/plugins/monitoring/front/service.php?hidesearch=1"
//              . "&reset=reset"
            . "&criteria[0][field]=3"
            . "&criteria[0][searchtype]=contains"
            . "&criteria[0][value]=FLAPPING"

            . "&criteria[1][link]=AND"
            . "&criteria[1][field]=7"
            . "&criteria[1][searchtype]=equals"
            . "&criteria[1][value]=0"

            . "&criteria[2][link]=AND"
            . "&criteria[2][field]=8"
            . "&criteria[2][searchtype]=equals"
            . "&criteria[2][value]=0"

            . "&criteria[2][link]=OR"
            . "&criteria[3][field]=3"
            . "&criteria[3][searchtype]=contains"
            . "&criteria[3][value]=RECOVERY"

            . "&criteria[4][link]=AND"
            . "&criteria[4][field]=7"
            . "&criteria[4][searchtype]=equals"
            . "&criteria[4][value]=0"

            . "&criteria[5][link]=AND"
            . "&criteria[5][field]=8"
            . "&criteria[5][searchtype]=equals"
            . "&criteria[5][value]=0"

            . "&criteria[6][link]=OR"
            . "&criteria[6][field]=3"
            . "&criteria[6][searchtype]=contains"
            . "&criteria[6][value]=WARNING"

            . "&criteria[7][link]=AND"
            . "&criteria[7][field]=7"
            . "&criteria[7][searchtype]=equals"
            . "&criteria[7][value]=0"

            . "&criteria[8][link]=AND"
            . "&criteria[8][field]=8"
            . "&criteria[8][searchtype]=equals"
            . "&criteria[8][value]=0"

            . "&itemtype=PluginMonitoringService"
            . "&start=0"
            . "&glpi_tab=3'";
        $warningconnection_link = $CFG_GLPI['root_doc'] .
            "/plugins/monitoring/front/service.php?hidesearch=1"
//              . "&reset=reset"
            . "&criteria[0][field]=3"
            . "&criteria[0][searchtype]=contains"
            . "&criteria[0][value]=UNKNOWN"

            . "&criteria[1][link]=AND"
            . "&criteria[1][field]=7"
            . "&criteria[1][searchtype]=equals"
            . "&criteria[1][value]=0"

            . "&criteria[2][link]=AND"
            . "&criteria[2][field]=8"
            . "&criteria[2][searchtype]=equals"
            . "&criteria[2][value]=0"

            . "&criteria[3][link]=OR"
            . "&criteria[3][field]=3"
            . "&criteria[3][searchtype]=contains"
            . "&criteria[3][value]=NULL"

            . "&criteria[4][link]=AND"
            . "&criteria[4][field]=7"
            . "&criteria[4][searchtype]=equals"
            . "&criteria[4][value]=0"

            . "&criteria[5][link]=AND"
            . "&criteria[5][field]=8"
            . "&criteria[5][searchtype]=equals"
            . "&criteria[5][value]=0"

            . "&itemtype=PluginMonitoringService"
            . "&start=0"
            . "&glpi_tab=3'";
        $ok_link = $CFG_GLPI['root_doc'] .
            "/plugins/monitoring/front/service.php?hidesearch=1"
//              . "&reset=reset"
            . "&criteria[0][field]=3"
            . "&criteria[0][searchtype]=contains"
            . "&criteria[0][value]=OK"

            . "&itemtype=PluginMonitoringService"
            . "&start=0"
            . "&glpi_tab=3'";
        $acknowledge_link = $CFG_GLPI['root_doc'] .
            "/plugins/monitoring/front/service.php?hidesearch=1"
//              . "&reset=reset"
            . "&criteria[0][field]=7"
            . "&criteria[0][searchtype]=equals"
            . "&criteria[0][value]=1"

            . "&itemtype=PluginMonitoringService"
            . "&start=0"
            . "&glpi_tab=3'";

        echo "<table align='center'>";
        echo "<tr>";
        echo "<td width='414'>";
        $background = '';
        if ($critical > 0) {
            $background = 'background="' . $CFG_GLPI['root_doc'] . '/plugins/monitoring/pics/bg_critical.png"';
        }
        echo "<table class='tab_cadre' width='100%' height='130' " . $background . " >";
        echo "<tr>";
        echo "<th style='background-color:transparent;'>";
        if ($type == 'Ressources' OR $type == 'Componentscatalog') {
            echo "<a href='" . $critical_link . ">" .
                "<font color='black' style='font-size: 12px;font-weight: bold;'>" . __('Critical', 'monitoring') . "</font></a>";
        } else {
            echo __('Critical', 'monitoring');
        }
        echo "</td>";
        echo "</tr>";
        echo "<tr>";
        echo "<th style='background-color:transparent;'>";
        if ($type == 'Ressources' OR $type == 'Componentscatalog') {
            echo "<a href='" . $critical_link . ">" .
                "<font color='black' style='font-size: 52px;font-weight: bold;'>" . $critical . "</font></a>";
        } else {
            echo "<font style='font-size: 52px;'>" . $critical . "</font>";
        }
        echo "</th>";
        echo "</tr>";
        echo "<tr><td>";
        echo "<p style='font-size: 11px; text-align: center;'> Soft : " . $critical_soft . "</p>";
        echo "</td></tr>";
        echo "</table>";
        echo "</td>";

        echo "<td width='188'>";
        $background = '';
        if ($warningdata > 0) {
            $background = 'background="' . $CFG_GLPI['root_doc'] . '/plugins/monitoring/pics/bg_warning.png"';
        }
        if ($type == 'Ressources') {
            echo "<table class='tab_cadre' width='100%' height='130' " . $background . " >";
        } else {
            echo "<table class='tab_cadre' width='100%' height='130' " . $background . " >";
        }
        echo "<tr>";
        echo "<th style='background-color:transparent;'>";
        if ($type == 'Ressources') {
            echo "<a href='" . $warningdata_link . ">" .
                "<font color='black' style='font-size: 12px;font-weight: bold;'>" . __('Warning', 'monitoring') . "</font></a>";
        } else {
            if ($type == 'Componentscatalog') {
                echo "<a href='" . $warning_link . ">" .
                    "<font color='black' style='font-size: 12px;font-weight: bold;'>" . __('Warning', 'monitoring') . "</font></a>";
            } else {
                echo __('Warning', 'monitoring');
            }
        }
        echo "</td>";
        echo "</tr>";
        echo "<tr>";
        echo "<th style='background-color:transparent;'>";
        if ($type == 'Ressources') {
            echo "<a href='" . $warningdata_link . ">" .
                "<font color='black' style='font-size: 52px;'>" . $warningdata . "</font></a>";
        } else if ($type == 'Componentscatalog') {
            echo "<a href='" . $warning_link . ">" .
                "<font color='black' style='font-size: 52px;'>" . $warningdata . "</font></a>";
        } else {
            echo "<font style='font-size: 52px;'>" . $warningdata . "</font>";
        }
        echo "</th>";
        echo "</tr>";
        echo "<tr><td>";
        echo "<p style='font-size: 11px; text-align: center;'> Soft : " . $warningdata_soft . "</p>";
        echo "</td></tr>";
        echo "</table>";
        echo "</td>";

        if ($type == 'Ressources') {
            echo "<td width='188'>";
            $background = '';
            if ($warningconnection > 0) {
                $background = 'background="' . $CFG_GLPI['root_doc'] . '/plugins/monitoring/pics/bg_warning_yellow.png"';
            }
            echo "<table class='tab_cadre' width='100%' height='130' " . $background . " >";
            echo "<tr>";
            echo "<th style='background-color:transparent;'>";
            if ($type == 'Ressources' OR $type == 'Componentscatalog') {
                echo "<a href='" . $warningconnection_link . ">" .
                    "<font color='black' style='font-size: 12px;font-weight: bold;'>" . __('Warning (connection)', 'monitoring') . "</font></a>";
            } else {
                echo __('Warning (connection)', 'monitoring');
            }
            echo "</td>";
            echo "</tr>";
            echo "<tr>";
            echo "<th style='background-color:transparent;'>";
            if ($type == 'Ressources' OR $type == 'Componentscatalog') {
                echo "<a href='" . $warningconnection_link . ">" .
                    "<font color='black' style='font-size: 52px;'>" . $warningconnection . "</font></a>";
            } else {
                echo "<font style='font-size: 52px;'>" . $warningconnection . "</font>";
            }
            echo "</th>";
            echo "</tr>";
            echo "<tr><td>";
            echo "<p style='font-size: 11px; text-align: center;'> Soft : " . $warningconnection_soft . "</p>";
            echo "</td></tr>";
            echo "</table>";
            echo "</td>";
        }

        echo "<td width='148'>";
        $background = '';
        if ($ok > 0) {
            $background = 'background="' . $CFG_GLPI['root_doc'] . '/plugins/monitoring/pics/bg_ok.png"';
        }
        echo "<table class='tab_cadre' width='100%' height='130' " . $background . " >";
        echo "<tr>";
        echo "<th style='background-color:transparent;'>";
        if ($type == 'Ressources' OR $type == 'Componentscatalog') {
            echo "<a href='" . $ok_link . ">" .
                "<font color='black' style='font-size: 12px;font-weight: bold;'>" . __('OK', 'monitoring') . "</font></a>";
        } else {
            echo __('OK', 'monitoring');
        }
        echo "</td>";
        echo "</tr>";
        echo "<tr>";
        echo "<th style='background-color:transparent;'>";
        if ($type == 'Ressources' OR $type == 'Componentscatalog') {
            echo "<a href='" . $ok_link . ">" .
                "<font color='black' style='font-size: 52px;font-weight: bold;'>" . $ok . "</font></a>";
        } else {
            echo "<font style='font-size: 52px;'>" . $ok . "</font>";
        }
        echo "</th>";
        echo "</tr>";
        echo "<tr><td>";
        echo "<p style='font-size: 11px; text-align: center;'> Soft : " . $ok_soft . "</p>";
        echo "</td></tr>";
        echo "</table>";
        echo "</td>";

        echo "<td width='120'>";
        $background = '';
        if ($acknowledge > 0) {
            $background = 'background="' . $CFG_GLPI['root_doc'] . '/plugins/monitoring/pics/bg_acknowledge.png"';
        }
        echo "<table class='tab_cadre' width='100%' height='130' " . $background . " >";
        echo "<tr>";
        echo "<th style='background-color:transparent;'>";
        if ($type == 'Ressources' OR $type == 'Componentscatalog') {
            echo "<a href='" . $acknowledge_link . "'>" .
                "<font color='black' style='font-size: 12px;font-weight: bold;'>" . __('Acknowledge', 'monitoring') . "</font></a>";
        } else {
            echo __('Acknowledge', 'monitoring');
        }
        echo "</td>";
        echo "</tr>";
        echo "<tr>";
        echo "<th style='background-color:transparent;'>";
        if ($type == 'Ressources' OR $type == 'Componentscatalog') {
            echo "<a href='" . $acknowledge_link . "'>" .
                "<font color='black' style='font-size: 52px;font-weight: bold;'>" . $acknowledge . "</font></a>";
        } else {
            echo "<font style='font-size: 52px;'>" . $acknowledge . "</font>";
        }
        echo "</th>";
        echo "</tr>";
        echo "<tr><td>";
        echo "<p style='font-size: 11px; text-align: center;'>&nbsp;</p>";
        echo "</td></tr>";
        echo "</table>";
        echo "</td>";

        echo "</tr>";
        echo "</table><br/>";

        // ** play sound
        if ($play_sound == '1') {
            echo '<audio autoplay="autoplay">
                 <source src="../audio/star-trek.ogg" type="audio/ogg" />
                 Your browser does not support the audio element.
               </audio>';
        }

        return [];
    }


    function displayHostsCounters($display = true)
    {
        global $CFG_GLPI;

        $play_sound = false;

        $up = $this->countHostsQuery([
            'state' => 'UP',
            'state_type' => 'HARD'
        ]);
        $up_soft = $this->countHostsQuery([
            'state' => 'UP',
            'state_type' => 'SOFT'
        ]);
        $unreachable = $this->countHostsQuery([
            'state' => 'UNREACHABLE',
            'state_type' => 'HARD',
            'is_acknowledged' => '0'
        ]);
        $unreachable_soft = $this->countHostsQuery([
            'state' => 'UNREACHABLE',
            'state_type' => 'SOFT',
            'is_acknowledged' => '0'
        ]);
        $unknown = $this->countHostsQuery([
            'state' => 'UNKNOWN',
            'state_type' => 'HARD',
            'is_acknowledged' => '0'
        ]);
        $unknown_soft = $this->countHostsQuery([
            'state' => 'UNKNOWN',
            'state_type' => 'SOFT',
            'is_acknowledged' => '0'
        ]);
        $down = $this->countHostsQuery([
            'state' => 'DOWN',
            'state_type' => 'HARD',
            'is_acknowledged' => '0'
        ]);
        $down_soft = $this->countHostsQuery([
            'state' => 'DOWN',
            'state_type' => 'SOFT',
            'is_acknowledged' => '0'
        ]);
        $acknowledge = $this->countHostsQuery([
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
        echo "<table class='center tab_cadre' style='width=80%'>";
        echo "<tr>";
        foreach ($a_states as $state => $status) {
            $link = $CFG_GLPI['root_doc'] .
                "/plugins/monitoring/front/host.php?hidesearch=1"
//              . "&reset=reset"
                . "&criteria[0][field]=5"
                . "&criteria[0][searchtype]=contains"
                . "&criteria[0][value]=" . strtoupper($state)

                . "&itemtype=PluginMonitoringHost"
                . "&start=0'";
            echo "<td class='center' style='width: 15%'>";
            echo "<a href='" . $link . ">";
            echo "<span class='font-$state' style='font-size: 12px;font-weight: bold;'>" . $status['label'] . "</span>";
            echo "</a>";
            echo "<br>";
            echo "<a href='" . $link . ">";
            echo "<span class='font-$state' style='font-size: 52px;font-weight: bold;'>" . $status['counter'] . "</span>";
            echo "</a>";
            echo "<br>";
            if ($status['soft'] >= 0) {
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


    function displayServicesCounters($display = true)
    {
        global $CFG_GLPI;

        $play_sound = false;

        // Get counters
        $ok = $this->countServicesQuery([
            'state' => 'OK',
            'state_type' => 'HARD'
        ]);
        $ok_soft = $this->countServicesQuery([
            'state' => 'OK',
            'state_type' => 'SOFT'
        ]);
        $unreachable = $this->countServicesQuery([
            'state' => 'UNREACHABLE',
            'state_type' => 'HARD',
            'is_acknowledged' => '0'
        ]);
        $unreachable_soft = $this->countServicesQuery([
            'state' => 'UNREACHABLE',
            'state_type' => 'SOFT',
            'is_acknowledged' => '0'
        ]);
        $unknown = $this->countServicesQuery([
            'state' => 'UNKNOWN',
            'state_type' => 'HARD',
            'is_acknowledged' => '0'
        ]);
        $unknown_soft = $this->countServicesQuery([
            'state' => 'UNKNOWN',
            'state_type' => 'SOFT',
            'is_acknowledged' => '0'
        ]);
        $critical = $this->countServicesQuery([
            'state' => 'CRITICAL',
            'state_type' => 'HARD',
            'is_acknowledged' => '0'
        ]);
        $critical_soft = $this->countServicesQuery([
            'state' => 'CRITICAL',
            'state_type' => 'SOFT',
            'is_acknowledged' => '0'
        ]);
        $warning = $this->countServicesQuery([
            'state' => 'WARNING',
            'state_type' => 'HARD',
            'is_acknowledged' => '0'
        ]);
        $warning_soft = $this->countServicesQuery([
            'state' => 'WARNING',
            'state_type' => 'SOFT',
            'is_acknowledged' => '0'
        ]);
        $acknowledge = $this->countServicesQuery([
            'is_acknowledged' => '1'
        ]);

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
            $a_states['unreachable'] = strval($unreachable);
            $a_states['unreachable_soft'] = strval($unreachable_soft);
            $a_states['unknown'] = strval($unknown);
            $a_states['unknown_soft'] = strval($unknown_soft);
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
            $a_states['unreachable'] = [
                'counter' => $unreachable, 'soft' => $unreachable_soft, 'label' => __('Unreachable', 'monitoring')
            ];
            $a_states['unknown'] = [
                'counter' => $unknown, 'soft' => $unknown_soft, 'label' => __('Unknown', 'monitoring')
            ];
            $a_states['acknowledge'] = [
                'counter' => $acknowledge, 'soft' => -1, 'label' => __('Acknowledged', 'monitoring')
            ];
        }

        // Hosts counters table
        echo "<table class='center tab_cadre' style='width=80%'>";
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
            echo "<td class='center' style='width: 15%'>";
            echo "<a href='" . $link . ">";
            echo "<span class='font-$state' style='font-size: 12px;font-weight: bold;'>" . $status['label'] . "</span>";
            echo "</a>";
            echo "<br>";
            echo "<a href='" . $link . ">";
            echo "<span class='font-$state' style='font-size: 52px;font-weight: bold;'>" . $status['counter'] . "</span>";
            echo "</a>";
            echo "<br>";
            if ($status['soft'] >= 0) {
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


    function countHostsQuery($whereState)
    {
        $dbu = new DbUtils();
        return $dbu->countElementsInTableForMyEntities(
            'glpi_plugin_monitoring_hosts', $whereState);
    }


    function countServicesQuery($whereState)
    {
        $dbu = new DbUtils();
        return $dbu->countElementsInTableForMyEntities(
            'glpi_plugin_monitoring_services', $whereState);
    }


    function showCounters($type, $display = 1, $ajax = 1)
    {
        global $CFG_GLPI;

        if ($display == 0) {
            $this->displayCounters($type, $display);
        } else if ($ajax == 1) {
            echo "<div id=\"updatecounter" . $type . "\"></div>";

            echo "<script type=\"text/javascript\">
               (function worker() {
                 $.get('" . $CFG_GLPI["root_doc"] . "/plugins/monitoring/ajax/updateCounter.php"
                . "?type=" . $type . "', function(data) {
                   $('#updatecounter" . $type . "').html(data);
                   setTimeout(worker, 50000);
                 });
               })();
            </script>";

        } else {
            $this->displayCounters($type);
        }
    }


    function showHostsCounters($display = true, $ajax = true)
    {
        if ($ajax) {
            PluginMonitoringToolbox::log("showHostsCounters called with ajax!");
        } else {
            $this->displayHostsCounters($display);
        }
    }


    function showServicesCounters($display = true, $ajax = true)
    {
        if ($ajax) {
            PluginMonitoringToolbox::log("showServicesCounters called with ajax!");
        } else {
            $this->displayServicesCounters($display);
        }
    }


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


    function displayPuce($scriptname, $items_id = '')
    {
        global $CFG_GLPI;

        $split = explode("/", $_SERVER['PHP_SELF']);
        if ($split[(count($split) - 1)] == $scriptname . ".php") {
            $display = false;
            if ($items_id != '') {
                if (isset($_GET['id'])
                    and $_GET['id'] == $items_id) {
                    $display = true;
                }
            } else {
                $display = true;
            }
            if ($display) {
                echo "<img src='" . $CFG_GLPI['root_doc'] . "/pics/right.png' /> ";
            }
        }
    }


    /**
     * Restart Monitoring framework buttons :
     * - on main Monitoring plugin page
     * - one button per each declared Shinken tag
     * - one button to restart all Shinken instances
     *
     * @global $CFG_GLPI
     */
    static function restartFramework()
    {
        global $CFG_GLPI;

        $pmTag = new PluginMonitoringTag();
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

            foreach ($fmwk_commands as $command) {
                echo '<table class="tab_cadre">';
                echo '<tr>';
                echo '<td style="width: 100px" onClick="$(\'#list_'. $command['command'] .'\').toggle();">';
                echo '<button title="'. $command['title'] .'">' . $command['button'] . '</button>';
                echo '</td>';
                echo '<td id="list_'. $command['command'] .'" style="display:none;">';
                echo '<ul>';
                $url = $CFG_GLPI['root_doc'] . "/plugins/monitoring/front/restart_fmwk.form.php?action=" . $command['command'];
                echo '<li>';
                echo '<a href="' . $url . '">' . __('All instances', 'monitoring') . '</a>';
                echo '</li>';
                if (count($a_tags) > 0) {
                    foreach ($a_tags as $taginfo => $data) {
                        $url = $CFG_GLPI['root_doc'] . "/plugins/monitoring/front/restart_fmwk.form.php?action=" . $command['command'] . "&tag=" . $data['id'];
                        echo '<li>';
                        echo '<a href="' . $url . '">' . $taginfo . '</a>';
                        echo '</li>';
                    }
                }
                echo '</ul>';
                echo '</td>';

                echo '</tr>';
                echo '</table>';
            }
            echo '<br/>';
        }
    }
}
