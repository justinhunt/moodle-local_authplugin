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
 * Action for adding/editing a subscription.
 * replace i) local_authplugin eg MOD_CST, then ii) authplugin eg cst, then iii) usersite eg fbquestion, then iv) create a capability
 *
 * @package local_authplugin
 * @copyright  2014 Justin Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/

require_once("../../../config.php");

use \local_authplugin\constants;
use \local_authplugin\subscription\submanager;
use \local_authplugin\forms\subform;

global $USER,$DB;

// first get the nfo passed in to set up the page
$id     = optional_param('id', 0,PARAM_INT);         // item id
$action = optional_param('action','edit',PARAM_TEXT);


//make sure we are logged in and can see this form
require_login();
$context = context_system::instance();

//require_capability('local/authplugin:itemedit', $context);

//set up the page object
$PAGE->set_url('/local/authplugin/subscriptions/managesubs.php', array('id'=>$id));
$PAGE->set_context($context);
$PAGE->set_title(get_string('addedititem','local_authplugin'));
$PAGE->set_heading(get_string('addedititem','local_authplugin'));

$PAGE->set_pagelayout('admin');

//are we in new or edit mode?
if ($id) {
    $item = $DB->get_record(constants::SUB_TABLE, array('id'=>$id), '*');
	if(!$item){
		print_error('could not find item of sub id:' . $id );
	}
    $edit = true;
} else {
    $edit = false;
}

//we always head back to the authplugin items page
$redirecturl = new moodle_url('/local/authplugin/authplugin_subscription.php', array());

	//handle delete actions
    if($action == 'confirmdelete'){
		$renderer = $PAGE->get_renderer('local_authplugin');
		echo $renderer->header('subscriptions', null, get_string('confirmitemdeletetitle', 'local_authplugin'));
		echo $renderer->confirm(get_string("confirmitemdelete","local_authplugin",$item->subscriptionname),
			new moodle_url('/local/authplugin/subscriptions/managesubs.php', array('action'=>'delete','id'=>$id)),
			$redirecturl);
		echo $renderer->footer();
		return;

	/////// Delete item NOW////////
    }elseif ($action == 'delete'){
    	require_sesskey();
		$success = submanager::delete_sub($item->subscriptionid);
        if (!$success){
            print_error("Could not delete authplugin subscription!");
            redirect($redirecturl);
        }
        redirect($redirecturl);
    }

    //create the subscription form
   $mform = new subform();

//if the cancel button was pressed, we are out of here
if ($mform->is_cancelled()) {
    redirect($redirecturl);
    exit;
}

//if we have data, then our job here is to save it and return to the quiz edit page
if ($data = $mform->get_data()) {
		require_sesskey();
		
		$theitem = new stdClass;
        $theitem->subscriptionid = $data->subscriptionid;
        $theitem->subscriptionname = $data->subscriptionname;
        $theitem->wildcard = $data->wildcard;
        if(!empty($data->apps)) {
            $theitem->apps = implode(',', $data->apps);
        }else{
            $theitem->apps ='';
        }
		$theitem->timemodified=time();
		
		//first insert a new item if we need to
		//that will give us a itemid, we need that for saving files
		if(!$edit){
            $ret  = submanager::create_sub( $theitem->subscriptionid,$theitem->subscriptionname, $theitem->apps, $theitem->wildcard);
			if (!$ret){
					print_error("Could not insert authplugin subscription!");
					redirect($redirecturl);
			}
		}else {
		    $theitem->id = $id;
		    if($data->subscriptionid!=$item->subscriptionid){
                print_error("I am sorry but you can not edit the subscription id. Delete and remake it. Nothing will happen to the subscription ids in the users table");
                redirect($redirecturl);
            }else {
                $ret = submanager::update_sub($theitem->subscriptionid, $theitem->subscriptionname, $theitem->apps, $theitem->wildcard);
                if (!$ret) {
                    print_error("Could not update authplugin subscription!");
                    redirect($redirecturl);
                }
            }
        }

		//go back to edit quiz page
		redirect($redirecturl);
}


//if  we got here, there was no cancel, and no form data, so we are showing the form
//if edit mode load up the item into a data object
if ($edit) {
	$data = $item;		
	$data->id = $item->id;
	$data->apps=explode(',',$item->apps);
}else{
	$data=new stdClass;
	$data->id = null;
}

    $mform->set_data($data);
	$renderer = $PAGE->get_renderer('local_authplugin');
	$mode='usersites';
	echo $renderer->header( get_string('edit', 'local_authplugin'));
	$mform->display();
	echo $renderer->footer();