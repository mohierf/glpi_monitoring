CREATE TABLE IF NOT EXISTS `glpi_plugin_monitoring_componentscatalogs` (
   `id` int(11) NOT NULL AUTO_INCREMENT,
   `name` varchar(255) DEFAULT NULL,
   `entities_id` int(11) NOT NULL DEFAULT '0',
   `is_recursive` tinyint(1) NOT NULL DEFAULT '0',
   `comment` text DEFAULT NULL COLLATE utf8_unicode_ci,
   `additional_templates` varchar(255) DEFAULT NULL,
   `notification_interval` int(4) NOT NULL DEFAULT '30',
   `hostnotificationtemplates_id` int(11) DEFAULT '-1',
   `servicenotificationtemplates_id` int(11) DEFAULT '-1',
   PRIMARY KEY (`id`),
   KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `glpi_plugin_monitoring_components` (
   `id` int(11) NOT NULL AUTO_INCREMENT,
   `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
   `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
   `plugin_monitoring_commands_id` int(11) NOT NULL DEFAULT '0',
   `arguments` text DEFAULT NULL COLLATE utf8_unicode_ci,
   `plugin_monitoring_checks_id` int(11) NOT NULL DEFAULT '0',
   `active_checks_enabled` tinyint(1) NOT NULL DEFAULT '1',
   `passive_checks_enabled` tinyint(1) NOT NULL DEFAULT '1',
   `calendars_id` int(11) NOT NULL DEFAULT '0',
   `remotesystem` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
   `is_arguments` tinyint(1) NOT NULL DEFAULT '0',
   `alias_command` text DEFAULT NULL COLLATE utf8_unicode_ci,
   `graph_template` int(11) NOT NULL DEFAULT '0',
   `link` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
   `perfname` text DEFAULT NULL COLLATE utf8_unicode_ci,
   `perfnameinvert` text DEFAULT NULL COLLATE utf8_unicode_ci,
   `perfnamecolor` text DEFAULT NULL COLLATE utf8_unicode_ci,
   `plugin_monitoring_eventhandlers_id` int(11) NOT NULL DEFAULT '0',
   `freshness_count` int(6) NOT NULL DEFAULT '0',
   `freshness_type` varchar(255) DEFAULT 'seconds',
   `business_impact` tinyint(1) NOT NULL DEFAULT '3',
   PRIMARY KEY (`id`),
   KEY `plugin_monitoring_commands_id` (`plugin_monitoring_commands_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `glpi_plugin_monitoring_componentscatalogs_components` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plugin_monitoring_componentscatalogs_id` int(11) NOT NULL DEFAULT '0',
  `plugin_monitoring_components_id` int(11) NOT NULL DEFAULT '0',
  `backend_host_template` varchar(50) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unicity` (`plugin_monitoring_componentscatalogs_id`,`plugin_monitoring_components_id`),
  KEY `backend` (`plugin_monitoring_componentscatalogs_id`,`backend_host_template`),
  KEY `plugin_monitoring_componentscatalogs_id` (`plugin_monitoring_componentscatalogs_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `glpi_plugin_monitoring_componentscatalogs_hosts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plugin_monitoring_componentscatalogs_id` int(11) NOT NULL DEFAULT '0',
  `is_static` tinyint(1) NOT NULL DEFAULT '1',
  `items_id` int(11) NOT NULL DEFAULT '0',
  `itemtype` varchar(100) DEFAULT NULL,
  `plugin_monitoring_hosts_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `itemtype` (`itemtype`,`items_id`),
  KEY `plugin_monitoring_componentscatalogs_id` (`plugin_monitoring_componentscatalogs_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `glpi_plugin_monitoring_componentscatalogs_rules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plugin_monitoring_componentscatalogs_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `itemtype` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `condition` text DEFAULT NULL COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `plugin_monitoring_componentscatalogs_id` (`plugin_monitoring_componentscatalogs_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `glpi_plugin_monitoring_services` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entities_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `plugin_monitoring_components_id` int(11) NOT NULL DEFAULT '0',
  `plugin_monitoring_componentscatalogs_hosts_id` int(11) NOT NULL DEFAULT '0',
  `state` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `state_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `last_check` datetime DEFAULT NULL,
  `latency` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `execution_time` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `event` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `perf_data` text DEFAULT NULL COLLATE utf8_unicode_ci,
  `arguments` text DEFAULT NULL COLLATE utf8_unicode_ci,
  `networkports_id` int(11) NOT NULL DEFAULT '0',
  `is_acknowledged` tinyint(1) NOT NULL DEFAULT '0',
  `is_acknowledgeconfirmed` tinyint(1) NOT NULL DEFAULT '0',
  `acknowledge_comment` text DEFAULT NULL COLLATE utf8_unicode_ci,
  `acknowledge_users_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `state` (`state`(50),`state_type`(50)),
  KEY `plugin_monitoring_componentscatalogs_hosts_id` (`plugin_monitoring_componentscatalogs_hosts_id`),
  KEY `last_check` (`last_check`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `glpi_plugin_monitoring_contacttemplates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `ui_administrator` tinyint(1) NOT NULL DEFAULT '0',
  `ui_can_submit_commands` tinyint(1) NOT NULL DEFAULT '0',
  `hn_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `hn_period` int(11) NOT NULL DEFAULT '0',
  `hn_commands` int(11) NOT NULL DEFAULT '0',
  `hn_options_d` tinyint(1) NOT NULL DEFAULT '1',
  `hn_options_u` tinyint(1) NOT NULL DEFAULT '1',
  `hn_options_r` tinyint(1) NOT NULL DEFAULT '1',
  `hn_options_f` tinyint(1) NOT NULL DEFAULT '1',
  `hn_options_s` tinyint(1) NOT NULL DEFAULT '1',
  `hn_options_n` tinyint(1) NOT NULL DEFAULT '0',
  `sn_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `sn_period` int(11) NOT NULL DEFAULT '0',
  `sn_commands` int(11) NOT NULL DEFAULT '0',
  `sn_options_w` tinyint(1) NOT NULL DEFAULT '1',
  `sn_options_u` tinyint(1) NOT NULL DEFAULT '1',
  `sn_options_c` tinyint(1) NOT NULL DEFAULT '1',
  `sn_options_r` tinyint(1) NOT NULL DEFAULT '1',
  `sn_options_f` tinyint(1) NOT NULL DEFAULT '1',
  `sn_options_s` tinyint(1) NOT NULL DEFAULT '1',
  `sn_options_n` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `glpi_plugin_monitoring_contacts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `users_id` int(11) NOT NULL DEFAULT '0',
  `plugin_monitoring_contacttemplates_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `glpi_plugin_monitoring_contacts_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entities_id` int(11) NOT NULL DEFAULT '0',
  `is_recursive` tinyint(1) NOT NULL DEFAULT '0',
  `users_id` int(11) NOT NULL DEFAULT '0',
  `groups_id` int(11) NOT NULL DEFAULT '0',
  `items_id` int(11) NOT NULL DEFAULT '0',
  `itemtype` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `glpi_plugin_monitoring_commandtemplates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plugin_monitoring_commands_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(255) DEFAULT NULL,
  `key` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `glpi_plugin_monitoring_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `users_id` int(11) NOT NULL DEFAULT '0',
  `backend_login` varchar(255) DEFAULT NULL,
  `backend_password` varchar(255) DEFAULT NULL,
  `backend_token` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `glpi_plugin_monitoring_configs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `timezones` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '["0"]',
  `version` varchar(255) DEFAULT NULL,
  `log_retention` int(5) NOT NULL DEFAULT '30',
  `extra_debug` tinyint(1) NOT NULL DEFAULT '0',
  `build_files` tinyint(1) NOT NULL DEFAULT '1',
  `nrpe_prefix_container` tinyint(1) NOT NULL DEFAULT '0',
  `append_id_hostname` tinyint(1) NOT NULL DEFAULT '0',
  `fmwk_check_period` int(5) NOT NULL DEFAULT '60',
  `alignak_webui_url` varchar(255) DEFAULT 'http://127.0.0.1:5001',
  `alignak_backend_url` varchar(255) DEFAULT 'http://127.0.0.1:5000',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `glpi_plugin_monitoring_displayviews` (
   `id` int(11) NOT NULL AUTO_INCREMENT,
   `name` varchar(255) DEFAULT NULL,
   `entities_id` int(11) NOT NULL DEFAULT '0',
   `is_recursive` tinyint(1) NOT NULL DEFAULT '0',
   `is_active` tinyint(1) NOT NULL DEFAULT '0',
   `users_id` int(11) NOT NULL DEFAULT '0',
   `counter` varchar(255) DEFAULT NULL,
   `in_central` tinyint(1) NOT NULL DEFAULT '0',
   `width` int(5) NOT NULL DEFAULT '950',
   `is_frontview` tinyint(1) NOT NULL DEFAULT '0',
   `comment` text DEFAULT NULL COLLATE utf8_unicode_ci,
   PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `glpi_plugin_monitoring_displayviews_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pluginmonitoringdisplayviews_id` int(11) NOT NULL DEFAULT '0',
  `groups_id` int(11) NOT NULL DEFAULT '0',
  `entities_id` int(11) NOT NULL DEFAULT '-1',
  `is_recursive` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `pluginmonitoringdisplayviews_id` (`pluginmonitoringdisplayviews_id`),
  KEY `groups_id` (`groups_id`),
  KEY `entities_id` (`entities_id`),
  KEY `is_recursive` (`is_recursive`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `glpi_plugin_monitoring_displayviews_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pluginmonitoringdisplayviews_id` int(11) NOT NULL DEFAULT '0',
  `users_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `pluginmonitoringdisplayviews_id` (`pluginmonitoringdisplayviews_id`),
  KEY `groups_id` (`users_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `glpi_plugin_monitoring_displayviews_items` (
   `id` int(11) NOT NULL AUTO_INCREMENT,
   `plugin_monitoring_displayviews_id` int(11) NOT NULL DEFAULT '0',
   `x` int(5) NOT NULL DEFAULT '0',
   `y` int(5) NOT NULL DEFAULT '0',
   `items_id` int(11) NOT NULL DEFAULT '0',
   `itemtype` varchar(100) DEFAULT NULL,
   `extra_infos` varchar(255) DEFAULT NULL,
   `is_minemap` tinyint(1) NOT NULL DEFAULT '0',
   PRIMARY KEY (`id`),
   KEY `plugin_monitoring_displayviews_id` (`plugin_monitoring_displayviews_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `glpi_plugin_monitoring_displayviews_rules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plugin_monitoring_displayviews_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `itemtype` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `condition` text DEFAULT NULL COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `plugin_monitoring_displayviews_id` (`plugin_monitoring_displayviews_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `glpi_plugin_monitoring_entities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entities_id` int(11) NOT NULL DEFAULT '0',
  `tag` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `jet_lag` varchar(10) COLLATE utf8_unicode_ci DEFAULT '0',
  `graphite_prefix` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `business_impact` int(11) NOT NULL DEFAULT '3',
  `definition_order` int(11) NOT NULL DEFAULT '100',
  `freshness_count` int(6) NOT NULL DEFAULT '0',
  `freshness_type` varchar(255) DEFAULT 'seconds',
  `calendars_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `entities_id` (`entities_id`),
  KEY `tag` (`tag`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `glpi_plugin_monitoring_hostaddresses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `items_id` int(11) NOT NULL DEFAULT '0',
  `itemtype` varchar(100) DEFAULT NULL,
  `networkports_id` int(11) NOT NULL DEFAULT '0',
  `ipaddresses_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `glpi_plugin_monitoring_hostconfigs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `items_id` int(11) NOT NULL DEFAULT '0',
  `itemtype` varchar(100) DEFAULT NULL,
  `plugin_monitoring_components_id` int(11) NOT NULL DEFAULT '0',
  `plugin_monitoring_realms_id` int(11) NOT NULL DEFAULT '0',
  `computers_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `glpi_plugin_monitoring_hosts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entities_id` int(11) NOT NULL DEFAULT '0',
  `itemtype` varchar(100) DEFAULT NULL,
  `items_id` int(11) NOT NULL DEFAULT '0',
  `dependencies` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `state` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `state_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `last_check` datetime DEFAULT NULL,
  `latency` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `execution_time` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `event` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `perf_data` text DEFAULT NULL COLLATE utf8_unicode_ci,
  `is_acknowledged` tinyint(1) NOT NULL DEFAULT '0',
  `is_acknowledgeconfirmed` tinyint(1) NOT NULL DEFAULT '0',
  `acknowledge_comment` text DEFAULT NULL COLLATE utf8_unicode_ci,
  `acknowledge_users_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `itemtype` (`itemtype`,`items_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `glpi_plugin_monitoring_logs` (
  `id` bigint(30) NOT NULL AUTO_INCREMENT,
  `date_mod` datetime DEFAULT NULL,
  `user_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `itemtype` varchar(100) DEFAULT NULL,
  `items_id` int(11) NOT NULL DEFAULT '0',
  `action` varchar(100) DEFAULT NULL,
  `value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `glpi_plugin_monitoring_networkports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `items_id` int(11) NOT NULL DEFAULT '0',
  `itemtype` varchar(100) DEFAULT NULL,
  `networkports_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `glpi_plugin_monitoring_realms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `comment` text DEFAULT NULL COLLATE utf8_unicode_ci,
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci AUTO_INCREMENT=1;


CREATE TABLE IF NOT EXISTS `glpi_plugin_monitoring_hosttemplates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `comment` text DEFAULT NULL COLLATE utf8_unicode_ci,
  `template` text DEFAULT NULL COLLATE utf8_unicode_ci,
  `file` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci AUTO_INCREMENT=1;


CREATE TABLE IF NOT EXISTS `glpi_plugin_monitoring_redirecthomes` (
   `id` int(11) NOT NULL AUTO_INCREMENT,
   `users_id` int(11) NOT NULL DEFAULT '0',
   `is_redirected` tinyint(1) NOT NULL DEFAULT '0',
   PRIMARY KEY (`id`),
  KEY `users_id` (`users_id`)
) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci AUTO_INCREMENT=1;


CREATE TABLE IF NOT EXISTS `glpi_plugin_monitoring_serviceevents` (
  `id` bigint(30) NOT NULL AUTO_INCREMENT,
  `plugin_monitoring_services_id` int(11) NOT NULL DEFAULT '0',
  `date` datetime DEFAULT NULL,
  `event` varchar(4096) COLLATE utf8_unicode_ci DEFAULT NULL,
  `perf_data` text DEFAULT NULL COLLATE utf8_unicode_ci,
  `state` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `state_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `latency` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `execution_time` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `unavailability` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `plugin_monitoring_services_id` (`plugin_monitoring_services_id`),
  KEY `plugin_monitoring_services_id_2` (`plugin_monitoring_services_id`,`date`),
  KEY `unavailability` (`unavailability`,`state_type`,`plugin_monitoring_services_id`),
  KEY `plugin_monitoring_services_id_3` (`plugin_monitoring_services_id`,`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `glpi_plugin_monitoring_commands` (
   `id` int(11) NOT NULL AUTO_INCREMENT,
   `is_active` tinyint(1) NOT NULL DEFAULT '1',
   `name` varchar(255) DEFAULT NULL,
   `comment` text DEFAULT NULL COLLATE utf8_unicode_ci,
   `command_name` varchar(255) DEFAULT NULL,
   `command_line` text DEFAULT NULL COLLATE utf8_unicode_ci,
   `poller_tag` varchar(255) DEFAULT NULL,
   `module_type` varchar(255) DEFAULT NULL,
   `arguments` text DEFAULT NULL COLLATE utf8_unicode_ci,
   PRIMARY KEY (`id`),
   KEY `name` (`name`),
   KEY `command_name` (`command_name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `glpi_plugin_monitoring_eventhandlers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `name` varchar(255) DEFAULT NULL,
  `comment` text DEFAULT NULL COLLATE utf8_unicode_ci,
  `command_name` varchar(255) DEFAULT NULL,
  `command_line` text DEFAULT NULL COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `command_name` (`command_name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `glpi_plugin_monitoring_notificationcommands` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `name` varchar(255) DEFAULT NULL,
  `comment` text DEFAULT NULL COLLATE utf8_unicode_ci,
  `command_name` varchar(255) DEFAULT NULL,
  `command_line` text DEFAULT NULL COLLATE utf8_unicode_ci,
  `reactionner_tag` varchar(255) DEFAULT NULL,
  `module_type` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `glpi_plugin_monitoring_checks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `max_check_attempts` int(2) NOT NULL DEFAULT '1',
  `check_interval` int(5) NOT NULL DEFAULT '1',
  `retry_interval` int(5) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `glpi_plugin_monitoring_contactgroups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `glpi_plugin_monitoring_contacts_contactgroups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plugin_monitoring_contacts_id` int(11) NOT NULL DEFAULT '0',
  `plugin_monitoring_contactgroups_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unicity` (`plugin_monitoring_contacts_id`,`plugin_monitoring_contactgroups_id`),
  KEY `plugin_monitoring_contactgroups_id` (`plugin_monitoring_contactgroups_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `glpi_plugin_monitoring_contactgroups_contactgroups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plugin_monitoring_contactgroups_id_1` int(11) NOT NULL DEFAULT '0',
  `plugin_monitoring_contactgroups_id_2` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unicity` (`plugin_monitoring_contactgroups_id_1`,`plugin_monitoring_contactgroups_id_2`),
  KEY `plugin_monitoring_contactgroups_id_2` (`plugin_monitoring_contactgroups_id_2`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `glpi_plugin_monitoring_shinkenwebservices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `action` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cnt` tinyint(2) NOT NULL DEFAULT '0',
  `fields_string` text DEFAULT NULL COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `glpi_plugin_monitoring_tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `comment` text DEFAULT NULL COLLATE utf8_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `tag` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ip` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `locked_ip` tinyint(1) NOT NULL DEFAULT '0',
  `auto_restart` tinyint(1) NOT NULL DEFAULT '0',
  `username` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `glpi_plugin_monitoring_perfdatas` (
   `id` int(11) NOT NULL AUTO_INCREMENT,
   `name` varchar(255) DEFAULT NULL,
   `perfdata` text DEFAULT NULL COLLATE utf8_unicode_ci,
   PRIMARY KEY (`id`),
   KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `glpi_plugin_monitoring_perfdatadetails` (
   `id` int(11) NOT NULL AUTO_INCREMENT,
   `name` varchar(255) DEFAULT NULL,
   `dynamic_name` tinyint(1) NOT NULL DEFAULT '0',
   `plugin_monitoring_perfdatas_id` int(11) NOT NULL DEFAULT '0',
   `position` int(2) NOT NULL DEFAULT '0',
   `dsname_num` tinyint(1) NOT NULL DEFAULT '1',
   `dsname1` varchar(255) DEFAULT NULL,
   `dsname2` varchar(255) DEFAULT NULL,
   `dsname3` varchar(255) DEFAULT NULL,
   `dsname4` varchar(255) DEFAULT NULL,
   `dsname5` varchar(255) DEFAULT NULL,
   `dsname6` varchar(255) DEFAULT NULL,
   `dsname7` varchar(255) DEFAULT NULL,
   `dsname8` varchar(255) DEFAULT NULL,
   `dsname9` varchar(255) DEFAULT NULL,
   `dsname10` varchar(255) DEFAULT NULL,
   `dsname11` varchar(255) DEFAULT NULL,
   `dsname12` varchar(255) DEFAULT NULL,
   `dsname13` varchar(255) DEFAULT NULL,
   `dsname14` varchar(255) DEFAULT NULL,
   `dsname15` varchar(255) DEFAULT NULL,
   `dsnameincr1` tinyint(1) NOT NULL DEFAULT '0',
   `dsnameincr2` tinyint(1) NOT NULL DEFAULT '0',
   `dsnameincr3` tinyint(1) NOT NULL DEFAULT '0',
   `dsnameincr4` tinyint(1) NOT NULL DEFAULT '0',
   `dsnameincr5` tinyint(1) NOT NULL DEFAULT '0',
   `dsnameincr6` tinyint(1) NOT NULL DEFAULT '0',
   `dsnameincr7` tinyint(1) NOT NULL DEFAULT '0',
   `dsnameincr8` tinyint(1) NOT NULL DEFAULT '0',
   `dsnameincr9` tinyint(1) NOT NULL DEFAULT '0',
   `dsnameincr10` tinyint(1) NOT NULL DEFAULT '0',
   `dsnameincr11` tinyint(1) NOT NULL DEFAULT '0',
   `dsnameincr12` tinyint(1) NOT NULL DEFAULT '0',
   `dsnameincr13` tinyint(1) NOT NULL DEFAULT '0',
   `dsnameincr14` tinyint(1) NOT NULL DEFAULT '0',
   `dsnameincr15` tinyint(1) NOT NULL DEFAULT '0',
   PRIMARY KEY (`id`),
   KEY `plugin_monitoring_perfdatas_id` (`plugin_monitoring_perfdatas_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `glpi_plugin_monitoring_hostdailycounters` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`hostname` varchar(255) NOT NULL DEFAULT '',
	`day` date NOT NULL DEFAULT '2013-01-01',
	`dayname` varchar(16) NOT NULL DEFAULT '',
	`counters` varchar(4096) NOT NULL DEFAULT '',
	`cPaperChanged` int(11) NOT NULL DEFAULT '0',
	`cPrinterChanged` int(11) NOT NULL DEFAULT '0',
	`cBinEmptied` int(11) NOT NULL DEFAULT '0',
	`cPagesInitial` int(11) NOT NULL DEFAULT '0',
	`cPagesTotal` int(11) NOT NULL DEFAULT '0',
	`cPagesToday` int(11) NOT NULL DEFAULT '0',
	`cPagesRemaining` int(11) NOT NULL DEFAULT '0',
	`cRetractedInitial` int(11) NOT NULL DEFAULT '0',
	`cRetractedTotal` int(11) NOT NULL DEFAULT '0',
	`cRetractedToday` int(11) NOT NULL DEFAULT '0',
	`cRetractedRemaining` int(11) NOT NULL DEFAULT '0',
	`cPaperLoad` int(11) NOT NULL DEFAULT '0',
	`cCardsInsertedOkToday` int(11) NOT NULL DEFAULT '0',
	`cCardsInsertedOkTotal` int(11) NOT NULL DEFAULT '0',
	`cCardsInsertedKoToday` int(11) NOT NULL DEFAULT '0',
	`cCardsInsertedKoTotal` int(11) NOT NULL DEFAULT '0',
	`cCardsRemovedToday` int(11) NOT NULL DEFAULT '0',
	`cCardsRemovedTotal` int(11) NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`),
	KEY (`hostname`,`day`),
	KEY (`hostname`,`dayname`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `glpi_plugin_monitoring_hostcounters` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`hostname` varchar(255) DEFAULT NULL,
	`date` datetime DEFAULT NULL,
	`counter` varchar(255) DEFAULT NULL,
	`value` int(11) NOT NULL DEFAULT '0',
	`updated` tinyint(1) NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`),
	KEY `hostname` (`hostname`),
	KEY `updated` (`hostname`, `date`, `updated`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `glpi_plugin_monitoring_shinkenstates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `hostname` varchar(255) DEFAULT NULL,
  `service` varchar(255) DEFAULT NULL,
  `state` varchar(255) DEFAULT NULL,
  `state_type` varchar(255) DEFAULT NULL,
  `last_check` datetime DEFAULT NULL,
  `last_output` text DEFAULT NULL,
  `last_perfdata` text DEFAULT NULL,
  `is_ack` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `hostname` (`hostname`(160),`service`(160))
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `glpi_plugin_monitoring_hostnotificationtemplates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `hn_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `hn_period` int(11) NOT NULL DEFAULT '0',
  `hn_options_d` tinyint(1) NOT NULL DEFAULT '1',
  `hn_options_u` tinyint(1) NOT NULL DEFAULT '1',
  `hn_options_r` tinyint(1) NOT NULL DEFAULT '1',
  `hn_options_f` tinyint(1) NOT NULL DEFAULT '0',
  `hn_options_s` tinyint(1) NOT NULL DEFAULT '1',
  `hn_options_n` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `glpi_plugin_monitoring_servicenotificationtemplates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sn_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `sn_period` int(11) NOT NULL DEFAULT '0',
  `sn_options_c` tinyint(1) NOT NULL DEFAULT '1',
  `sn_options_w` tinyint(1) NOT NULL DEFAULT '1',
  `sn_options_u` tinyint(1) NOT NULL DEFAULT '1',
  `sn_options_x` tinyint(1) NOT NULL DEFAULT '1',
  `sn_options_r` tinyint(1) NOT NULL DEFAULT '1',
  `sn_options_f` tinyint(1) NOT NULL DEFAULT '0',
  `sn_options_s` tinyint(1) NOT NULL DEFAULT '1',
  `sn_options_n` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `glpi_plugin_monitoring_downtimes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plugin_monitoring_hosts_id` int(11) NOT NULL DEFAULT '0',
  `flexible` tinyint(1) DEFAULT '0',
  `start_time` datetime NOT NULL DEFAULT '2014-01-01 00:00:00',
  `end_time` datetime NOT NULL DEFAULT '2014-01-01 00:00:00',
  `duration` int(1) DEFAULT '24',
  `duration_type` varchar(64) COLLATE utf8_unicode_ci DEFAULT 'days',
  `comment` varchar(1024) COLLATE utf8_unicode_ci DEFAULT NULL,
  `services` tinyint(1) DEFAULT '0',
  `users_id` int(11) DEFAULT '-1',
  `tickets_id` int(11) DEFAULT '0',
  `notified` tinyint(1) DEFAULT '0',
  `expired` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `plugin_monitoring_hosts_id` (`plugin_monitoring_hosts_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `glpi_plugin_monitoring_acknowledges` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `itemtype` varchar(100) DEFAULT 'Host',
  `items_id` int(11) NOT NULL DEFAULT '0',
  `start_time` datetime NOT NULL DEFAULT '2014-01-01 00:00:00',
  `end_time` datetime NOT NULL DEFAULT '2014-01-01 00:00:00',
  `sticky` tinyint(1) DEFAULT '1',
  `persistent` tinyint(1) DEFAULT '1',
  `notify` tinyint(1) DEFAULT '1',
  `comment` varchar(1024) COLLATE utf8_unicode_ci DEFAULT NULL,
  `users_id` int(11) DEFAULT '-1',
  `notified` tinyint(1) DEFAULT '0',
  `expired` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `itemtype` (`itemtype`,`items_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
