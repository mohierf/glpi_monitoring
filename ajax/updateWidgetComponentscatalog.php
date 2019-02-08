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

// ----------------------------------------------------------------------
// Original Author of file: David DURIEUX
// Purpose of file:
// ----------------------------------------------------------------------

// Direct access to file
if (strpos($_SERVER['PHP_SELF'],"updateWidgetComponentscatalog.php")) {
   include ("../../../inc/includes.php");
   header("Content-Type: text/html; charset=UTF-8");
   Html::header_nocache();
}
session_write_close();

if (!defined('GLPI_ROOT')) {
   die("Can not acces directly to this file");
}

Session::checkLoginUser();

if (! isset($_SESSION['plugin_monitoring_reduced_interface'])) {
   $_SESSION['plugin_monitoring_reduced_interface'] = false;
}

//      echo "
//      <script>
//         function toggleEntity(idEntity) {
//            Ext.select('#'+idEntity).each(function(el) {
//               var displayed = false;
//               el.select('tr.services').each(function(elTr) {
//                  elTr.setDisplayed(! elTr.isDisplayed());
//                  displayed = elTr.isDisplayed();
//               });
//               // if (! displayed) {
//                  // el.select('tr.header').each(function(elTr) {
//                     // elTr.applyStyles({'height':'10px'});
//                     // elTr.select('th').each(function(elTd) {
//                        // elTd.applyStyles({'height':'10px'});
//                     // });
//                  // });
//               // }
//               el.select('tr.header').each(function(elTr) {
//                  elTr.applyStyles(displayed ? {'height':'50px'} : {'height':'10px'});
//                  elTr.select('th').each(function(elTd) {
//                     elTd.applyStyles(displayed ? {'height':'50px'} : {'height':'10px'});
//                  });
//               });
//            });
//         };
//      </script>
//      ";


$pmComponentscatalog = new PluginMonitoringComponentscatalog();
$pmComponentscatalog->showWidgetFrame(
        $_GET['id'],
        $_SESSION['plugin_monitoring_reduced_interface'],
        $_GET['is_minemap']);

?>