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

namespace local_authplugin;

defined('MOODLE_INTERNAL') || die();

use local_authplugin\user\usersitemanager;
use local_authplugin\user\usermanager;
use local_authplugin\subscription\usersubmanager;

/**
 *
 * This is a class containing functions for Cloud Poodll API
 * @package   local_authplugin
 * @copyright 2018 Poodll Co. Ltd (https://poodll.com)
 * @author    Justin Hunt
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class workhorse
{


    /*
    * A simple array that allows us to pass back error messages with function returns
    */
    private static function get_return_array(){
      return array('success'=>false,'payload'=>'');
    }

    /*
     * Parts of this code  borrowed from poodll filter license manager
     *
     */
    private static function validate_url($parenturl, $wildcardok, $siteurl)
    {

        //get return array
        $ret = self::get_return_array();

        //get arrays of the wwwroot and registered url
        //just in case, lowercase'ify them
        $parenturl = strtolower($parenturl);
        $theregisteredurl = strtolower($siteurl->url1);

        $theregisteredurl = trim($theregisteredurl);
        $wwwroot_bits = parse_url($parenturl);
        $registered_bits = parse_url($theregisteredurl);

        //if neither parsed successfully, that a no straight up
        if (!$wwwroot_bits || !$registered_bits) {
            $ret['success'] = false;
            $ret['payload'] = 'FAILED: Parent URL or Registered URL is poorly formed ';
            return $ret;
        }

        //get the subdomain widlcard address, ie *.a.b.c.d.com
        $wildcard_subdomain_wwwroot = '';
        if (array_key_exists('host', $wwwroot_bits)) {
            $wildcardparts = explode('.', $wwwroot_bits['host']);
            $wildcardparts[0] = '*';
            $wildcard_subdomain_wwwroot = implode('.', $wildcardparts);
        } else {
            $ret['success'] = false;
            $ret['payload'] = 'FAILED: Parent URL has no host ';
            return $ret;
        }

        //match either the exact domain or the wildcard domain or fail
        if (array_key_exists('host', $registered_bits)) {
            //this will cover exact matches and path matches
            if ($registered_bits['host'] === $wwwroot_bits['host']) {
                $ret['success'] = true;
                return $ret;
                //this will cover subdomain matches but only for institution bigdog and enterprise license
            } elseif (($registered_bits['host'] === $wildcard_subdomain_wwwroot) && ($wildcardok)) {
                $ret['success'] = true;
                return $ret;
            } else {
                $ret['success'] = false;
                $ret['payload'] = 'FAILED: Parent URL does not match registered URL';
                return $ret;
            }
        } else {
            $ret['success'] = false;
            $ret['payload'] = 'FAILED: Registered URL does not have a host part';
            return $ret;
        }

    }

    /*
     * Convenience method to get subs for re-use to save a DB call
     *
     *
     */
    public static function fetch_usersubs()
    {
        global $USER;
        return usersubmanager::get_usersubs_apps($USER->id);
    }


    public static function are_subs_valid($usersubs=false){
        global $USER;

        //get return array
        $ret = self::get_return_array();

        //check domain licensed details
        if(!$usersubs) {
            $usersubs = usersubmanager::get_usersubs($USER->id);
        }

        if ($usersubs) {
            foreach ($usersubs as $sub) {
                if (($sub->expiredate > 0) && (time() <= $sub->expiredate)) {
                    $ret['success'] = true;
                    return $ret;
                }
            }
        }
        $ret['success'] = false;
        $ret['payload'] = 'FAILED: No current subscriptions';
        return $ret;

    }

    /*
     * Checks appid against apps registered for the subscription
     *
     *
     */
    public static function is_app_valid($appid,$usersubs=false)
    {
        global $USER;

        //get return array
        $ret = self::get_return_array();
        //a bit hacky ... but just adding a wildcard flag here
        $ret['wildcard']=false;

        //licensed flag
        $licensed =false;

        //check domain licensed details
        if(!$usersubs) {
            $usersubs = usersubmanager::get_usersubs_apps($USER->id);
        }

        if ($usersubs) {
            foreach ($usersubs as $sub) {

                //if the subscription has expired ... keep looking
                if (($sub->expiredate > 0) && (time() > $sub->expiredate)) {
                    continue;
                }

                //check the apps for this subscription
                $apps = explode(',',$sub->apps);
                foreach($apps as $app){
                    if($app==$appid){
                        $licensed=true;
                        //we keep looking in case we have a wildcard'able subscription for same app
                        //if we already found it however , then we break
                        if($sub->wildcard){
                            $ret['wildcard']=true;
                            break;
                        }
                    }
                }
            }
            if ($licensed) {
                $ret['success'] = true;
            }else{
                $ret['success'] = false;
                $ret['payload'] = 'App: ' . $appid . ' not authorised';
            }
        }else{
            $ret['success'] = false;
            $ret['payload'] = 'Could not find any registered subscriptions';
        }

        return $ret;
    }

    /*
     * Checks parent url against all registered URLs for validity
     *
     *
     */
    public static function is_parent_valid($parent,$wildcard)
    {
        global $USER;

        //get return array
        $ret = self::get_return_array();

        //licensed flag
        $licensed =false;


        //check domain licensed details
        $usersites = usersitemanager::get_usersites($USER->id);
        if ($usersites) {
            foreach ($usersites as $usersite) {
                $val_result  = self::validate_url($parent, $wildcard, $usersite);
                $licensed = $val_result['success'];
                if ($licensed) {
                    $ret['success'] = true;
                    break;
                }

            }
            if (!$licensed) {
                $ret['success'] = false;
                $ret['payload'] = 'Parent URL not licensed:';
            }
        } else {
            $ret['success'] = false;
            $ret['payload'] = 'Could not find any registered URLs';
        }

        return $ret;
    }


    /*
     * Fetch and return all details for the requested upload
     *
     *
     */
    public static function do_fetch_some_details($reqtype,$textparam1,$textparam2,$textparam3,$textparam4,
                                                 $intparam1,$intparam2,$intparam3,$intparam4)
    {
        //get return array
        $ret = self::get_return_array();

        $result = array();
        $result['returnCode'] = '0';
        $result['returnMessage'] = 'All ok';


        $ret['success']=true;
        $ret['payload']=$result;

        return $ret;

    }//end of function


    /*
    * Update the authplugin user
    *
    *
    */
    public static function do_update_authplugin_user($params)
    {

        //get return array
        $ret = self::get_return_array();

        //if the user has passed in a transaction id, we need to add/update a subscription
        if ($params['transactionid']!==0){

            //update the authplugin user
            $result = usermanager::update_authpluginuser_by_username($params['username'],$params['awsaccessid'],$params['awsaccesssecret']);
            //if unsuccessful we quit here
            if (!$result) {
                $ret['success'] = false;
                $ret['payload'] = 'unable to update the authplugin user: ';
                return $ret;
            }

            //get the authplugin user
            $authplugin_user = usersubmanager::get_authpluginuser_by_username($params['username']);

            //update the authplugin user's subs
            $result = usersubmanager::create_usersub($params['subscriptionid'],$params['transactionid'],
                $params['expiredate'],$authplugin_user->userid);
            //if unsuccessful we quit here
            if (!$result) {
                $ret['success'] = false;
                $ret['payload'] = 'unable to update the authplugin user';
                return $ret;
            }
        }

        //update the moodle user
        $result = usermanager::update_standarduser_by_username($params['username'],
            $params['firstname'],$params['lastname'],
            $params['email']);
        //if unsuccessful we quit here
        if(!$result){
            $ret['success']=false;
            $ret['payload']='unable to update the standard user';
            return $ret;
        }

        //report back on success (or not)
        if(!$result){
            $ret['success']=false;
            $ret['payload']='unable to re-register the site urls';
        }else{
            $ret['success']=true;
            $ret['payload']='All good';
        }
        return $ret;

    }

    /*
   * Rewrite all the authplugin user's sites.
   *
   *
   */
    public static function do_update_authplugin_sites($params)
    {
        //get return array
        $ret = self::get_return_array();

        //update the user sites
        $result = usersitemanager::update_usersites_by_username(
            $params['username'],
            $params['url1'],
            $params['url2'],
            $params['url3'],
            $params['url4'],
            $params['url5']
        );

        //report back on success (or not)
        if(!$result){
            $ret['success']=false;
            $ret['payload']='unable to re-register the site urls';
        }else{
            $ret['success']=true;
            $ret['payload']='All good';
        }
        return $ret;

    }

    /*
  * Create a random secret for the CPAPI user, set it, and return it
  *
  *
  */
    public static function do_reset_authplugin_secret($params)
    {
        //get return array
        $ret = self::get_return_array();

        //update the user sites
        $result = usermanager::reset_user_secret(
            $params['username'],
            $params['currentsecret']
        );

        //report back on success (or not)
        if(!$result){
            $ret['success']=false;
            $ret['payload']='unable to reset the secret';
        }else{
            $ret['success']=true;
            $ret['payload']=$result;
        }
        return $ret;

    }


}//end of class