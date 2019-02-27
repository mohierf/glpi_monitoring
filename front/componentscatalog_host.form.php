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

Session::checkRight("plugin_monitoring_componentscatalog", UPDATE);

$pmCC_Host = new PluginMonitoringComponentscatalog_Host();

PluginMonitoringToolbox::logIfDebug("CC_host_form, POST: " . print_r($_POST, true));

if (isset ($_POST["add"])) {
    if (isset($_POST['items_id']) and isset($_POST['itemtype'])
        and $_POST['items_id'] != '0') {
        if (!$pmCC_Host->getFromDBByCrit([
            'plugin_monitoring_componentscatalogs_id' => $_POST["plugin_monitoring_componentscatalogs_id"],
            'itemtype' => $_POST['itemtype'], 'items_id' => $_POST['items_id']])) {
            $pmCC_Host_id = $pmCC_Host->add($_POST);
        } else {
            Session::addMessageAfterRedirect(__('This host is still linked to the components catalog.', 'monitoring'), false, ERROR);
        }
    }
    Html::back();
} else if (isset($_POST["purge"])) {
    foreach ($_POST["item"] as $id => $num) {
        $pmCC_Host->delete(['id' => $id]);
    }
    Html::back();
}

Html::footer();
