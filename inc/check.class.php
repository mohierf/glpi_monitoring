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

class PluginMonitoringCheck extends CommonDBTM
{
    static $rightname = 'plugin_monitoring_command';

    /**
     * Initialization called on plugin installation
     * @param Migration $migration
     */
    function initialize($migration)
    {
        $input = [];
        $input['name'] = '5 minutes / 5 retries';
        $input['max_check_attempts'] = '5';
        $input['check_interval'] = '5';
        $input['retry_interval'] = '1';
        $this->add($input);

        $input = [];
        $input['name'] = '5 minutes / 3 retries';
        $input['max_check_attempts'] = '3';
        $input['check_interval'] = '5';
        $input['retry_interval'] = '1';
        $this->add($input);

        $input = [];
        $input['name'] = '15 minutes / 3 retries';
        $input['max_check_attempts'] = '3';
        $input['check_interval'] = '15';
        $input['retry_interval'] = '1';
        $this->add($input);

        $input = [];
        $input['name'] = '15 minutes / 1 retry';
        $input['max_check_attempts'] = '1';
        $input['check_interval'] = '15';
        $input['retry_interval'] = '1';
        $this->add($input);

        $input = [];
        $input['name'] = '60 minutes / 3 retries';
        $input['max_check_attempts'] = '3';
        $input['check_interval'] = '60';
        $input['retry_interval'] = '1';
        $this->add($input);

        $input = [];
        $input['name'] = '60 minutes / 1 retry';
        $input['max_check_attempts'] = '1';
        $input['check_interval'] = '60';
        $input['retry_interval'] = '1';
        $this->add($input);

        $migration->displayMessage("  created default check strategies");
    }


    static function getTypeName($nb = 0)
    {
        return __('Check strategies', 'monitoring');
    }


    /*
     * Search options, see: https://glpi-developer-documentation.readthedocs.io/en/master/devapi/search.html#search-options
     */
    public function getSearchOptionsNew()
    {
        return $this->rawSearchOptions();
    }

    function rawSearchOptions()
    {

        $tab = [];

        $tab[] = [
            'id' => 'common',
            'name' => __('Check strategies', 'monitoring')
        ];

        $index = 1;
        $tab[] = [
            'id' => $index++,
            'table' => $this->getTable(),
            'field' => 'name',
            'name' => __('Name'),
            'datatype' => 'itemlink'
        ];

        $tab[] = [
            'id' => $index++,
            'table' => $this->getTable(),
            'field' => 'max_check_attempts',
            'datatype' => 'number',
            'name' => __('Maximum check attempts', 'monitoring'),
        ];

        $tab[] = [
            'id' => $index++,
            'table' => $this->getTable(),
            'field' => 'check_interval',
            'datatype' => 'number',
            'name' => __('Check interval', 'monitoring'),
        ];

        $tab[] = [
            'id' => $index,
            'table' => $this->getTable(),
            'field' => 'retry_interval',
            'datatype' => 'number',
            'name' => __('Retry interval', 'monitoring'),
        ];

        /*
         * Include other fields here
         */

        $tab[] = [
            'id' => '99',
            'table' => $this->getTable(),
            'field' => 'id',
            'name' => __('ID'),
            'usehaving' => true,
            'searchtype' => 'equals',
        ];

        return $tab;
    }


    function defineTabs($options = [])
    {
        $ong = [];
        $this->addDefaultFormTab($ong);
        return $ong;
    }


    function getComments()
    {
        $comment =
            __('Max check attempts (number of retries)', 'monitoring') . ' : ' . $this->fields['max_check_attempts'] . '<br/>
         ' . __('Time in minutes between 2 checks', 'monitoring') . ' : ' . $this->fields['check_interval'] . ' minutes<br/>
         ' . __('Time in minutes between 2 retries', 'monitoring') . ' : ' . $this->fields['retry_interval'] . ' minutes';

        if (!empty($comment)) {
            return Html::showToolTip($comment, array('display' => false));
        }

        return $comment;
    }


    function showForm($items_id, $options = [])
    {

        $this->initForm($items_id, $options);
        $this->showFormHeader($options);

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Name') . " :</td>";
        echo "<td align='center'>";
        echo "<input type='text' name='name' value='" . $this->fields["name"] . "' size='30'/>";
        echo "</td>";
        echo "<td>" . __('Max check attempts (number of retries)', 'monitoring') . "&nbsp;:</td>";
        echo "<td align='center'>";
        Dropdown::showNumber("max_check_attempts", array(
                'value' => $this->fields['max_check_attempts'],
                'min' => 1)
        );
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Time in minutes between 2 checks', 'monitoring') . "&nbsp;:</td>";
        echo "<td align='center'>";
        Dropdown::showNumber("check_interval", array(
                'value' => $this->fields['check_interval'],
                'min' => 1)
        );
        echo "</td>";
        echo "<td>" . __('Time in minutes between 2 retries', 'monitoring') . "&nbsp;:</td>";
        echo "<td align='center'>";
        Dropdown::showNumber("retry_interval", array(
                'value' => $this->fields['retry_interval'],
                'min' => 1)
        );
        echo "</td>";
        echo "</tr>";

        $this->showFormButtons($options);

        return true;
    }
}
