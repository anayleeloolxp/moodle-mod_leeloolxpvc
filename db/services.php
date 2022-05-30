<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * External functions and service definitions.
 *
 * @package mod_leeloolxpvc
 * @copyright  2020 Leeloo LXP (https://leeloolxp.com)
 * @author Leeloo LXP <info@leeloolxp.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

// We defined the web service functions to install.
$functions = array(
    'mod_leeloolxpvc_update_recording' => array(
        'classname' => 'mod_leeloolxpvc_external',
        'methodname' => 'update_recording',
        'classpath' => 'mod/leeloolxpvc/externallib.php',
        'description' => 'Update Recording of leeloolxpvc Instance.',
        'type' => 'write',
    ),

    'mod_leeloolxpvc_view_leeloolxpvc' => array(
        'classname' => 'mod_leeloolxpvc_external',
        'methodname' => 'view_leeloolxpvc',
        'description' => 'Simulate the view.php web interface page: trigger events, completion, etc...',
        'type' => 'write',
        'capabilities' => 'mod/leeloolxpvc:view',
        'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    ),

    'mod_leeloolxpvc_get_leeloolxpvcs_by_courses' => array(
        'classname'     => 'mod_leeloolxpvc_external',
        'methodname'    => 'get_leeloolxpvcs_by_courses',
        'description'   => 'Returns a list of leeloolxpvcs in a provided list of courses.',
        'type'          => 'read',
        'capabilities'  => 'mod/leeloolxpvc:view',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    ),
);

// We define the services to install as pre-build services. A pre-build service is not editable by administrator.
$services = array(
    'Leeloo LXP VC API' => array(
        'functions' => array(
            'mod_leeloolxpvc_update_recording',
        ),
        'restrictedusers' => 0,
        'enabled' => 1,
    ),
);
