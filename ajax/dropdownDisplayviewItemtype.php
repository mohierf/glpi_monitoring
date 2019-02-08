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

include ("../../../inc/includes.php");
header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

Session::checkLoginUser();

switch ($_POST['itemtype']) {

   case 'PluginMonitoringServicescatalog':
      Dropdown::show('PluginMonitoringServicescatalog', array('name'=>'items_id'));
      break;

   case 'PluginMonitoringComponentscatalog':
      Dropdown::show('PluginMonitoringComponentscatalog', array('name'=>'items_id'));
      echo "<br/>".__('Display minemap', 'monitoring')." : ";
      Dropdown::showYesNo('is_minemap');
      break;

   case 'PluginMonitoringService':
      $rand = mt_rand();
      $elements = array(
         '0'                => Dropdown::EMPTY_VALUE,
         'Computer'         => Computer::getTypeName(),
         'NetworkEquipment' => NetworkEquipment::getTypeName()
      );
      Dropdown::showFromArray(
              'itemtype',
              $elements,
              array(
                 'rand'                => $rand,
                 'emptylabel' => true,
                 'display_emptychoice' => true));

      $params = array('itemtype'        => '__VALUE__',
                      'entity_restrict' => $_POST['a_entities'],
                      'selectgraph'     => '1',
                      'rand'            => $rand);

      Ajax::updateItemOnSelectEvent("dropdown_itemtype$rand", "show_itemtype$rand",
                                  $CFG_GLPI["root_doc"]."/plugins/monitoring/ajax/dropdownServiceHostType.php",
                                  $params);

      echo "<span id='show_itemtype$rand'><input type='hidden' name='services_id[]' value='0'/></span>\n";
      break;

   case 'PluginMonitoringWeathermap':
      $toadd = array('-1' => "[".__('Legend', 'monitoring')."]");
      Dropdown::show(
              'PluginMonitoringWeathermap',
              array(
                  'name'  => 'items_id',
                  'toadd' => $toadd));
      echo "<br/>".__('% of the width of the frame', 'monitoring')."&nbsp: ";
      Dropdown::showNumber("extra_infos", array(
                      'value' => 100,
                      'min'   => 0,
                      'max'   => 100,
                      'step'  => 5)
      );
      break;

   case 'PluginMonitoringDisplayview':
      if (isset($_POST['sliders_id'])) {
         Dropdown::show('PluginMonitoringDisplayview',
                        array('name'      =>'items_id'));
      } else {
         Dropdown::show('PluginMonitoringDisplayview',
                        array('name'      =>'items_id',
                              'condition' => "`is_frontview`='0'",
                              'used'      => array($_POST['displayviews_id'])));
      }
      break;

   case 'service':
   case 'host':

      $elements = array(
          'Computer'          => __('Computer'),
          'NetworkEquipment'  => __('NetworkEquipment'),
          'Peripheral'        => __('Peripheral'),
          'Phone'             => __('Phone'),
          'Printer'           => __('Printer')
      );
      $pmDisplayview_rule = new PluginMonitoringDisplayview_rule();
      $a_items = $pmDisplayview_rule->find("`plugin_monitoring_displayviews_id`='".$_POST['displayviews_id']."'"
              . " AND `type`='host'");
      foreach ($a_items as $data) {
         if (isset($elements[$data['itemtype']])) {
            unset($elements[$data['itemtype']]);
         }
      }
      Dropdown::showFromArray('type', $elements);
      echo "<br/>".__('Display minemap', 'monitoring')." : ";
      Dropdown::showYesNo('is_minemap');
      break;

   case 'PluginMonitoringCustomitem_Gauge':
      Dropdown::show('PluginMonitoringCustomitem_Gauge', array('name'=>'items_id'));
      break;

   case 'PluginMonitoringCustomitem_Counter':
      Dropdown::show('PluginMonitoringCustomitem_Counter', array('name'=>'items_id'));
      break;

   default:
      break;

}

if (strstr($_SERVER['HTTP_REFERER'], 'slider.form')) {
   if ($_POST['itemtype'] == 'PluginMapsMap') {

   }
}
?>
