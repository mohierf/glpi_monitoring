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

class PluginMonitoringServiceevent extends CommonDBTM
{

    static $rightname = 'plugin_monitoring_service_event';

    static function getTypeName($nb = 0)
    {
        return __CLASS__;
    }


    function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        if (!$withtemplate) {
            switch ($item->getType()) {
                case 'Central':
                    if (Session::haveRight("plugin_monitoring_central", READ)
                        and Session::haveRight("plugin_monitoring_service_event", self::HOMEPAGE)) {
                        return [1 => __('Last monitoring events', 'monitoring')];
                    }
                    break;

//                case 'Computer':
//                    /* @var CommonDBTM $item */
//                    $array_ret = [];
//                    if ($item->getID() > 0 and self::canView()) {
//                        if (Session::haveRight("plugin_monitoring_service_event", READ)) {
//                            $array_ret[] = self::createTabEntry(__('Services events', 'monitoring'));
//                        }
//                    }
//                    return $array_ret;
//                    break;
            }
        }
        return '';
    }


    static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {

        if ($item->getType() == 'Computer') {
            if (self::canView()) {
//                // Show list filtered on computer, sorted on day descending ...
//                $_GET = [
//                    'field' => [22],
//                    'searchtype' => ['equals'],
//                    'contains' => [$item->getID()],
//                    'itemtype' => 'PluginMonitoringServiceevent',
//                    'start' => 0,
//                    'sort' => 3,
//                    'order' => 'DESC'];
//                Search::manageGetValues(self::getTypeName());
//                Search::showList(self::getTypeName(), $_GET);
//                return true;
            }
        }
        return true;
    }


    function getSearchOptions()
    {

        $tab = [];

        $tab['common'] = __('Service events', 'monitoring');

        $tab[1]['table'] = $this->getTable();
        $tab[1]['field'] = 'id';
        $tab[1]['linkfield'] = 'id';
        $tab[1]['name'] = __('ID');
        $tab[1]['datatype'] = 'itemlink';
        $tab[1]['massiveaction'] = false; // implicit field is id

        $tab[2]['table'] = 'glpi_plugin_monitoring_services';
        $tab[2]['field'] = 'name';
        $tab[2]['linkfield'] = 'plugin_monitoring_services_id';
        $tab[2]['name'] = __('Service instance', 'monitoring');
        $tab[2]['datatype'] = 'itemlink';

        $items_joinparams = [
            'beforejoin' => ['table' => 'glpi_plugin_monitoring_services']];

        $tab[21]['table'] = 'glpi_plugin_monitoring_components';
        $tab[21]['field'] = 'name';
        $tab[21]['name'] = __('Component', 'monitoring');
        $tab[21]['datatype'] = 'itemlink';
        $tab[21]['joinparams'] = $items_joinparams;

        $tab[22]['table'] = 'glpi_computers';
        $tab[22]['field'] = 'name';
        $tab[22]['linkfield'] = 'items_id';
        $tab[22]['name'] = __('Computer');
        $tab[22]['datatype'] = 'itemlink';
        $tab[22]['itemlink_type'] = 'Computer';
        $tab[22]['joinparams'] = [
            'condition' => " AND REFTABLE.itemtype='Computer' ",
            'beforejoin' => ['table' => 'glpi_plugin_monitoring_componentscatalogs_hosts',
                'joinparams' => $items_joinparams]];

        $tab[23]['table'] = 'glpi_networkequipments';
        $tab[23]['field'] = 'name';
        $tab[23]['linkfield'] = 'items_id';
        $tab[23]['name'] = _n('Network device', 'Network devices', 1);
        $tab[23]['datatype'] = 'itemlink';
        $tab[23]['itemlink_type'] = 'NetworkEquipment';
        $tab[23]['joinparams'] = [
            'condition' => " AND REFTABLE.itemtype='NetworkEquipment' ",
            'beforejoin' => ['table' => 'glpi_plugin_monitoring_componentscatalogs_hosts',
                'joinparams' => $items_joinparams]];

        $tab[24]['table'] = 'glpi_printers';
        $tab[24]['field'] = 'name';
        $tab[24]['linkfield'] = 'items_id';
        $tab[24]['name'] = __('Printer');
        $tab[24]['datatype'] = 'itemlink';
        $tab[24]['itemlink_type'] = 'Printer';
        $tab[24]['joinparams'] = [
            'condition' => " AND REFTABLE.itemtype='Printer' ",
            'beforejoin' => ['table' => 'glpi_plugin_monitoring_componentscatalogs_hosts',
                'joinparams' => $items_joinparams]];

        $tab[3]['table'] = $this->getTable();
        $tab[3]['field'] = 'date';
        $tab[3]['name'] = __('Date', 'monitoring');
        $tab[3]['datatype'] = 'datetime';
        $tab[3]['massiveaction'] = false;

        $tab[4]['table'] = $this->getTable();
        $tab[4]['field'] = 'output';
        $tab[4]['name'] = __('Event output', 'monitoring');
        $tab[4]['massiveaction'] = false;

        $tab[5]['table'] = $this->getTable();
        $tab[5]['field'] = 'perf_data';
        $tab[5]['name'] = __('Event performance data', 'monitoring');
        $tab[5]['massiveaction'] = false;

        $tab[6]['table'] = $this->getTable();
        $tab[6]['field'] = 'state';
        $tab[6]['name'] = __('Service state', 'monitoring');
        $tab[6]['massiveaction'] = false;

        $tab[7]['table'] = $this->getTable();
        $tab[7]['field'] = 'state_type';
        $tab[7]['name'] = __('Service state type', 'monitoring');
        $tab[7]['massiveaction'] = false;

        return $tab;
    }


    static function getSpecificValueToDisplay($field, $values, array $options = [])
    {

        if (!is_array($values)) {
            $values = [$field => $values];
        }
        switch ($field) {
            case 'plugin_monitoring_services_id':
                $pmService = new PluginMonitoringService();
                $pmService->getFromDB($values[$field]);
                return $pmService->getLink();
                break;
        }
        return parent::getSpecificValueToDisplay($field, $values, $options);
    }


    static function convert_datetime_timestamp($str)
    {

        list($date, $time) = explode(' ', $str);
        list($year, $month, $day) = explode('-', $date);
        list($hour, $minute, $second) = explode(':', $time);

        $timestamp = mktime($hour, $minute, $second, $month, $day, $year);

        return $timestamp;
    }


    function calculateUptime($hosts_id, $startDate, $endDate)
    {
        $a_list = $this->find("`plugin_monitoring_hosts_id`='" . $hosts_id . "'
         AND `date` > '" . date("Y-m-d H:i:s", $startDate) . "'
         AND `date` < '" . date("Y-m-d H:i:s", $endDate) . "'", "date");

        $a_list_before = $this->find("`plugin_monitoring_hosts_id`='" . $hosts_id . "'
         AND `date` < '" . date("Y-m-d H:i:s", $startDate) . "'", "date DESC", 1);

        $state_before = '';
        if (count($a_list_before) == '0') {
            $state_before = 'OK';
        } else {
            $datat = current($a_list_before);
            if (strstr($datat['output'], ' OK -')) {
                $state_before = 'OK';
            } else {
                $state_before = 'CRITICAL';
            }
        }

        $count = [];
        $count['critical'] = 0;
        $count['ok'] = 0;
        $last_datetime = date("Y-m-d H:i:s", $startDate);

        foreach ($a_list as $data) {
            if (strstr($data['output'], ' OK -')) {
                if ($state_before == "OK") {
                    $count['ok'] += $this->convert_datetime_timestamp($data['date']) -
                        $this->convert_datetime_timestamp($last_datetime);
                } else {
                    $count['critical'] += $this->convert_datetime_timestamp($data['date']) -
                        $this->convert_datetime_timestamp($last_datetime);
                }
                $state_before = '';
            } else {
                if ($state_before == "CRITICAL") {
                    $count['critical'] += $this->convert_datetime_timestamp($data['date']) -
                        $this->convert_datetime_timestamp($last_datetime);
                } else {
                    $count['ok'] += $this->convert_datetime_timestamp($data['date']) -
                        $this->convert_datetime_timestamp($last_datetime);
                }
                $state_before = '';
            }
            $last_datetime = $data['date'];

        }
        if (!isset($data['output']) OR strstr($data['output'], ' OK -')) {
            $count['ok'] += date('U') - $this->convert_datetime_timestamp($last_datetime);
        } else {
            $count['critical'] += date('U') - $this->convert_datetime_timestamp($last_datetime);
        }
        $total = $count['ok'] + $count['critical'];
        return ['ok_t' => $count['ok'] . " seconds",
            'critical_t' => $count['critical'] . " seconds",
            'ok_p' => round(($count['ok'] * 100) / $total, 3),
            'critical_p' => round(($count['critical'] * 100) / $total, 3)];

    }
}


