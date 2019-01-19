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

namespace local_authplugin\user;

defined('MOODLE_INTERNAL') || die();

use \local_authplugin\constants;


/**
 *
 * This is a class containing functions for sending authplugins
 * @package   local_authplugin
 * @copyright 2016 Poodll Co. Ltd (https://poodll.com)
 * @author    Justin Hunt
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class usersitemanager
{


    /*
     * Delete a usersite
     *
     *
     *
     */
    public static function delete_usersite($siteid) {
        global $DB;
        $ret = $DB->delete_records(constants::USERSITE_TABLE, array('id'=>$siteid));
        return $ret;
    }

    /*
    * Create a  particular user sites
    *
    *
    *
    */
    public static function get_usersite($id) {
        global $DB;
        $site = $DB->get_record(constants::USERSITE_TABLE, array('id'=>$id));
        return $site;
    }

    /*
     * Create a users sites
     *
     *
     *
     */
    public static function get_usersites($userid) {
        global $DB;
        $ret = false;

        $sites = $DB->get_records(constants::USERSITE_TABLE, array('userid'=>$userid));
        if($sites){
            return $sites;
        }

        return $ret;
    }

    /*
   * Get a list of user sites ready for display
   *
   *
   *
   */
    public static function get_usersites_fordisplay($userid) {
        global $DB;
        $ret = false;

        $sites = $DB->get_records_sql('SELECT ust.*, u.username FROM {' . constants::USERSITE_TABLE . '} ust INNER JOIN {user} u ON u.id = ust.userid WHERE userid = ?'
        , array($userid));

        if($sites){
            return $sites;
        }

        return $ret;
    }

    public static function get_authpluginuser_by_username($username){
        global $DB;

        $authplugin_user = $DB->get_record_sql("SELECT authplugin.* FROM {" . constants::USER_TABLE .
                "} authplugin INNER JOIN {user} u ON u.id = authplugin.userid WHERE u.username = ?;",array($username));
        return $authplugin_user;
    }

    /*
    * Create a users sites
    *
    *
    *
    */
    public static function get_usersites_by_username($username)
    {
        $ret=false;

        $authplugin_user = self::get_authpluginuser_by_username($username);
        if($authplugin_user){
            $ret = self::get_usersites($authplugin_user->userid);
        }
        return $ret;
    }

    /*
     * Get all a user urls
     *
     */
    public static function get_userurls($userid) {
        $urls = array();
        $sites = self::get_usersites($userid);
        if($sites){
            foreach($sites as $site){
                $urls[] = $site->url1;
            }
        }
        return $urls;
    }

    /*
     * Get all a user urls
     *
     */
    public static function update_usersites_by_username($username,$url1,$url2,$url3,$url4,$url5) {

        global $DB;
        $ret = false;
        //remove all the sites
        $usersites = self::get_usersites_by_username($username);
        if($usersites) {
            foreach ($usersites as $usersite) {
                self::delete_usersite($usersite->id);
            }
        }

        //Re-register all the valid looking URLs
        $authplugin_user = self::get_authpluginuser_by_username($username);
        if(!$authplugin_user){return $ret;}
        $urls = array($url1,$url2,$url3,$url4,$url5);
        foreach($urls as $url){
            if(empty($url)){continue;}
            $url =trim($url);
            $url = strtolower($url);
            if(strpos($url,'http')===0) {
                self::create_usersite($url, $authplugin_user->userid);
            }
        }
        $ret =true;
        return $ret;
    }

    /*
     * Create a new usersite
     *
     *
     *
     */
    public static function create_usersite($url,$userid=false) {
        global $DB, $USER;
        $ret = false;

        //if the userid was not passed in, then we use the current user
        //this will be when added from webservice
        if(!$userid){
            $userid=$USER->id;
        }

        $thesite = new \stdClass;
        $thesite->userid = $userid;
        $thesite->url1 = $url;
        $thesite->wildcardok = 0;
        $thesite->expiredate = 0;
        $thesite->timemodified=time();

        $thesite->id = $DB->insert_record(constants::USERSITE_TABLE,$thesite);
        $ret = $thesite->id;

        return $ret;
    }

    public static function update_usersite($id, $url,$userid) {
        global $DB;

        //It should not be possible to not pass in a userid here
        if(!$userid){
            return false;
        }

        //build siteurl object
        $thesite = new \stdClass;
        $thesite->id = $id;
        $thesite->userid = $userid;
        $thesite->url1 = $url;
        $thesite->wildcardok = 0;
        $thesite->expiredate =0;
        $thesite->timemodified=time();

        //execute updaet and return
        $ret = $DB->update_record(constants::USERSITE_TABLE,$thesite);
        return $ret;
    }

}