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

// Direct access to file
if (strpos($_SERVER['PHP_SELF'], "restart_host.php")) {
    include("../../../inc/includes.php");
    header("Content-Type: text/html; charset=UTF-8");
    Html::header_nocache();
}

if (!defined('GLPI_ROOT')) {
    die("Can not acces directly to this file");
}

Session::checkLoginUser();

PluginMonitoringToolbox::log("restart_host, server data: " . print_r($_SERVER, true));

PluginMonitoringToolbox::log("restart_host, Request for a restart of an host, posted data: " . print_r($_POST, true));

Session::checkRight("plugin_monitoring_host_actions", CREATE);

//$redirect = $CFG_GLPI["root_doc"] . "/plugins/monitoring/index.php";
$redirect = $_SERVER['HTTP_REFERER'];

/*
 * Fusion inventory installed?
 */
if (!class_exists("PluginFusioninventoryAgent")) {
    PluginMonitoringToolbox::log("restart_host, plugin Fusion inventory is not installed!");
    Html::redirect($redirect);
}

/*
 * Posted data?
 */
if (empty($_POST)) {
    PluginMonitoringToolbox::log("restart_host, no posted data!");
    Html::redirect($redirect);
}

/*
 * Check posted data?
 */
if (!isset($_POST['host_command'])) {
    PluginMonitoringToolbox::log("restart_host, parameters: missing host_command");
    Html::redirect($redirect);
}
$host_command = $_POST['host_command'];
if (!isset($_POST['host_id'])) {
    PluginMonitoringToolbox::log("restart_host, parameters: missing host_id");
    Html::redirect($redirect);
}
$host_id = $_POST['host_id'];
if (!isset($_POST['host_name'])) {
    PluginMonitoringToolbox::log("restart_host, parameters: missing host_name");
    Html::redirect($redirect);
}
$host_name = $_POST['host_name'];
$computer = new Computer();
if (!$computer->getFromDBByCrit(['name' => $host_name])) {
    PluginMonitoringToolbox::log("restart_host, computer named $host_name not found in the database!");
    Html::redirect($redirect);
}

/*
 * Get FusionInventory agent related to the computer
 */
$agent = new PluginFusioninventoryAgent();
$agent_id = $agent->getAgentWithComputerid($host_id);
if ($agent_id === false) {
    $message = "restart_host, no FI agent for the computer named $host_name.";
    PluginMonitoringToolbox::log($message);
    Session::addMessageAfterRedirect($message, WARNING);
    Html::redirect($redirect);
}

/*
 * Get FusionInventory task related with the host command ...
 */
$pfTaskjob = new PluginFusioninventoryTaskjob();
$a_lists = $pfTaskjob->find("name LIKE '$host_command'", '', 1);
if (count($a_lists) == 0) {
    $message = "restart_host, FI task not found for the command $host_command.";
    PluginMonitoringToolbox::log($message);
    Session::addMessageAfterRedirect($message, ERROR);
    Html::redirect($redirect);
}
$a_list = current($a_lists);
$taskjob_id = $a_list['id'];
$definition = importArrayFromDB($a_list['definition']);

/*
 Pour les valeurs :
 $query = "INSERT INTO `glpi_plugin_fusioninventory_taskjobstates`
      (`plugin_fusioninventory_taskjobs_id`, `items_id`, `itemtype`, `state`,
       `plugin_fusioninventory_agents_id`, `uniqid`)
      VALUES ('0', '0', 'PluginFusioninventoryDeployPackage', '0', '0', '".uniqid()."')";
 '0', => l'id du job dans glpi_plugin_fusioninventory_taskjobs (fixe a chaque exécution)
 '0', => l'id du package 'PluginFusioninventoryDeployPackage',
 'PluginFusioninventoryDeployPackage' => c'est l'itemtype, donc on ne touche pas
 '0', => c'est le statut donc toujours 0 (=préparé)
 '0', => c'est l'id de l'agent de l'ordinateur, que tu peux récupérer l'id via la fonction PluginFusioninventoryAgent::getAgentWithComputerid('idducomputer')
 */
$query = "INSERT INTO `glpi_plugin_fusioninventory_taskjobstates`
   (`plugin_fusioninventory_taskjobs_id`, `items_id`, `itemtype`, `state`,
    `plugin_fusioninventory_agents_id`, `uniqid`)
   VALUES
   ('$taskjob_id', '" . $definition[0]['PluginFusioninventoryDeployPackage'] . "',
    'PluginFusioninventoryDeployPackage', '0',
    '$agent_id', '" . uniqid() . "')";

try {
    $result = $DB->query($query);
    $message = sprintf(__("Host command '%s' requested for the host '%s'", 'monitoring'),
        $host_command, $host_name );
    PluginMonitoringToolbox::log($message);
    Session::addMessageAfterRedirect($message);
} catch (Exception $e) {
    $message = sprintf(__("Host command '%s' requested for the host '%s', failed. Error: %s", 'monitoring'),
        $host_command, $host_name, $e->getMessage());
    PluginMonitoringToolbox::log($message);
    Session::addMessageAfterRedirect($message, ERROR);
}

Html::redirect($redirect);
