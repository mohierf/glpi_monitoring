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

include ("../../../inc/includes.php");

header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

$pmWeathermaplink = new PluginMonitoringWeathermaplink();
$pmWeathermaplink->getFromDB($_POST['items_id']);

echo "<br/>";
echo __('Max bandwidth input', 'monitoring')."&nbsp;:&nbsp;";
echo "<input type='text' name='up_bandwidth_in' value='".$pmWeathermaplink->fields['bandwidth_in']."'/>";
echo "<br/>";
echo __('Max bandwidth output', 'monitoring')."&nbsp;:&nbsp;";
echo "<input type='text' name='up_bandwidth_out' value='".$pmWeathermaplink->fields['bandwidth_out']."'/>";
echo "<br/>";
echo "<input type='submit' name='update' value=\"".__('Save')."\" class='submit'>";

?>