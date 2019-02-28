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

class PluginMonitoringDowntime extends CommonDBTM
{

    static $rightname = 'plugin_monitoring_downtime';


    static function getTypeName($nb = 0)
    {
        return __('Downtime', 'monitoring');
    }


    function defineTabs($options = array())
    {
        $ong = array();
        $this->addDefaultFormTab($ong);
        return $ong;
    }


    function getComments()
    {
    }


    /**
     * Display tab
     *
     * @param CommonGLPI $item
     * @param integer $withtemplate
     *
     * @return string name of the tab(s) to display
     */
    function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        if ($item->getType() == 'Ticket' || $item->getType() == 'Computer') {
            if (self::canView()) {
                return __('Downtimes', 'monitoring');
            }
        }

        return '';
    }


    /**
     * Display content of tab
     *
     * @param CommonGLPI $item
     * @param integer $tabnum
     * @param integer $withtemplate
     *
     * @return boolean true
     */
    static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {

        // PluginMonitoringToolbox::log("Downtime, displayTabContentForItem ($withtemplate), item concerned : ".$item->getTypeName()."/".$item->getID()."\n");
        if ($item->getType() == 'Ticket') {
            /* @var Ticket $item */
            if (self::canView()) {
                // Show list filtered on item, sorted on day descending ...
                Search::showList('PluginMonitoringDowntime', array(
                    'criteria' => array(
                        array(
                            'field' => 12,
                            'searchtype' => 'equals',
                            'value' => $item->getID()
                        )
                    ),
                    'order' => 'DESC',
                    'sort' => 4,
                    'itemtype' => 'PluginMonitoringDowntime'
                ));
                return true;
            }
        }

        if ($item->getType() == 'Computer') {
            /* @var Computer $item */
            if (self::canView()) {
                // Show list filtered on item, sorted on day descending ...
                Search::showList('PluginMonitoringDowntime', array(
                    'criteria' => array(
                        array(
                            'field' => 2,
                            'searchtype' => 'equals',
                            'value' => $item->getID()
                        )
                    ),
                    'order' => 'DESC',
                    'sort' => 4,
                    'itemtype' => 'PluginMonitoringDowntime'
                ));
                return true;
            }
        }

        return true;
    }


    function getSearchOptions()
    {

        $tab = array();

        $tab['common'] = __('Host downtimes', 'monitoring');

        $tab[1]['table'] = $this->getTable();
        $tab[1]['field'] = 'id';
        $tab[1]['linkfield'] = 'id';
        $tab[1]['name'] = __('ID');
        $tab[1]['datatype'] = 'itemlink';
        $tab[1]['massiveaction'] = false; // implicit field is id

        // $tab[2]['table']           = $this->getTable();
        // $tab[2]['field']           = 'plugin_monitoring_hosts_id';
        // $tab[2]['name']            = __('Host name', 'monitoring');
        // $tab[2]['datatype']        = 'specific';
        // $tab[2]['massiveaction']   = false;

        $tab[2]['table'] = "glpi_computers";
        $tab[2]['field'] = 'name';
        $tab[2]['name'] = __('Computer');
        $tab[2]['datatype'] = 'itemlink';

        $tab[3]['table'] = $this->getTable();
        $tab[3]['field'] = 'flexible';
        $tab[3]['name'] = __('Flexible downtime', 'monitoring');
        $tab[3]['datatype'] = 'bool';
        $tab[3]['massiveaction'] = false;

        $tab[4]['table'] = $this->getTable();
        $tab[4]['field'] = 'start_time';
        $tab[4]['name'] = __('Start time', 'monitoring');
        $tab[4]['datatype'] = 'datetime';
        $tab[4]['massiveaction'] = false;

        $tab[5]['table'] = $this->getTable();
        $tab[5]['field'] = 'end_time';
        $tab[5]['name'] = __('End time', 'monitoring');
        $tab[5]['datatype'] = 'datetime';
        $tab[5]['massiveaction'] = false;

        $tab[6]['table'] = $this->getTable();
        $tab[6]['field'] = 'duration';
        $tab[6]['name'] = __('Duration', 'monitoring');
        $tab[6]['massiveaction'] = false;

        $tab[7]['table'] = $this->getTable();
        $tab[7]['field'] = 'duration_type';
        $tab[7]['name'] = __('Duration type', 'monitoring');
        $tab[7]['massiveaction'] = false;

        $tab[8]['table'] = $this->getTable();
        $tab[8]['field'] = 'comment';
        $tab[8]['name'] = __('Comment', 'monitoring');
        $tab[8]['datatype'] = 'itemlink';
        // $tab[8]['datatype']        = 'text';
        $tab[8]['massiveaction'] = false;

        $tab[9]['table'] = $this->getTable();
        $tab[9]['field'] = 'users_id';
        $tab[9]['name'] = __('User', 'monitoring');
        $tab[9]['massiveaction'] = false;

        $tab[10]['table'] = $this->getTable();
        $tab[10]['field'] = 'notified';
        $tab[10]['name'] = __('Notified to monitoring system', 'monitoring');
        $tab[10]['datatype'] = 'bool';
        $tab[10]['massiveaction'] = false;

        $tab[11]['table'] = $this->getTable();
        $tab[11]['field'] = 'expired';
        $tab[11]['name'] = __('Period expired', 'monitoring');
        $tab[11]['datatype'] = 'bool';
        $tab[11]['massiveaction'] = false;

        $tab[12]['table'] = "glpi_tickets";
        $tab[12]['field'] = 'id';
        $tab[12]['name'] = __('Ticket');
        $tab[12]['datatype'] = 'itemlink';

        return $tab;
    }


    static function getSpecificValueToDisplay($field, $values, array $options = array())
    {

        if (!is_array($values)) {
            $values = array($field => $values);
        }
        switch ($field) {
            case 'plugin_monitoring_hosts_id':
                $pmHost = new PluginMonitoringHost();
                $pmHost->getFromDB($values[$field]);
                return $pmHost->getLink(array("monitoring" => "1"));
                break;

            case 'duration_type':
                $a_duration_type = array();
                $a_duration_type['seconds'] = __('Second(s)', 'monitoring');
                $a_duration_type['minutes'] = __('Minute(s)', 'monitoring');
                $a_duration_type['hours'] = __('Hour(s)', 'monitoring');
                $a_duration_type['days'] = __('Day(s)', 'monitoring');
                return $a_duration_type[$values[$field]];
                break;

            case 'users_id':
                $user = new User();
                $user->getFromDB($values[$field]);
                return $user->getName(1);
                break;
        }
        return parent::getSpecificValueToDisplay($field, $values, $options);
    }


    /**
     * Get entity
     */
    function getEntityID($options = array())
    {
        return $this->fields["entities_id"];
    }


    /**
     * Get current downtime for an host
     */
    function getFromHost($host_id)
    {
        $this->getFromDBByQuery("WHERE `" . $this->getTable() . "`.`plugin_monitoring_hosts_id` = '" . $this->getID() . "' AND `expired` = '0' ORDER BY end_time DESC LIMIT 1");
        return $this->getID();
    }


    /**
     * Get user name for a downtime
     */
    function getUsername()
    {
        $user = new User();
        $user->getFromDB($this->fields['users_id']);
        return $user->getName(1);
    }


    /**
     * In scheduled downtime ?
     */
    function isInDowntime()
    {
        if ($this->getID() == -1) return false;

        if ($this->isExpired()) return false;

        // Now ...
        $now = strtotime(date('Y-m-d H:i:s'));
        // Start time ...
        $start_time = strtotime($this->fields["start_time"]);
        // End time ...
        $end_time = strtotime($this->fields["end_time"]);

        // PluginMonitoringToolbox::log("isInDowntime, now : $now, start : $start_time, end : $end_time\n");
        if (($start_time <= $now) && ($now <= $end_time)) {
            // PluginMonitoringToolbox::log("isInDowntime, yes, id : ".$this->getID()."\n");
            return true;
        }

        return false;
    }


    /**
     * Downtime expired ?
     */
    function isExpired()
    {
        if ($this->getID() == -1) return false;

        // Now ...
        $now = strtotime(date('Y-m-d H:i:s'));
        // Start time ...
//        $start_time = strtotime($this->fields["start_time"]);
        // End time ...
        $end_time = strtotime($this->fields["end_time"]);

        $this->fields["expired"] = ($now > $end_time);
        $this->update($this->fields);
        return ($this->fields["expired"] == 1);
    }


    /**
     * Downtime has an associated ticket ?
     */
    function isAssociatedTicket()
    {
        if ($this->getID() == -1) return false;

        return ($this->fields["tickets_id"] != 0);
    }
}
