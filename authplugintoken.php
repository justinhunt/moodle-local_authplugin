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
 * Return token
 * @package    local_authplugin
 * @copyright  2018 justin hunt <justin@poodll.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('AJAX_SCRIPT', true);
define('REQUIRE_CORRECT_ACCESS', true);
define('NO_MOODLE_COOKIES', true);

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/externallib.php');

use local_authplugin\user\usersitemanager;
use local_authplugin\subscription\usersubmanager;
use local_authplugin\user\usermanager;

// Allow CORS requests.
//This works, but if server also adds it ... some clients will complain about multiple. Add this at server level
/*
 * e.g Access to XMLHttpRequest at 'https://cloud.poodll.com/webservice/rest/server.php' from origin 'http://localhost' has been blocked by CORS policy:
 * Response to preflight request doesn't pass access control check:
 * The 'Access-Control-Allow-Origin' header contains multiple values '*, *', but only one is allowed.
 *
 * */
header('Access-Control-Allow-Origin: *');

$username = required_param('username', PARAM_USERNAME);
$password = required_param('password', PARAM_RAW);
$serviceshortname  = required_param('service',  PARAM_ALPHANUMEXT);

echo $OUTPUT->header();

if (!$CFG->enablewebservices) {
    throw new moodle_exception('enablewsdescription', 'webservice');
}
$username = trim(core_text::strtolower($username));
if (is_restored_user($username)) {
    throw new moodle_exception('restoredaccountresetpassword', 'webservice');
}

$systemcontext = context_system::instance();

$user = authenticate_user_login($username, $password);
if (!empty($user)) {

    // Cannot authenticate unless maintenance access is granted.
    $hasmaintenanceaccess = has_capability('moodle/site:maintenanceaccess', $systemcontext, $user);
    if (!empty($CFG->maintenance_enabled) and !$hasmaintenanceaccess) {
        throw new moodle_exception('sitemaintenance', 'admin');
    }

    if (isguestuser($user)) {
        throw new moodle_exception('noguest');
    }
    if (empty($user->confirmed)) {
        throw new moodle_exception('usernotconfirmed', 'moodle', '', $user->username);
    }
    // check credential expiry
    //the function 'get_auth_plugin' is NOT part of this plugin. Its a core Moodle one.
    $userauth = get_auth_plugin($user->auth);
    if (!empty($userauth->config->expiration) and $userauth->config->expiration == 1) {
        $days2expire = $userauth->password_expire($user->username);
        if (intval($days2expire) < 0 ) {
            throw new moodle_exception('passwordisexpired', 'webservice');
        }
    }

    // let enrol plugins deal with new enrolments if necessary
    enrol_check_plugins($user);

    // setup user session to check capability
    \core\session\manager::set_user($user);

    //check if the service exists and is enabled
    $service = $DB->get_record('external_services', array('shortname' => $serviceshortname, 'enabled' => 1));
    if (empty($service)) {
        // will throw exception if no token found
        throw new moodle_exception('servicenotavailable', 'webservice');
    }

    // Get an existing token or create a new one.
    $token = external_generate_token_for_current_user($service);

    /*
    *  We had to edit external_generate_token_for_current_user()
     * to return a NEW token when the old one is 120 minutes from expiry
     * noting that change here
     *   lib/externalib.php/external_generate_token_for_current_user
     * // Remove token is not valid anymore.
        if (!empty($token->validuntil) and $token->validuntil < time()) {
            $DB->delete_records('external_tokens', array('token' => $token->token, 'tokentype' => EXTERNAL_TOKEN_PERMANENT));
            $unsettoken = true;
        }
    //===========================
        // Remove token is not valid anymore.
        if (!empty($token->validuntil) and $token->validuntil < time()) {
            $DB->delete_records('external_tokens', array('token' => $token->token, 'tokentype' => EXTERNAL_TOKEN_PERMANENT));
            $unsettoken = true;
        // generate a new token if this one will expire in next two hours
        } elseif(!empty($token->validuntil) and $token->validuntil < (time()-2 * HOURSECS)){
        	$unsettoken = true;
        }
    *
    */


    $privatetoken = $token->privatetoken;
    external_log_token_request($token);

    $siteadmin = has_capability('moodle/site:config', $systemcontext, $USER->id);

    $usertoken = new stdClass;
    $usertoken->token = $token->token;
    // Private token, only transmitted to https sites and non-admin users.
    if (is_https() and !$siteadmin) {
        $usertoken->privatetoken = $privatetoken;
    } else {
        $usertoken->privatetoken = null;
    }

    //return timestamps so that client can judge how long to cache
    $usertoken->validuntil = $token->validuntil;
    $usertoken->authpluginservertime = time();

    //get subscriptions
    $subs = usersubmanager::get_usersubs_apps($USER->id);
    $usertoken->subs=[];
    $usertoken->apps=[];
    if($subs) {
        foreach ($subs as $sub) {
            $usesub = new stdClass();
            $usesub->subscriptionid = $sub->subscriptionid;
            $usesub->subscriptionname = $sub->subscriptionname;
            $usesub->expiredate = $sub->expiredate;
            $usertoken->subs[] = $usesub;

            if(!empty($sub->apps) && $sub->expiredate>time() ){
                $apps = explode(',',$sub->apps);
                foreach($apps as $app) {
                    if(!in_array($app,$usertoken->apps)){
                        $usertoken->apps[]=$app;
                    }
                }
            }
        }
    }

    //get sites
    $sites = usersitemanager::get_usersites($USER->id);
    if($sites) {
        $usertoken->sites = [];
        foreach ($sites as $site) {
            $usertoken->sites[] = $site->url1;
        }
    }

    //get AWS creds
    $awscreds = usermanager::fetch_awscreds($USER->id);
    if($awscreds){
        $usertoken->awsaccessid=$awscreds->awsaccessid;
        $usertoken->awsaccesssecret=$awscreds->awsaccesssecret;
    }

    //turn it all into a json string and send it back
    echo json_encode($usertoken);

} else {
    throw new moodle_exception('invalidlogin');
}
