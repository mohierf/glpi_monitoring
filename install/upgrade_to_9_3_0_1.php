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

class PluginMonitoringUpgradeTo9_3_0_1
{
    /**
     * @param Migration $migration
     */
    public function upgrade(Migration $migration)
    {
        // Drop the hosts and services tables to restart from scratch
        // The tables will be re-created by the SQL script
        $migration->dropTable('glpi_plugin_monitoring_hosts');
        $migration->dropTable('glpi_plugin_monitoring_services');
        $migration->dropTable('glpi_plugin_monitoring_serviceevents');

        /*
        $table = 'glpi_plugin_monitoring_services';
        $migration->changeField($table, 'name', 'service_description', 'text');
        $migration->addField($table, 'host_name', 'string', [
            'value' => '',
            'after' => 'service_description'
        ]);
        $migration->addKey($table, ['host_name', 'service_description'], 'host_name_service_description');
        $migration->migrationOneTable($table);
        */
    }
}
