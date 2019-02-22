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


/**
 * Toolbox of various utility methods
 **/
class PluginMonitoringToolbox
{

    static function loadLib()
    {
        global $CFG_GLPI;

        // Use tools/minify.sh t ocreate a minified JS
        echo '<script src="' . $CFG_GLPI["root_doc"] . '/plugins/monitoring/lib/scripts-1.js"></script>';

        /*
        echo '<script src="'.$CFG_GLPI["root_doc"].'/plugins/monitoring/lib/nvd3/lib/d3.v3.min.js"></script>
        <script src="'.$CFG_GLPI["root_doc"].'/plugins/monitoring/lib/nvd3/nv.d3.min.js"></script>
        <script src="'.$CFG_GLPI["root_doc"].'/plugins/monitoring/lib/nvd3/src/tooltip.js"></script>
        <script src="'.$CFG_GLPI["root_doc"].'/plugins/monitoring/lib/nvd3/src/utils.js"></script>
        <script src="'.$CFG_GLPI["root_doc"].'/plugins/monitoring/lib/nvd3/src/interactiveLayer.js"></script>
        <script src="'.$CFG_GLPI["root_doc"].'/plugins/monitoring/lib/nvd3/src/models/legend.js"></script>
        <script src="'.$CFG_GLPI["root_doc"].'/plugins/monitoring/lib/nvd3/src/models/axis.js"></script>
        <script src="'.$CFG_GLPI["root_doc"].'/plugins/monitoring/lib/nvd3/src/models/scatter.js"></script>
        <script src="'.$CFG_GLPI["root_doc"].'/plugins/monitoring/lib/nvd3/src/models/line.js"></script>
        <script src="'.$CFG_GLPI["root_doc"].'/plugins/monitoring/lib/nvd3/src/models/lineChart.js"></script>
        <script src="'.$CFG_GLPI["root_doc"].'/plugins/monitoring/lib/gauge.min.js"></script>
        <script src="'.$CFG_GLPI["root_doc"].'/plugins/monitoring/lib/jqueryplugins/tooltipsy/tooltipsy.min.js"></script>
        <script src="'.$CFG_GLPI["root_doc"].'/plugins/monitoring/lib/jqueryplugins/tooltipsy/jquery.tipsy.min.js"></script>
        <script src="'.$CFG_GLPI["root_doc"].'/plugins/monitoring/lib/jqueryplugins/jquery-ui/jquery-ui.min.js"></script>';
        */
    }


    /**
     * Log a message
     *
     * @param $message string or array
     */
    static function log($message)
    {
        /*
        * Call the Glpi base file logging function:
        * - base filename
        * - log message
        * - force file logging - not set to use the default Glpi configuration (use_log_in_files)
        */
        if (is_array($message)) {
            $message = print_r($message, true);
        }
        Toolbox::logInFile(PLUGIN_MONITORING_LOG, $message . "\n");
    }

    /**
     * Log when extra-debug is activated
     *
     * @param $message string or array
     */
    static function logIfDebug($message)
    {
        if (PluginMonitoringConfig::getValue('extra_debug')) {
            self::log($message);
        }
    }

    /*
     * Recursive copy
     */
    static function copyr($source, $dest)
    {
        // recursive function to copy
        // all subdirectories and contents:
        if (is_dir($source)) {
            $dir_handle = opendir($source);
            $sourcefolder = basename($source);
            mkdir($dest . "/" . $sourcefolder);
            while ($file = readdir($dir_handle)) {
                if ($file != "." && $file != "..") {
                    if (is_dir($source . "/" . $file)) {
                        self::copyr($source . "/" . $file, $dest . "/" . $sourcefolder);
                    } else {
                        copy($source . "/" . $file, $dest . "/" . $file);
                    }
                }
            }
            closedir($dir_handle);
        } else {
            // can also handle simple copy commands
            copy($source, $dest);
        }
    }
}
