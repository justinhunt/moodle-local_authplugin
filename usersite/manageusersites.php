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
 * Action for adding/editing a usersite.
 * replace i) local_authplugin eg MOD_CST, then ii) authplugin eg cst, then iii) usersite eg fbquestion, then iv) create a capability
 *
 * @package local_authplugin
 * @copyright  2014 Justin Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/

require_once("../../../config.php");

use \local_authplugin\constants;
use \local_authplugin\user\usersitemanager;
use \local_authplugin\forms\usersiteform;

global $USER,$DB;

// first get the nfo passed in to set up the page
$userid = required_param('userid' ,PARAM_INT);
$id     = optional_param('id', 0,PARAM_INT);         // item id
$action = optional_param('action','edit',PARAM_TEXT);


//make sure we are logged in and can see this form
require_login();
$context = context_system::instance();

//require_capability('local/authplugin:itemedit', $context);

//set up the page object
$PAGE->set_url('/local/authplugin/usersite/manageusersites.php', array('userid'=>$userid, 'id'=>$id));
$PAGE->set_context($context);
$PAGE->set_title(get_string('addedititem','local_authplugin'));
$PAGE->set_heading(get_string('addedititem','local_authplugin'));

$PAGE->set_pagelayout('admin');

//are we in new or edit mode?
if ($id) {
    $item = $DB->get_record(constants::USERSITE_TABLE, array('id'=>$id), '*');
	if(!$item){
		print_error('could not find item of id:' . $id );
	}
    $edit = true;
} else {
    $edit = false;
}

//we always head back to the authplugin items page
$redirecturl = new moodle_url('/local/authplugin/authplugin_user.php', array('selecteduser'=>$userid));

	//handle delete actions
    if($action == 'confirmdelete'){
		$renderer = $PAGE->get_renderer('local_authplugin');
		echo $renderer->header('usersites', null, get_string('confirmitemdeletetitle', 'authplugin'));
		echo $renderer->confirm(get_string("confirmitemdelete","local_authplugin",$item->url1),
			new moodle_url('/local/authplugin/usersite/manageusersites.php', array('action'=>'delete','id'=>$id,'userid'=>$userid)),
			$redirecturl);
		echo $renderer->footer();
		return;

	/////// Delete item NOW////////
    }elseif ($action == 'delete'){
    	require_sesskey();
		$success = usersitemanager::delete_usersite($id);
        if (!$success){
            print_error("Could not delete authplugin site!");
            redirect($redirecturl);
        }
        redirect($redirecturl);
    }

    //create the usersite form
   $mform = new usersiteform();

//if the cancel button was pressed, we are out of here
if ($mform->is_cancelled()) {
    redirect($redirecturl);
    exit;
}

//if we have data, then our job here is to save it and return to the quiz edit page
if ($data = $mform->get_data()) {
		require_sesskey();
		
		$theitem = new stdClass;
        $theitem->userid = $data->userid;
        $theitem->url1 = $data->url1;
        $theitem->wildcardok = 0;
        $theitem->expiredate = 0;
		$theitem->timemodified=time();
		
		//first insert a new item if we need to
		//that will give us a itemid, we need that for saving files
		if(!$edit){
            $ret  = usersitemanager::create_usersite($data->url1,$data->userid);
			if (!$ret){
					print_error("Could not insert authplugin item!");
					redirect($redirecturl);
			}
		}else {
		    $theitem->id = $id;
            $ret  = usersitemanager::update_usersite($theitem->id, $data->url1,$data->userid);
		    if (!$ret){
                print_error("Could not update authplugin item!");
                redirect($redirecturl);
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
    $data->userid = $item->userid;
}else{
	$data=new stdClass;
	$data->id = null;
	$data->userid= $userid;
}

    $mform->set_data($data);
	$renderer = $PAGE->get_renderer('local_authplugin');
	$mode='usersites';
	echo $renderer->header( get_string('edit', 'local_authplugin'));
	$mform->display();
	echo $renderer->footer();