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
/*
function plugin_monitoring_giveItem($type, $id, $data, $num)
{

//   $searchopt = &Search::getOptions($type);
//   $table = $searchopt[$id]["table"];
//   $field = $searchopt[$id]["field"];
//
//   switch ($table.'.'.$field) {
//
//   }

    return "";
}
*/


function plugin_monitoring_getAddSearchOptions($itemtype)
{

    $sopt = [];
    $index = 19000;

    if ($itemtype == 'Entity') {
        // Monitoring framework tag for the entity
        $index++;
        $sopt[$index]['table'] = 'glpi_plugin_monitoring_entities';
        $sopt[$index]['field'] = 'tag';
        $sopt[$index]['datatype'] = 'string';
        $sopt[$index]['name'] = __('Entity tag', 'monitoring');
        $sopt[$index]['joinparams'] = ['jointype' => 'child'];
        $sopt[$index]['massiveaction'] = false;

        // Monitoring objects definition order for the entity
        $index++;
        $sopt[$index]['table'] = 'glpi_plugin_monitoring_entities';
        $sopt[$index]['field'] = 'definition_order';
        $sopt[$index]['datatype'] = 'number';
        $sopt[$index]['name'] = __('Definition order', 'monitoring');
        $sopt[$index]['joinparams'] = ['jointype' => 'child'];
        $sopt[$index]['massiveaction'] = false;

        // Graphite prefix for the entity
        $index++;
        $sopt[$index]['table'] = 'glpi_plugin_monitoring_entities';
        $sopt[$index]['field'] = 'graphite_prefix';
        $sopt[$index]['datatype'] = 'string';
        $sopt[$index]['name'] = __('Graphite prefix', 'monitoring');
        $sopt[$index]['joinparams'] = ['jointype' => 'child'];
        $sopt[$index]['massiveaction'] = false;

        // Calendar jet lag for the entity
        $index++;
        $sopt[$index]['table'] = 'glpi_plugin_monitoring_entities';
        $sopt[$index]['field'] = 'jet_lag';
        $sopt[$index]['datatype'] = 'number';
        $sopt[$index]['name'] = __('Jet lag', 'monitoring');
        $sopt[$index]['joinparams'] = ['jointype' => 'child'];
        $sopt[$index]['massiveaction'] = false;
    }

    if ($itemtype == 'User') {
        // Contact template for user
        $index++;
        $sopt[$index]['table'] = 'glpi_plugin_monitoring_contacttemplates';
        $sopt[$index]['field'] = 'name';
        $sopt[$index]['datatype'] = 'itemtype';
        $sopt[$index]['name'] = __('User template', 'monitoring');
        $sopt[$index]['joinparams'] = [
            'beforejoin' => [
                'table' => 'glpi_plugin_monitoring_contacts',
                'joinparams' => ['jointype' => 'child']]];
        $sopt[$index]['massiveaction'] = false;

        // user in alignak backend
        $index++;
        $sopt[$index]['table'] = 'glpi_plugin_monitoring_users';
        $sopt[$index]['field'] = 'backend_login';
        $sopt[$index]['datatype'] = 'string';
        $sopt[$index]['name'] = __('alignak account name', 'monitoring');
        $sopt[$index]['joinparams'] = ['jointype' => 'child'];
        $sopt[$index]['massiveaction'] = false;

    }

    return $sopt;
}


/* Cron */
function cron_plugin_monitoring()
{
    return 1;
}


/**
 * Plugin install process
 *
 * @return boolean True if success
 */
function plugin_monitoring_install()
{

    $version = plugin_version_monitoring();
    $migration = new Migration($version['version']);
    require_once(__DIR__ . '/install/install.php');
    $install = new PluginMonitoringInstall();
    if (!$install->isPluginInstalled()) {
        return $install->install($migration);
    }
    return $install->upgrade($migration);
}


/**
 * Plugin uninstall process
 */
function plugin_monitoring_uninstall()
{
    require_once(__DIR__ . '/install/install.php');
    $install = new PluginMonitoringInstall();
    $install->uninstall(true);
}


//function plugin_headings_monitoring_status($item)
//{
//
//    echo "<br/>Http :<br/>";
//
//    $pmHostevent = new PluginMonitoringHostevent();
//    $pmHostevent->showForm($item);
//
//}


//function plugin_headings_monitoring_dashboadservicecatalog($item)
//{
//    $pmServicescatalog = new PluginMonitoringServicescatalog();
//    $pmDisplay = new PluginMonitoringDisplay();
//
//    $pmDisplay->showCounters("Businessrules");
//    $pmServicescatalog->showChecks();
//}


//function plugin_headings_monitoring_tasks($item, $itemtype = '', $items_id = 0)
//{
//
//}


//function plugin_headings_monitoring($item, $withtemplate = 0)
//{
//
//}


function plugin_monitoring_MassiveActionsFieldsDisplay($options = [])
{

    return false;
}


function plugin_monitoring_MassiveActions($type)
{

    switch ($type) {

        case "PluginMonitoringComponentscatalog":
            return [
                "plugin_monitoring_playrule_componentscatalog" => __('Force play rules', 'monitoring')
            ];
            break;

        case "PluginMonitoringDisplayview":
            return [
                "plugin_monitoring_playrule_displayview" => __('Force play rules', 'monitoring')
            ];
            break;

        case "User":
            return [
                'PluginMonitoringUser' . MassiveAction::CLASS_ACTION_SEPARATOR . 'createalignakuser' => __('Create alignak backend account', 'monitoring')
            ];
    }

    return [];
}


function plugin_monitoring_MassiveActionsDisplay($options = [])
{
    PluginMonitoringToolbox::logIfDebug("MassiveActionsDisplay: " . $options['itemtype']);
    switch ($options['itemtype']) {

        case "PluginMonitoringComponentscatalog":
            if ($options['action'] == 'plugin_monitoring_playrule_componentscatalog') {
                echo "<input name='add' value='Post' class='submit' type='submit'>";
            }
            break;

        case "PluginMonitoringDisplayview":
            if ($options['action'] == 'plugin_monitoring_playrule_displayview') {
                echo "<input name='add' value='Post' class='submit' type='submit'>";
            }
            break;
    }

    return "";
}


function plugin_monitoring_MassiveActionsProcess($data)
{
    PluginMonitoringToolbox::logIfDebug("MassiveActionsProcess: " . $data['action']);
    switch ($data['action']) {

        case 'plugin_monitoring_playrule_componentscatalog':
            $pmComponentscatalog_rule = new PluginMonitoringComponentscatalog_rule();
            foreach ($data['item'] as $key => $val) {
                $a_rules = $pmComponentscatalog_rule->find("`plugin_monitoring_componentscatalogs_id`='" . $key . "'");
                foreach ($a_rules as $data) {
                    $pmComponentscatalog_rule->getFromDB($data['id']);
                    PluginMonitoringComponentscatalog_rule::getItemsDynamically($pmComponentscatalog_rule);
                }
            }
            break;

        case 'plugin_monitoring_playrule_displayview':
            $pmDisplayview_rule = new PluginMonitoringDisplayview_rule();
            foreach ($data['item'] as $key => $val) {
                $a_rules = $pmDisplayview_rule->find("`plugin_monitoring_displayviews_id`='" . $key . "'");
                foreach ($a_rules as $data) {
                    $pmDisplayview_rule->getFromDB($data['id']);
                    PluginMonitoringDisplayview_rule::getItemsDynamically($pmDisplayview_rule);
                }
            }
            break;

    }
    return TRUE;
}


/*
function plugin_monitoring_addSelect($type, $id, $num)
{
    if (in_array($type, ['PluginMonitoringService', 'PluginMonitoringHost'])) {
        PluginMonitoringToolbox::log("addSelect: $type, id: $id, num: $num");

        $searchopt = &Search::getOptions($type);
        $table = $searchopt[$id]["table"];
        $field = $searchopt[$id]["field"];

        if ($type == 'PluginMonitoringService') {
            if ($table . "." . $field == 'glpi_computers.name') {
                return " CONCAT_WS('', `glpi_computers`.`name`, `glpi_printers`.`name`, `glpi_networkequipments`.`name`) AS ITEM_$num,";
            }
        } else if ($type == 'PluginMonitoringHost') {
            if ($table . "." . $field == 'glpi_computers.name') {
                return " CONCAT_WS('', `glpi_computers`.`name`, `glpi_printers`.`name`, `glpi_networkequipments`.`name`) AS ITEM_$num,";
            }
        }
    }

    return "";
}
*/


function plugin_monitoring_forceGroupBy($type)
{
    return false;
}


function plugin_monitoring_addLeftJoin($itemtype, $ref_table, $new_table, $linkfield, &$already_link_tables)
{
//    PluginMonitoringToolbox::log("addLeftJoin: $itemtype, ref: $ref_table, new:$new_table, field: $linkfield");

    switch ($itemtype) {

        case 'PluginMonitoringNetworkport':
            $already_link_tables_tmp = $already_link_tables;
            array_pop($already_link_tables_tmp);

            $leftjoin_networkequipments = 1;
            if (in_array('glpi_states', $already_link_tables_tmp)
                OR in_array('glpi_networkequipments', $already_link_tables_tmp)) {
                $leftjoin_networkequipments = 0;
            }
            switch ($new_table . "." . $linkfield) {
                case "glpi_networkequipments.networkequipments_id" :
                    if ($leftjoin_networkequipments == '0') {
                        return " ";
                    }
                    return " LEFT JOIN `glpi_networkequipments` ON (`glpi_plugin_monitoring_networkports`.`items_id` = `glpi_networkequipments`.`id` ) ";
                    break;

                case "glpi_states.states_id":
                    if ($leftjoin_networkequipments == '1') {
                        return " LEFT JOIN `glpi_networkequipments` ON (`glpi_plugin_monitoring_networkports`.`items_id` = `glpi_networkequipments`.`id` )
                     LEFT JOIN `glpi_states` ON (`glpi_networkequipments`.`states_id` = `glpi_states`.`id` ) ";
                    } else {
                        return " LEFT JOIN `glpi_states` ON (`glpi_networkequipments`.`states_id` = `glpi_states`.`id` ) ";
                    }
                    break;
            }
            break;

        case 'Computer':
            if ($new_table . "." . $linkfield == "glpi_plugin_monitoring_computers_deviceprocessors.plugin_monitoring_computers_deviceprocessors_id") {
                return " LEFT JOIN `glpi_items_deviceprocessors` AS `processormonit` "
                    . " ON (`glpi_computers`.`id` = `processormonit`.`items_id`"
                    . " AND `processormonit`.`itemtype` = 'Computer') ";
            }
            break;

        case 'PluginMonitoringServiceevent':
//         // Join between service events and services
//         if ($new_table.".".$linkfield == "glpi_plugin_monitoring_services.id") {
//            return "
//               LEFT JOIN `glpi_plugin_monitoring_services`
//             ON (`glpi_plugin_monitoring_serviceevents`.`plugin_monitoring_services_id`
//             = `glpi_plugin_monitoring_services`.`id`)
//            ";
//         }
//         // Join between service events and components catalogs
//         if ($new_table.".".$linkfield == "glpi_plugin_monitoring_components.plugin_monitoring_components_id") {
//            return "
//               LEFT JOIN `glpi_plugin_monitoring_services`
//             ON (`glpi_plugin_monitoring_serviceevents`.`plugin_monitoring_services_id`
//             = `glpi_plugin_monitoring_services`.`id`)
//               LEFT JOIN `glpi_plugin_monitoring_components`
//             ON (`glpi_plugin_monitoring_services`.`plugin_monitoring_components_id`
//             = `glpi_plugin_monitoring_components`.`id`)
//            ";
//         }
//         // Join between service events and components catalogs hosts
//         if ($new_table.".".$linkfield == "glpi_plugin_monitoring_componentscatalogs_hosts.plugin_monitoring_componentscatalogs_hosts_id") {
//            return "
//               LEFT JOIN `glpi_plugin_monitoring_services` as servicess
//             ON (`glpi_plugin_monitoring_serviceevents`.`plugin_monitoring_services_id`
//             = servicess.`id`)
//            ";
//         }
//         // Join between service events and computers
//         if ($new_table.".".$linkfield == "glpi_computers.computers_id") {
//            return "
//               LEFT JOIN `glpi_plugin_monitoring_componentscatalogs_hosts`
//             ON (glpi_plugin_monitoring_services.`plugin_monitoring_componentscatalogs_hosts_id`
//             = `glpi_plugin_monitoring_componentscatalogs_hosts`.`id`)
//               LEFT JOIN `glpi_computers`
//             ON (`glpi_plugin_monitoring_componentscatalogs_hosts`.`items_id` = `glpi_computers`.`id`
//                AND
//                `glpi_plugin_monitoring_componentscatalogs_hosts`.`itemtype` = 'Computer')
//            ";
//         }
            break;

        case 'PluginMonitoringUnavailability':
            // Join between unavailabilities and services
            if ($new_table . "." . $linkfield == "glpi_plugin_monitoring_services.id") {
                return "
                INNER JOIN `glpi_plugin_monitoring_services`
              ON (`glpi_plugin_monitoring_unavailabilities`.`plugin_monitoring_services_id`
              = `glpi_plugin_monitoring_services`.`id`)
             ";
            }
            // Join between unavailabilities and components catalogs
            if ($new_table . "." . $linkfield == "glpi_plugin_monitoring_components.plugin_monitoring_components_id") {
                return "
                INNER JOIN `glpi_plugin_monitoring_components`
              ON (`glpi_plugin_monitoring_services`.`plugin_monitoring_components_id`
              = `glpi_plugin_monitoring_components`.`id`)
             ";
            }
            // Join between unavailabilities and components catalogs hosts
            if ($new_table . "." . $linkfield == "glpi_plugin_monitoring_componentscatalogs_hosts.plugin_monitoring_componentscatalogs_hosts_id") {
                return "
                INNER JOIN `glpi_plugin_monitoring_componentscatalogs_hosts`
              ON (`glpi_plugin_monitoring_services`.`plugin_monitoring_componentscatalogs_hosts_id`
              = `glpi_plugin_monitoring_componentscatalogs_hosts`.`id`)
             ";
            }
            // Join between unavailabilities and computers
            if ($new_table . "." . $linkfield == "glpi_computers.computers_id") {
                $ret = '';
                if (!in_array('glpi_plugin_monitoring_services', $already_link_tables)) {
                    $ret .= "
                   LEFT JOIN `glpi_plugin_monitoring_services`
                     ON (`glpi_plugin_monitoring_unavailabilities`.`plugin_monitoring_services_id`
                           = `glpi_plugin_monitoring_services`.`id`)
                ";
                }
                $ret .= "
                LEFT JOIN `glpi_plugin_monitoring_componentscatalogs_hosts`
                  ON (`glpi_plugin_monitoring_services`.`plugin_monitoring_componentscatalogs_hosts_id`
                     = `glpi_plugin_monitoring_componentscatalogs_hosts`.`id`)
                LEFT JOIN `glpi_computers`
                  ON (`glpi_plugin_monitoring_componentscatalogs_hosts`.`items_id` = `glpi_computers`.`id`
                     AND
                  `glpi_plugin_monitoring_componentscatalogs_hosts`.`itemtype` = 'Computer')
             ";
                return $ret;
            }
            if ($new_table . "." . $linkfield == "glpi_networkequipments.networkequipments_id") {
                return "LEFT JOIN `glpi_networkequipments`
                  ON (`glpi_plugin_monitoring_componentscatalogs_hosts`.`items_id` = `glpi_networkequipments`.`id`
                     AND
                  `glpi_plugin_monitoring_componentscatalogs_hosts`.`itemtype` = 'NetworkEquipment')";
            }
            break;

        case 'PluginMonitoringDowntime':
            // Join between downtimes and computers
            if ($new_table . "." . $linkfield == "glpi_computers.computers_id") {
                return "
                INNER JOIN `glpi_plugin_monitoring_hosts`
             ON (`glpi_plugin_monitoring_downtimes`.`plugin_monitoring_hosts_id` = `glpi_plugin_monitoring_hosts`.`id`)
                INNER JOIN `glpi_computers`
                  ON (`glpi_plugin_monitoring_hosts`.`items_id` = `glpi_computers`.`id`)
             ";
            }
            break;

        case 'PluginMonitoringService':
            // glpi_plugin_monitoring_services, new:glpi_plugin_monitoring_hosts, field: plugin_monitoring_componentscatalogs_hosts_id

            if ($new_table . "/" . $linkfield == "glpi_computers/plugin_monitoring_componentscatalogs_hosts_id") {
                return "
                LEFT JOIN `glpi_plugin_monitoring_componentscatalogs_hosts`
                    ON `plugin_monitoring_componentscatalogs_hosts_id` = `glpi_plugin_monitoring_componentscatalogs_hosts`.`id`
                LEFT JOIN `glpi_computers`
                    ON `glpi_plugin_monitoring_componentscatalogs_hosts`.`items_id` = `glpi_computers`.`id` 
                    AND `glpi_plugin_monitoring_componentscatalogs_hosts`.`itemtype`='Computer'
                ";

            } else if ($new_table . "/" . $linkfield == 'glpi_plugin_monitoring_hosts/plugin_monitoring_componentscatalogs_hosts_id') {
                return "
                LEFT JOIN `glpi_plugin_monitoring_componentscatalogs_hosts`
                    ON `plugin_monitoring_componentscatalogs_hosts_id` = `glpi_plugin_monitoring_componentscatalogs_hosts`.`id`
                LEFT JOIN `glpi_plugin_monitoring_hosts` AS `glpi_plugin_monitoring_hosts_plugin_monitoring_componentscatalogs_hosts_id`
                    ON (`glpi_plugin_monitoring_componentscatalogs_hosts`.`itemtype` =
                    `glpi_plugin_monitoring_hosts_plugin_monitoring_componentscatalogs_hosts_id`.`itemtype`
                       AND `glpi_plugin_monitoring_componentscatalogs_hosts`.`items_id` =
                           `glpi_plugin_monitoring_hosts_plugin_monitoring_componentscatalogs_hosts_id`.`items_id`)";
            }
            break;

        case 'PluginMonitoringHost':
            if ($new_table . "." . $linkfield == "glpi_computers.computers_id") {
                return "
               LEFT JOIN `glpi_computers`
                  ON `glpi_plugin_monitoring_hosts`.`items_id` = `glpi_computers`.`id`
                        AND `glpi_plugin_monitoring_hosts`.`itemtype`='Computer'
               LEFT JOIN `glpi_printers`
                  ON `glpi_plugin_monitoring_hosts`.`items_id` = `glpi_printers`.`id`
                     AND `glpi_plugin_monitoring_hosts`.`itemtype`='Printer'
               LEFT JOIN `glpi_networkequipments`
                  ON `glpi_plugin_monitoring_hosts`.`items_id` = `glpi_networkequipments`.`id`
                     AND `glpi_plugin_monitoring_hosts`.`itemtype`='NetworkEquipment' ";
            }
            break;

    }
    return "";
}


function plugin_monitoring_addOrderBy($type, $id, $order, $key = 0)
{
    return "";
}


function plugin_monitoring_addDefaultWhere($type)
{

    switch ($type) {
        case "PluginMonitoringDisplayview" :
            $who = Session::getLoginUserID();
            return " (`glpi_plugin_monitoring_displayviews`.`users_id` = '$who' OR `glpi_plugin_monitoring_displayviews`.`users_id` = '0') ";
            break;
    }
    return "";
}

/*
function plugin_monitoring_addWhere($link, $nott, $type, $id, $val)
{
    PluginMonitoringToolbox::log("addWhere: $link, type: $type, id: $id, val: $val");

    $searchopt = &Search::getOptions($type);
    $table = $searchopt[$id]["table"];
    $field = $searchopt[$id]["field"];

    switch ($type) {
        // Computer List (front/computer.php)
        case 'PluginMonitoringService':
            switch ($table . "." . $field) {
                case "glpi_plugin_monitoring_services.Computer":
                case "glpi_plugin_monitoring_services.Printer":
                case "glpi_plugin_monitoring_services.NetworkEquipment":
                    return $link . " (`glpi_plugin_monitoring_componentscatalogs_hosts`.`items_id` = '" . $val . "') ";
                    break;
            }
            break;

        case 'PluginMonitoringHost':
            switch ($table . "." . $field) {
                case "glpi_plugin_monitoring_hosts.name":
                    return $link . " (CONCAT_WS('', `glpi_computers`.`name`, `glpi_printers`.`name`, `glpi_networkequipments`.`name`) LIKE '%" . $val . "%') ";
                    break;
            }
            break;
    }

    return "";
}
*/

/*
 * Web services registering
 */
function plugin_monitoring_registerMethods()
{
    global $WEBSERVICES_METHOD;

    # Web services for the monitoring framework configuration
    $WEBSERVICES_METHOD['monitoring.getConfigAll'] = [
        'PluginMonitoringWebservice', 'methodgetConfig'
    ];
    $WEBSERVICES_METHOD['monitoring.getConfigCommands'] = [
        'PluginMonitoringWebservice', 'methodgetConfigCommands'
    ];
    $WEBSERVICES_METHOD['monitoring.getConfigHosts'] = [
        'PluginMonitoringWebservice', 'methodgetConfigHosts'
    ];
    $WEBSERVICES_METHOD['monitoring.getConfigRealms'] = [
        'PluginMonitoringWebservice', 'methodgetConfigRealms'
    ];
    $WEBSERVICES_METHOD['monitoring.getConfigHostgroups'] = [
        'PluginMonitoringWebservice', 'methodgetConfigHostgroups'
    ];
    $WEBSERVICES_METHOD['monitoring.getConfigContacts'] = [
        'PluginMonitoringWebservice', 'methodgetConfigContacts'
    ];
    $WEBSERVICES_METHOD['monitoring.getConfigTimeperiods'] = [
        'PluginMonitoringWebservice', 'methodgetConfigTimeperiods'
    ];
    $WEBSERVICES_METHOD['monitoring.getConfigServicesTemplates'] = [
        'PluginMonitoringWebservice', 'methodgetConfigServicesTemplates'
    ];
    $WEBSERVICES_METHOD['monitoring.getConfigServices'] = [
        'PluginMonitoringWebservice', 'methodgetConfigServices'
    ];

    # Other web services
    $WEBSERVICES_METHOD['monitoring.getMonitoredEntities'] = [
        'PluginMonitoringWebservice', 'methodgetMonitoredEntities'
    ];
    $WEBSERVICES_METHOD['monitoring.dashboard'] = [
        'PluginMonitoringWebservice', 'methodDashboard'
    ];
    $WEBSERVICES_METHOD['monitoring.getServicesList'] = [
        'PluginMonitoringWebservice', 'methodGetServicesList'
    ];
    $WEBSERVICES_METHOD['monitoring.getHostsStates'] = [
        'PluginMonitoringWebservice', 'methodGetHostsStates'
    ];
    $WEBSERVICES_METHOD['monitoring.getServicesStates'] = [
        'PluginMonitoringWebservice', 'methodGetServicesStates'
    ];
    $WEBSERVICES_METHOD['monitoring.getHostsLocations'] = [
        'PluginMonitoringWebservice', 'methodGetHostsLocations'
    ];
    $WEBSERVICES_METHOD['monitoring.getUnavailabilities'] = [
        'PluginMonitoringWebservice', 'methodGetUnavailabilities'
    ];
}


/**
 * Define Dropdown tables to be managed in GLPI :
 **/
function plugin_monitoring_getDropdown()
{

    return [
        'PluginMonitoringCheck' => __('Check definitions', 'monitoring'),
        'PluginMonitoringCommand' => __('Commands', 'monitoring'),
        'PluginMonitoringRealm' => __('Realms', 'monitoring'),
        'PluginMonitoringTag' => __('Tags', 'monitoring'),
        'PluginMonitoringComponentscatalog' => __('Components catalogs', 'monitoring'),
        'PluginMonitoringContacttemplate' => __('Contact templates', 'monitoring'),
        'PluginMonitoringHostnotificationtemplate' => __('Host notification templates', 'monitoring'),
        'PluginMonitoringServicenotificationtemplate' => __('Service notification templates', 'monitoring'),
        'PluginMonitoringComponent' => __('Components', 'monitoring')
    ];
}


function plugin_monitoring_searchOptionsValues($item)
{
    global $CFG_GLPI;

    // Fred : Add a log to check whether this function is still called ...
    PluginMonitoringToolbox::log("********** plugin_monitoring_searchOptionsValues is called ... still used?");
    // Search options for services
    if ($item['searchoption']['table'] == 'glpi_plugin_monitoring_services'
        AND $item['searchoption']['field'] == 'state') {
        $input = [];
        $input['CRITICAL'] = 'CRITICAL';
        $input['DOWNTIME'] = 'DOWNTIME';
        $input['FLAPPING'] = 'FLAPPING';
        $input['OK'] = 'OK';
        $input['RECOVERY'] = 'RECOVERY';
        $input['UNKNOWN'] = 'UNKNOWN';
        $input['WARNING'] = 'WARNING';

        Dropdown::showFromArray($item['name'], $input, ['value' => $item['value']]);
        return true;
    } else if ($item['searchoption']['table'] == 'glpi_plugin_monitoring_services'
        AND $item['searchoption']['field'] == 'state_type') {
        $input = [];
        $input['HARD'] = 'HARD';
        $input['SOFT'] = 'SOFT';

        Dropdown::showFromArray($item['name'], $input, ['value' => $item['value']]);
        return true;
    } else if ($item['searchoption']['table'] == 'glpi_plugin_monitoring_services'
        AND ($item['searchoption']['field'] == 'Computer'
            OR $item['searchoption']['field'] == 'Printer'
            OR $item['searchoption']['field'] == 'NetworkEquipment')) {

        $itemtype = $item['searchoption']['field'];

        $use_ajax = false;

        if ($CFG_GLPI["use_ajax"]) {
            $nb = countElementsInTable("glpi_plugin_monitoring_componentscatalogs_hosts", "`itemtype`='Computer'");
            if ($nb > $CFG_GLPI["ajax_limit_count"]) {
                $use_ajax = true;
            }
        }

        $params = [];
        $params['itemtype'] = $itemtype;
        $params['searchText'] = '';
        $params['myname'] = $item['name'];
        $params['rand'] = '';
        $params['value'] = $item['value'];

        $default = "<select name='" . $item['name'] . "' id='dropdown_" . $item['name'] . "0'>";
        if (isset($item['value'])
            AND !empty($item['value'])) {
            /* @var  CommonDBTM $itemm */
            $itemm = new $itemtype();
            $itemm->getFromDB($item['value']);
            $default .= "<option value='" . $item['value'] . "'>" . $itemm->getName() . "</option></select>";
        }

        Ajax::dropdown($use_ajax, "/plugins/monitoring/ajax/dropdownDevices.php", $params, $default);

        return true;
    }

    // Search options for hosts
    if ($item['searchoption']['table'] == 'glpi_plugin_monitoring_hosts'
        AND $item['searchoption']['field'] == 'state') {
        $input = [];
        $input['DOWN'] = 'DOWN';
        $input['DOWNTIME'] = 'DOWNTIME';
        $input['FLAPPING'] = 'FLAPPING';
        $input['RECOVERY'] = 'RECOVERY';
        $input['UNKNOWN'] = 'UNKNOWN';
        $input['UNREACHABLE'] = 'UNREACHABLE';
        $input['UP'] = 'UP';

        Dropdown::showFromArray($item['name'], $input, ['value' => $item['value']]);
        return true;
    } else if ($item['searchoption']['table'] == 'glpi_plugin_monitoring_hosts'
        AND $item['searchoption']['field'] == 'state_type') {
        $input = [];
        $input['HARD'] = 'HARD';
        $input['SOFT'] = 'SOFT';

        Dropdown::showFromArray($item['name'], $input, ['value' => $item['value']]);
        return true;
    }
}


function plugin_monitoring_ReplayRulesForItem($args)
{
    PluginMonitoringToolbox::log("plugin_monitoring_ReplayRulesForItem: " . print_r($args, true));

    /* @var CommonDBTM $item */
    $itemtype = $args[0];
    $items_id = $args[1];
    $item = new $itemtype();
    $item->getFromDB($items_id);
    PluginMonitoringComponentscatalog_rule::doesThisItemVerifyRule($item);
}


function plugin_monitoring_postinit()
{
    global $CFG_GLPI;

    if (isset($_SESSION['glpiactiveprofile']['interface'])
        and $_SESSION['glpiactiveprofile']['interface'] == 'helpdesk') {
        if (strstr($_SERVER['PHP_SELF'], "/helpdesk.public.php")) {
            if (count($_GET) == 0
                and count($_POST) == 0) {
                $pmredirecthome = new PluginMonitoringRedirecthome();
                if ($pmredirecthome->is_redirect($_SESSION['glpiID'])) {
                    Html::redirect($CFG_GLPI["root_doc"] . "/plugins/monitoring/front/dashboard.php");
                    exit;
                }
            }
        }
    }
}
