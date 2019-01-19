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
 * External Web Service for CLoud Poodll API
 *
 * @package    local_authplugin
 * @copyright  2018 Justin Hunt (https://poodll.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once($CFG->libdir . "/externallib.php");

use \local_authplugin\workhorse;
use \local_authplugin\constants;

class local_authplugin_services extends external_api {


    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function fetch_some_details_parameters() {
        $params = array();
        $params['appid'] = new external_value(PARAM_TEXT, 'The request type', VALUE_DEFAULT, '');
        $params['parent'] = new external_value(PARAM_TEXT, 'The request type', VALUE_DEFAULT, '');
        $params['reqtype'] = new external_value(PARAM_TEXT, 'The request type', VALUE_DEFAULT, '');
        $params['textparam1'] = new external_value(PARAM_TEXT, 'A text param 1', VALUE_DEFAULT, '');
        $params['textparam2'] = new external_value(PARAM_TEXT, 'A text param 2', VALUE_DEFAULT, '');
        $params['textparam3'] = new external_value(PARAM_TEXT, 'A text param 3', VALUE_DEFAULT, '');
        $params['textparam4'] = new external_value(PARAM_TEXT, 'A text param 4', VALUE_DEFAULT, '');
        $params['intparam1'] = new external_value(PARAM_INT, 'An int param 1', VALUE_DEFAULT, 0);
        $params['intparam2'] = new external_value(PARAM_INT, 'An int param 2', VALUE_DEFAULT, 0);
        $params['intparam3'] = new external_value(PARAM_INT, 'An int param 3', VALUE_DEFAULT, 0);
        $params['intparam4'] = new external_value(PARAM_INT, 'An int param 4', VALUE_DEFAULT, 0);

        return new external_function_parameters(
                $params
        );
    }

    /**
     * Returns welcome message
     * @return array result of action
     */
    public static function fetch_some_details($appid,$parent,$reqtype,$textparam1,$textparam2,$textparam3,$textparam4,
                                              $intparam1,$intparam2,$intparam3,$intparam4) {
        global $USER;

        $rawparams = array();
        $rawparams['appid'] = $appid;
        $rawparams['parent'] = $parent;
        $rawparams['reqtype'] = $reqtype;
        $rawparams['textparam1'] = $textparam1;
        $rawparams['textparam2'] = $textparam2;
        $rawparams['textparam3'] = $textparam3;
        $rawparams['textparam4'] = $textparam4;
        $rawparams['intparam1'] = $intparam1;
        $rawparams['intparam2'] = $intparam2;
        $rawparams['intparam3'] = $intparam3;
        $rawparams['intparam4'] = $intparam4;



        //Parameter validation
        //REQUIRED
        $params = self::validate_parameters(self::fetch_some_details_parameters(),
                $rawparams);

        //Context validation
        //OPTIONAL but in most web service it should present
        $context = context_user::instance($USER->id);
        self::validate_context($context);

        //Capability checking
        //OPTIONAL but in most web service it should present
        if (!has_capability('local/authplugin:use', $context)) {
            throw new moodle_exception('nopermission');
        }

        //get user subs because we need these for checking everything
        $usersubs = workhorse::fetch_usersubs();

        //check if any subscriptions are active
        $ret = workhorse::are_subs_valid($usersubs);

        if($ret['success']) {
            //check if appid is valid
            $ret = workhorse::is_app_valid($appid, $usersubs);
            $wildcard = $ret['wildcard'];
            if ($ret['success']) {
                //check if url is valid and if so, get upload details that we require
                $ret = workhorse::is_parent_valid($parent, $wildcard);
                if ($ret['success']) {
                    $ret = workhorse::do_fetch_some_details($reqtype, $textparam1, $textparam2, $textparam3, $textparam4,
                        $intparam1, $intparam2, $intparam3, $intparam4);
                    if ($ret['success']) {
                        return $ret['payload'];
                    }
                }
            }
        }

        //if we got here, then we failed miserably
        //send back errors and a message.
        $result = array();
        $result['returnCode'] = '1';
        $result['returnData'] = $ret['payload'];

        return $result;
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function fetch_some_details_returns() {
        return
            new external_single_structure(
                array(
                    'returnCode' => new external_value(PARAM_INT, 'Indicates success or failure of the call'),
                    'returnData' => new external_value(PARAM_TEXT, 'contains data of some sort')
                )
        );
    }


    /*
     * Create user
     */

    public static function hello_world($welcomemessage = 'Hello world'){
        return 'that worked:' . $welcomemessage;
    }
    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function hello_world_parameters() {
        return new external_function_parameters(
            ['amessage' => new external_value(PARAM_TEXT, 'The message', VALUE_DEFAULT, 'bah blah')]
        );
    }
    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function hello_world_returns() {
        return new external_value(PARAM_TEXT, ' A hello world message');
    }

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function update_authplugin_user_parameters() {
        $params = array();
        $params['username'] = new external_value(PARAM_TEXT, 'The username that links authplugin and moodle records', VALUE_DEFAULT, '');
        $params['firstname'] = new external_value(PARAM_TEXT, 'The users first name', VALUE_DEFAULT, 'anonymous');
        $params['lastname'] = new external_value(PARAM_TEXT, 'The users last name', VALUE_DEFAULT, 'coward');
        $params['email'] = new external_value(PARAM_TEXT, 'The users email', VALUE_DEFAULT, 'anonymouscoward@poodll.com');
        $params['expiredate'] = new external_value(PARAM_INT, 'The number of days to hold the file', VALUE_DEFAULT, 0);
        $params['subscriptionid'] = new external_value(PARAM_INT, 'The id of the subscription(type)', VALUE_DEFAULT, 0);
        $params['transactionid'] = new external_value(PARAM_INT, 'The id of the transaction', VALUE_DEFAULT, 0);
        $params['awsaccessid'] = new external_value(PARAM_TEXT, 'The users IAM id', VALUE_DEFAULT, constants::AWSACCESSID_NONE);
        $params['awsaccesssecret'] = new external_value(PARAM_TEXT, 'The users IAM secret', VALUE_DEFAULT, constants::AWSACCESSSECRET_NONE);

        return new external_function_parameters(
            $params
        );
    }


    /*
     * Update user this will occur when a transaction completes on Poodll.com
     * $username
     * $firstname
     * $lastname
     * $email
     * $expiry
     * $subscriptionid
     * $transactionid
     */
    public static function update_authplugin_user($username,
                                           $firstname,
                                           $lastname,
                                           $email,
                                           $expiredate,
                                           $subscriptionid,
                                           $transactionid,
                                            $awsaccessid,
                                            $awsaccesssecret){

        global $USER;

        $rawparams = array();
        $rawparams['username'] =strtolower($username);
        $rawparams['firstname'] = $firstname;
        $rawparams['lastname'] = $lastname;
        $rawparams['email'] = $email;
        $rawparams['expiredate'] = $expiredate;
        $rawparams['subscriptionid'] = $subscriptionid;
        $rawparams['transactionid'] = $transactionid;
        $rawparams['awsaccessid'] = $awsaccessid;
        $rawparams['awsaccesssecret'] = $awsaccesssecret;

        //Parameter validation
        //REQUIRED
        $params = self::validate_parameters(self::update_authplugin_user_parameters(),
            $rawparams);

        //Context validation
        //OPTIONAL but in most web service it should present
        $context = context_user::instance($USER->id);
        self::validate_context($context);

        //Capability checking
        //OPTIONAL but in most web service it should present
        if (!has_capability('local/authplugin:use', $context)) {
            throw new moodle_exception('nopermission');
        }

        if (!has_capability('local/authplugin:manage', $context)) {
            throw new moodle_exception('nopermission');
        }


        //do the job and process the result
        $ret = workhorse::do_update_authplugin_user($params);
        $result = array();
        if($ret['success']) {
            $result['returnCode'] = '0';
            $result['returnMessage'] = "All good";
        }else{
            $result['returnCode'] = '1';
            $result['returnMessage'] = $ret['payload'];
        }

        return $result;
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function update_authplugin_user_returns() {
        return
            new external_single_structure(
                array(
                    'returnCode' => new external_value(PARAM_INT, 'Indicates success or failure of the call'),
                    'returnMessage' => new external_value(PARAM_TEXT, 'If call failed, contains a message about why')
                )
            );
    }//end of function

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function update_authplugin_sites_parameters() {
        $params = array();
        $params['username'] = new external_value(PARAM_TEXT, 'The username that links authplugin and moodle records', VALUE_DEFAULT, '');
        $params['url1'] = new external_value(PARAM_TEXT, 'The first of the users registered URLs', VALUE_DEFAULT, '');
        $params['url2'] = new external_value(PARAM_TEXT, 'The second of the users registered URLs', VALUE_DEFAULT, '');
        $params['url3'] = new external_value(PARAM_TEXT, 'The third of the users registered URLs', VALUE_DEFAULT, '');
        $params['url4'] = new external_value(PARAM_TEXT, 'The fourth of the users registered URLs', VALUE_DEFAULT, '');
        $params['url5'] = new external_value(PARAM_TEXT, 'The fifth of the users registered URLs', VALUE_DEFAULT, '');
        return new external_function_parameters(
            $params
        );
    }


    /*
   * Update CPAPI sites
     * $username
     * $email
     * $url1
     * $url2
     * $url3
     * $url4
     * $url5
   */
    public static function update_authplugin_sites($username,
                                           $url1,
                                           $url2,
                                           $url3,
                                           $url4,
                                           $url5){

        global $USER;

        $rawparams = array();
        $rawparams['username'] = strtolower($username);
        $rawparams['url1'] = $url1;
        $rawparams['url2'] = $url2;
        $rawparams['url3'] = $url3;
        $rawparams['url4'] = $url4;
        $rawparams['url5'] = $url5;

        //Parameter validation
        //REQUIRED
        $params = self::validate_parameters(self::update_authplugin_sites_parameters(),
            $rawparams);

        //Context validation
        //OPTIONAL but in most web service it should present
        $context = context_user::instance($USER->id);
        self::validate_context($context);

        //Capability checking
        //OPTIONAL but in most web service it should present
        if (!has_capability('local/authplugin:use', $context)) {
            throw new moodle_exception('nopermission');
        }

        if (!has_capability('local/authplugin:manage', $context)) {
            throw new moodle_exception('nopermission');
        }


        //do the job and process the result
        $ret = workhorse::do_update_authplugin_sites($params);
        $result = array();
        if($ret['success']) {
            $result['returnCode'] = '0';
            $result['returnMessage'] = "All good";
        }else{
            $result['returnCode'] = '1';
            $result['returnMessage'] = $ret['payload'];
        }

        return $result;
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function update_authplugin_sites_returns() {
        return
            new external_single_structure(
                array(
                    'returnCode' => new external_value(PARAM_INT, 'Indicates success or failure of the call'),
                    'returnMessage' => new external_value(PARAM_TEXT, 'If call failed, contains a message about why')
                )
            );
    }//end of function


    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function reset_authplugin_secret_parameters() {
        $params = array();
        $params['username'] = new external_value(PARAM_TEXT, 'The username that links authplugin and moodle records', VALUE_DEFAULT, '');
        $params['currentsecret'] = new external_value(PARAM_TEXT, 'The password that the user can then use later in their app to get a token', VALUE_DEFAULT, '');

        return new external_function_parameters(
            $params
        );
    }

    /*
  * Reset CPAPI secret
    * $username
    * $currentsecret
  */
    public static function reset_authplugin_secret($username,$currentsecret){

        global $USER;

        $rawparams = array();
        $rawparams['username'] = strtolower($username);
        $rawparams['currentsecret'] =$currentsecret;


        //Parameter validation
        //REQUIRED
        $params = self::validate_parameters(self::reset_authplugin_secret_parameters(),
            $rawparams);

        //Context validation
        //OPTIONAL but in most web service it should present
        $context = context_user::instance($USER->id);
        self::validate_context($context);

        //Capability checking
        //OPTIONAL but in most web service it should present
        if (!has_capability('local/authplugin:use', $context)) {
            throw new moodle_exception('nopermission');
        }

        if (!has_capability('local/authplugin:manage', $context)) {
            throw new moodle_exception('nopermission');
        }


        //do the job and process the result
        $ret = workhorse::do_reset_authplugin_secret($params);
        $result = array();
        if($ret['success']) {
            $result['returnCode'] = '0';
            $result['returnMessage'] = $ret['payload'];
        }else{
            $result['returnCode'] = '1';
            $result['returnMessage'] = $ret['payload'];
        }

        return $result;
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function reset_authplugin_secret_returns() {
        return
            new external_single_structure(
                array(
                    'returnCode' => new external_value(PARAM_INT, 'Indicates success or failure of the call'),
                    'returnMessage' => new external_value(PARAM_TEXT, 'If call failed, contains a message about why. If succeeds contains new secret')
                )
            );
    }//end of function

}//end of class
