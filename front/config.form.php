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

include("../../../inc/includes.php");

Session::checkRight("plugin_monitoring_command", READ);

Html::header(__('Monitoring - configuration', 'monitoring'),
    "", "config", "pluginmonitoringmenu", "");


$pmConfig = new PluginMonitoringConfig();
if (isset ($_POST["update"])) {
    $pmConfig->update($_POST);
    $pmConfig->load_alignak_url();
    Html::back();
} else if (isset($_POST['timezones_add'])) {
    $input = [];
    $pmConfig->getFromDB($_POST['id']);
    $input['id'] = $_POST['id'];
    $a_timezones = importArrayFromDB($pmConfig->fields['timezones']);
    foreach ($_POST['timezones_to_add'] as $timezone) {
        $a_timezones[] = $timezone;
    }
    $input['timezones'] = exportArrayToDB($a_timezones);
    $pmConfig->update($input);
    Html::back();
} else if (isset($_POST['timezones_delete'])) {
    $input = [];
    $pmConfig->getFromDB($_POST['id']);
    $input['id'] = $_POST['id'];
    $a_timezones = importArrayFromDB($pmConfig->fields['timezones']);
    foreach ($_POST['timezones_to_delete'] as $timezone) {
        $key = array_search($timezone, $a_timezones);
        unset($a_timezones[$key]);
    }
    $input['timezones'] = exportArrayToDB($a_timezones);
    $pmConfig->update($input);
    Html::back();
}


$pmConfig->showForm(0, ['canedit' => Session::haveRight("config", UPDATE)]);

Html::footer();
