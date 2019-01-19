<?php

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
 * Web service local plugin template external functions and service definitions.
 *
 * @package    local_authplugin
 * @copyright  2018 Justin Hunt (justin@poodll.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// We defined the web service functions to install.
$functions = array(
    'local_authplugin_fetch_some_details' => array(
        'classname'   => 'local_authplugin_services',
        'methodname'  => 'fetch_upload_details',
        'classpath'   => 'local/authplugin/externallib.php',
        'description' => 'Will fetch upload details',
        'type'        => 'write',
        'services' => array('authplugin')
    ),

        'local_authplugin_hello_world' => array(
                'classname'   => 'local_authplugin_services',
                'methodname'  => 'hello_world',
                'classpath'   => 'local/authplugin/externallib.php',
                'description' => 'Will return a hello message',
                'type'        => 'read',
            'services' => array('authplugin')
        ),
        'local_authplugin_update_authplugin_user' => array(
            'classname'   => 'local_authplugin_services',
            'methodname'  => 'update_authplugin_user',
            'classpath'   => 'local/authplugin/externallib.php',
            'description' => 'Will update a CPAPI user',
            'type'        => 'write',
            'services' => array('authplugin')
        ),
        'local_authplugin_update_authplugin_sites' => array(
            'classname'   => 'local_authplugin_services',
            'methodname'  => 'update_authplugin_sites',
            'classpath'   => 'local/authplugin/externallib.php',
            'description' => 'Will update a CPAPI user site listings',
            'type'        => 'write',
            'services' => array('authplugin')
        ),
        'local_authplugin_reset_authplugin_secret' => array(
            'classname'   => 'local_authplugin_services',
            'methodname'  => 'reset_authplugin_secret',
            'classpath'   => 'local/authplugin/externallib.php',
            'description' => 'Will update a CPAPI user secret',
            'type'        => 'write',
            'services' => array('authplugin')
        )
);

// We define the services to install as pre-build services. A pre-build service is not editable by administrator.
$services = array(
        'AuthPlugin_API' => array(
                'functions' => array (
                    'local_authplugin_fetch_some_details'),
                'restrictedusers' => 0,
                'enabled'=>1,
            'shortname'=>'authplugin'
        )
);
