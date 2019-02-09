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

class PluginMonitoringNotificationcommand extends CommonDBTM
{


    static $rightname = 'plugin_monitoring_command';


    /**
     * Shinken/Alignak define:
     *  #-- Nagios legacy macros
     *  $USER1$=$NAGIOSPLUGINSDIR$
     *  $NAGIOSPLUGINSDIR$
     *
     *  #-- Location of the plugins for Shinken/Alignak
     *  $PLUGINSDIR$
     */

    function initialize()
    {
        global $DB;

        // Shinken 2.x default commands
        // Host notifications
        $input = [];
        $input['name'] = 'Host : mail notification';
        $input['command_name'] = 'notify-host-by-email';
        $input['command_line'] = $DB->escape('/usr/bin/printf "%b" "Shinken Notification\n\nType:$NOTIFICATIONTYPE$\nHost: $HOSTNAME$\nState: $HOSTSTATE$\nAddress: $HOSTADDRESS$\nInfo: $HOSTOUTPUT$\nDate/Time: $DATE$ $TIME$\n" | /usr/bin/mail -s "Host $HOSTSTATE$ alert for $HOSTNAME$" $CONTACTEMAIL$');
        $this->add($input);

        $input = [];
        $input['name'] = 'Host : log notification';
        $input['command_name'] = 'notify-host-by-log';
        $input['command_line'] = $DB->escape('/usr/bin/printf "%b" "\n-----\n$DATE$ $TIME$ - Alignak notification #$NOTIFICATIONNUMBER$:\n Type:$NOTIFICATIONTYPE$\n Host: $HOSTNAME$ ($HOSTADDRESS$)\n State: $HOSTSTATE$\n Info: $HOSTOUTPUT$\n" >> /tmp/alignak-notifications.log');
        $this->add($input);

        $input = [];
        $input['name'] = 'Host : mail notification (python)';
        $input['command_name'] = 'notify-host-by-email-py';
        $input['command_line'] = $DB->escape('$PLUGINSDIR$/send_mail_host.py -n "$NOTIFICATIONTYPE$" -H "$HOSTALIAS$" -a "$HOSTADDRESS$" -i "$SHORTDATETIME$" -o "$HOSTOUTPUT$" -t "$CONTACTEMAIL$" -r "$HOSTSTATE$" -S shinken@localhost');
        $this->add($input);

        $input = [];
        $input['name'] = 'Host : mail detailed notification';
        $input['command_name'] = 'detailled-host-by-email';
        $input['command_line'] = $DB->escape('/usr/bin/printf "%b" "Shinken Notification\n\nType:$NOTIFICATIONTYPE$\nHost: $HOSTNAME$\nState: $HOSTSTATE$\nAddress: $HOSTADDRESS$\nDate/Time: $DATE$/$TIME$\n Host Output : $HOSTOUTPUT$\n\nHost description: $_HOSTDESC$\nHost Impact: $_HOSTIMPACT$" | /usr/bin/mail -s "Host $HOSTSTATE$ alert for $HOSTNAME$" $CONTACTEMAIL$');
        $this->add($input);

        $input = [];
        $input['name'] = 'Host : XMPP notification';
        $input['command_name'] = 'notify-host-by-xmpp';
        $input['command_line'] = $DB->escape('$PLUGINSDIR$/notify_by_xmpp.py -a $PLUGINSDIR$/notify_by_xmpp.ini "Host $HOSTNAME$ is $HOSTSTATE$ - Info : $HOSTOUTPUT$" $CONTACTEMAIL$');
        $this->add($input);

        // Service notifications
        $input = [];
        $input['name'] = 'Service : mail notification';
        $input['command_name'] = 'notify-service-by-email';
        $input['command_line'] = $DB->escape('/usr/bin/printf "%b" "Shinken Notification\n\nNotification Type: $NOTIFICATIONTYPE$\n\nService: $SERVICEDESC$\nHost: $HOSTNAME$\nAddress: $HOSTADDRESS$\nState: $SERVICESTATE$\n\nDate/Time: $DATE$ $TIME$\nAdditional Info : $SERVICEOUTPUT$\n" | /usr/bin/mail -s "** $NOTIFICATIONTYPE$ alert - $HOSTNAME$/$SERVICEDESC$ is $SERVICESTATE$ **" $CONTACTEMAIL$');
        $this->add($input);

        $input = [];
        $input['name'] = 'Service : log notification';
        $input['command_name'] = 'notify-service-by-log';
        $input['command_line'] = $DB->escape('/usr/bin/printf "%b" "\n-----\n$DATE$ $TIME$ - Alignak notification #$NOTIFICATIONNUMBER$:\n Type:$NOTIFICATIONTYPE$\n Host: $HOSTNAME$ ($HOSTADDRESS$)\n Service: $SERVICEDESC$\n State: $SERVICESTATE$\n Info: $SERVICEOUTPUT$\n" >> /tmp/alignak-notifications.log');
        $this->add($input);

        $input = [];
        $input['name'] = 'Service : mail notification (python)';
        $input['command_name'] = 'notify-service-by-email-py';
        $input['command_line'] = $DB->escape('$PLUGINSDIR$/send_mail_service.py -s "$SERVICEDESC$" -n "$NOTIFICATIONTYPE$" -H "$HOSTALIAS$" -a "$HOSTADDRESS$" -i "$SHORTDATETIME$" -o "$SERVICEOUTPUT$" -t "$CONTACTEMAIL$" -r "$SERVICESTATE$" -S shinken@localhost');
        $this->add($input);

        $input = [];
        $input['name'] = 'Service : mail detailed notification';
        $input['command_name'] = 'detailled-service-by-email';
        $input['command_line'] = $DB->escape('/usr/bin/printf "%b" "Shinken Notification\n\nNotification Type: $NOTIFICATIONTYPE$\n\nService: $SERVICEDESC$\nHost: $HOSTALIAS$\nAddress: $HOSTADDRESS$\nState: $SERVICESTATE$\n\nDate/Time: $DATE$ at $TIME$\nService Output : $SERVICEOUTPUT$\n\nService Description: $_SERVICEDETAILLEDESC$\nService Impact: $_SERVICEIMPACT$\nFix actions: $_SERVICEFIXACTIONS$" | /usr/bin/mail -s "$SERVICESTATE$ on Host : $HOSTALIAS$/Service : $SERVICEDESC$" $CONTACTEMAIL$');
        $this->add($input);

        $input = [];
        $input['name'] = 'Service : XMPP notification';
        $input['command_name'] = 'notify-service-by-xmpp';
        $input['command_line'] = $DB->escape('$PLUGINSDIR$/notify_by_xmpp.py -a $PLUGINSDIR$/notify_by_xmpp.ini "$NOTIFICATIONTYPE$ $HOSTNAME$ $SERVICEDESC$ $SERVICESTATE$ $SERVICEOUTPUT$ $LONGDATETIME$" $CONTACTEMAIL$');
        $this->add($input);
    }


    static function getTypeName($nb = 0)
    {
        return __('Notification commands', 'monitoring');
    }


    public function getSearchOptionsNew()
    {
        return $this->rawSearchOptions();
    }

    function rawSearchOptions()
    {

        $tab = [];

        $tab[] = [
            'id' => 'common',
            'name' => __('Notification commands', 'monitoring')
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
            'field' => 'is_active',
            'name' => __('Is active'),
            'datatype' => 'bool'
        ];

        $tab[] = [
            'id' => $index++,
            'table' => $this->getTable(),
            'field' => 'command_name',
            'name' => __('Command name'),
        ];

        $tab[] = [
            'id' => $index++,
            'table' => $this->getTable(),
            'field' => 'command_line',
            'name' => __('Command line'),
        ];

        $tab[] = [
            'id' => $index++,
            'table' => $this->getTable(),
            'field' => 'reactionner_tag',
            'name' => __('Reactionner tag'),
        ];

        $tab[] = [
            'id' => $index,
            'table' => $this->getTable(),
            'field' => 'module_type',
            'name' => __('Module type'),
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


    function showForm($items_id, $options = [], $copy = [])
    {
        if (count($copy) > 0) {
            foreach ($copy as $key => $value) {
                $this->fields[$key] = stripslashes($value);
            }
        }

        $this->initForm($items_id, $options);
        $this->showFormHeader($options);

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Name') . " :</td>";
        echo "<td>";
        echo "<input type='text' name='name' value='" . $this->fields["name"] . "' size='30'/>";
        echo "</td>";
        echo "<td>" . __('Command name', 'monitoring') . "&nbsp;:</td>";
        echo "<td>";
        echo "<input type='text' name='command_name' value='" . $this->fields["command_name"] . "' size='30'/>";
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Active ?', 'monitoring') . "</td>";
        echo "<td>";
        if (self::canCreate()) {
            Dropdown::showYesNo('is_active', $this->fields['is_active']);
        } else {
            echo Dropdown::getYesNo($this->fields['is_active']);
        }
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Module type', 'monitoring') . " :</td>";
        echo "<td>";
        echo "<input type='text' name='module_type' value='" . $this->fields["module_type"] . "' size='30'/>";
        echo "</td>";
        echo "<td>" . __('Reactionner tag', 'monitoring') . "&nbsp;:</td>";
        echo "<td>";
        echo "<input type='text' name='reactionner_tag' value='" . $this->fields["reactionner_tag"] . "' size='30'/>";
        echo "</td>";
        echo "</tr>";


        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Command line', 'monitoring') . "&nbsp;:</td>";
        echo "<td colspan='3'>";
        echo "<input type='text' name='command_line' value='" . $this->fields["command_line"] . "' size='130'/>";
        echo "</td>";
        echo "</tr>";

        $this->showFormButtons($options);

        return true;
    }
}
