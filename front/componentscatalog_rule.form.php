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

Session::checkRight("plugin_monitoring_componentscatalog", READ);

Html::header(__('Monitoring - components catalogs', 'monitoring'),
    "", "config", "pluginmonitoringmenu", "componentscatalog");

if (isset($_POST['itemtypen'])) {
   $_POST['itemtype'] = $_POST['itemtypen'];
}

$pmComponentscatalog_rule = new PluginMonitoringComponentscatalog_rule();
$pmComponentscatalog = new PluginMonitoringComponentscatalog();


if (isset($_GET['addrule'])) {
   if (!isset($_GET['criteria'])) {
//      $_SESSION['plugin_monitoring_rules'] = $_POST;
   } else {
      $_POST = $_GET;
      $input = array();
      $pmComponentscatalog->getFromDB($_POST['plugin_monitoring_componentscatalogs_id']);
      $input['entities_id'] = $pmComponentscatalog->fields['entities_id'];
      $input['is_recursive'] = $pmComponentscatalog->fields['is_recursive'];
      $input['name'] = $_POST['name'];
      $input['itemtype'] = $_POST['itemtype'];
      $input['plugin_monitoring_componentscatalogs_id'] = $_POST['plugin_monitoring_componentscatalogs_id'];
      unset($_POST['entities_id']);
      unset($_POST['is_recursive']);
      unset($_POST['name']);
      unset($_POST['addrule']);
      unset($_POST['itemtypen']);
      unset($_POST['plugin_monitoring_componentscatalogs_id']);
      unset($_POST['_glpi_csrf_token']);
      $input['condition'] = exportArrayToDB($_POST);
      $rules_id = $pmComponentscatalog_rule->add($input);
      unset($_SESSION['plugin_monitoring_rules']);
      unset($_SESSION["glpisearch"][$input['itemtype']]);
      Html::redirect($CFG_GLPI['root_doc']."/plugins/monitoring/front/componentscatalog.form.php?id=".$input['plugin_monitoring_componentscatalogs_id']);
   }
} else if (isset($_GET['updaterule'])) {
   if (!isset($_GET['criteria'])
        AND !isset($_GET['reset'])) {
//      $_SESSION['plugin_monitoring_rules'] = $_POST;
   } else {
      $_POST = $_GET;
      $input = array();
      $pmComponentscatalog->getFromDB($_POST['plugin_monitoring_componentscatalogs_id']);
      $input['id'] = $_POST['id'];
      $input['entities_id'] = $pmComponentscatalog->fields['entities_id'];
      $input['is_recursive'] = $pmComponentscatalog->fields['is_recursive'];
      $input['name'] = $_POST['name'];
      $input['itemtype'] = $_POST['itemtype'];
      $input['plugin_monitoring_componentscatalogs_id'] = $_POST['plugin_monitoring_componentscatalogs_id'];
      unset($_POST['entities_id']);
      unset($_POST['is_recursive']);
      unset($_POST['name']);
      unset($_POST['updaterule']);
      unset($_POST['itemtypen']);
      unset($_POST['plugin_monitoring_componentscatalogs_id']);
      unset($_POST['id']);
      unset($_POST['_glpi_csrf_token']);
      $input['condition'] = exportArrayToDB($_POST);
      $pmComponentscatalog_rule->update($input);
      unset($_SESSION['plugin_monitoring_rules']);
      unset($_SESSION["glpisearch"][$input['itemtype']]);
      Html::redirect($CFG_GLPI['root_doc']."/plugins/monitoring/front/componentscatalog.form.php?id=".$input['plugin_monitoring_componentscatalogs_id']);
   }
} else if (isset($_GET['deleterule'])) {
   $_POST = $_GET;
   $pmComponentscatalog_rule->delete($_POST);
   Html::redirect($CFG_GLPI['root_doc']."/plugins/monitoring/front/componentscatalog.form.php?id=".$_POST['plugin_monitoring_componentscatalogs_id']);
} else if (isset($_GET['criteria'])
        OR isset($_GET['reset'])) {
//   if (isset($_SESSION['plugin_monitoring_rules'])) {
//      unset($_SESSION['plugin_monitoring_rules']);
//   }
//   $_SESSION['plugin_monitoring_rules'] = $_POST;
//   $_SESSION['plugin_monitoring_rules_REQUEST_URI'] = $_SERVER['REQUEST_URI'];
   //Html::back();
} else if (isset($_GET['id'])
        AND !isset($_GET['itemtype'])) {
   $pmComponentscatalog_rule->getFromDB($_GET['id']);

   $val = importArrayFromDB($pmComponentscatalog_rule->fields['condition']);

   $params = Search::manageParams($pmComponentscatalog_rule->fields['itemtype'], $val);

   $url = str_replace("?id=".$_GET['id'], "", $_SERVER['REQUEST_URI']);
   $url .= "?".Toolbox::append_params($params);
   $url .= "&plugin_monitoring_componentscatalogs_id=".$pmComponentscatalog_rule->fields['plugin_monitoring_componentscatalogs_id'];
   $url .= "&name=".$pmComponentscatalog_rule->fields['name'];
   $url .= "&id=".$_GET['id'];

   Html::redirect($url);
}

if (isset($_POST['name'])) {
   $a_construct = array();
   foreach ($_POST as $key=>$value) {
      $a_construct[] = $key."=".$value;
   }
   $_SERVER['REQUEST_URI'] = $_SERVER['REQUEST_URI']."?".implode("&", $a_construct);
   Html::redirect($_SERVER['REQUEST_URI']);
}

$pmComponentscatalog_rule->addRule();

Html::footer();
