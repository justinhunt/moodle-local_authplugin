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

namespace local_authplugin\subscription;

defined('MOODLE_INTERNAL') || die();

use \local_authplugin\constants;


/**
 *
 * This is a class containing functions for storing info about subs
 * @package   local_authplugin
 * @copyright 2016 Poodll Co. Ltd (https://poodll.com)
 * @author    Justin Hunt
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class submanager
{


    /*
     * Delete a usersite
     *
     *
     *
     */
    public static function delete_sub($subscriptionid) {
        global $DB;
        $ret = $DB->delete_records(constants::SUB_TABLE, array('subscriptionid'=>$subscriptionid));
        return $ret;
    }

    /*
    * Get a  particular subscription
    *
    *
    *
    */
    public static function get_sub($subscriptionid) {
        global $DB;
        $sub = $DB->get_record(constants::SUB_TABLE, array('subscriptionid'=>$subscriptionid));
        return $sub;
    }

    /*
   * Get a  particular subscription
   *
   *
   *
   */
    public static function get_subs() {
        global $DB;
        $sub = $DB->get_records(constants::SUB_TABLE, array());
        return $sub;
    }

    /*
     * Create a new subscription
     *
     *
     *
     */
    public static function create_sub($subid,$subname,$apps,$wildcard) {
        global $DB;

        //make sure we do not already have this sub. And if so, just update it.
        $thesub = $DB->get_record(constants::SUB_TABLE, array('subscriptionid'=>$subid));
        if($thesub){
            return self::update_sub($subid,$subname,$apps,$wildcard);
        }

        //add the sub
        $thesub = new \stdClass;
        $thesub->subscriptionid = $subid;
        $thesub->subscriptionname= $subname;
        $thesub->apps= $apps;
        $thesub->wildcard= $wildcard;
        $thesub->timemodified=time();

        $thesub->id = $DB->insert_record(constants::SUB_TABLE,$thesub);
        $ret = $thesub->id;
        return $ret;
    }

    public static function update_sub($subid,$subname,$apps,$wildcard) {
        global $DB;

        $thesub = $DB->get_record(constants::SUB_TABLE, array('subscriptionid'=>$subid));
        if(!$thesub){return false;}

        //build siteurl object
        $thesub->subscriptionname = $subname;
        $thesub->apps= $apps;
        $thesub->wildcard= $wildcard;
        $thesub->timemodified=time();

        //execute updaet and return
        $ret = $DB->update_record(constants::SUB_TABLE,$thesub);
        return $ret;
    }

}