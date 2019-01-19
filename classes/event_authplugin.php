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

use local_authplugin\user\usermanager;

/**
 *
 * This is a class responding to Moodle create user events
 * @package   local_authplugin
 * @copyright 2016 Poodll Co. Ltd (https://poodll.com)
 * @author    Justin Hunt
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class event_authplugin
{

    /**
     * authplugin the event
     * Here we create a authplugin user table entry, when we get an event that a Moodle user has been created
     * The authplugin user table contains info about a users subscription
     *
     *
     */
    public static function create_user_handler($event)
    {
        //get the event data.
        $event_data = $event->get_data();

                try {
                    //user data
                    $userid=false;
                    if(array_key_exists('relateduserid', $event_data)){
                        $userid = $event_data['relateduserid'];
                        $event_data['userid'] = $userid;
                    }elseif(array_key_exists('userid', $event_data)){
                        $userid = $event_data['userid'];
                    }
                    if ($userid) {
                        $exists = usermanager::user_exists($userid);
                        if(!$exists){
                            $success = usermanager::create_user(0,$userid,"",0);
                        }
                    }

                } catch (\Exception $error) {
                    debugging("fetching user error for authplugin request  failed with error: " . $error->getMessage(), DEBUG_ALL);
                }
            }//end of create user handler
}