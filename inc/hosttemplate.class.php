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

class PluginMonitoringHosttemplate extends CommonDropdown
{
    public $display_dropdowntitle = false;

    public $first_level_menu = "plugins";
    public $second_level_menu = "pluginmonitoringmenu";
    public $third_level_menu = "host_template";

    static $rightname = 'plugin_monitoring_componentscatalog';


    /**
     * Initialization called on plugin installation
     * @param Migration $migration
     */
    function initialize($migration)
    {
        // Default file type Cfg
        $input = [];
        $input['name'] = "Monitoring host template";
        $input['comment'] = __("Default realm", 'monitoring');
        $input['icon'] = 'default-dist.png';
        $input['ext'] = 'cfg';
        $this->add($input);
        $migration->displayMessage("  created Cfg document type");
    }


    static function getTypeName($nb = 0)
    {
        return _n('Host template', 'Host templates', $nb, 'monitoring');
    }


    function defineTabs($options = []) {

        $ong = [];
        $this->addDefaultFormTab($ong)
            ->addStandardTab('Document_Item', $ong, $options)
            ->addStandardTab('Log', $ong, $options);

        return $ong;
    }


    function prepareInputForAdd($input)
    {
        $input['name'] = preg_replace("/[^A-Za-z0-9]/", "", $input['name']);
        return $input;
    }


    function prepareInputForUpdate($input)
    {
        $input['name'] = preg_replace("/[^A-Za-z0-9]/", "", $input['name']);

        $input = $this->addFiles($input);

        return $input;
    }


    function getAdditionalFields()
    {
        return [
            [
                'name' => 'template',
                'label' => __('Host template (Nagios lefacy file format)', 'monitoring'),
                'type' => 'template_string'
            ],
            [
                'name' => 'file',
                'label' => __('Host template file', 'monitoring'),
                'type' => 'template_file'
            ]
        ];
    }


    function displaySpecificTypeField($ID, $field = array())
    {
        global $CFG_GLPI;

        switch ($field['type']) {
            case 'template_string' :
                $rand_text = mt_rand();
                $content_id = "content$rand_text";
                $cols = 90;
                $rows = 6;
                if ($CFG_GLPI["use_rich_text"]) {
                    $cols = 100;
                    $rows = 10;
                }

                Html::textarea([
                    'name' => 'template',
                    'value' => $this->fields["template"],
                    'rand' => $rand_text,
                    'editor_id' => $content_id,
                    'enable_fileupload' => false,
                    'enable_richtext' => $CFG_GLPI["use_rich_text"],
                    'cols' => $cols,
                    'rows' => $rows]);
                break;

            case 'template_file' :
                $rand = mt_rand();
                $full_picture = "<div class='user_picture_border'>";
                $full_picture .= "<p>Coucou test de fred</p>";
                $full_picture .= "</div>";

                Html::showTooltip($full_picture, ['applyto' => "picture$rand"]);
                echo Html::file([
                    'name' => 'file',
                    'display' => true,
                    'onlyimages' => false]);
                echo "<input type='checkbox' name='_blank_picture'>&nbsp;".__('Clear');
                break;
        }
    }


    function post_addItem() {
        // Add document if needed
        $this->input = $this->addFiles($this->input, ['force_update'  => true,
            'content_field' => 'file']);
    }

}

