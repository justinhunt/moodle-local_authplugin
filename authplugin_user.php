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
 * Provides the interface for overall managing of items
 *
 * @package mod_authplugin
 * @copyright  2018 Justin Hunt  {@link http://poodll.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/

require_once('../../config.php');
require_once($CFG->libdir . '/adminlib.php');

use \local_authplugin\constants;
use \local_authplugin\subscription\usersubmanager;
use \local_authplugin\user\usersitemanager;

$context = context_system::instance();

/// Set up the page header
$PAGE->set_context($context);
$PAGE->set_url('/local/authplugin/authplugin_user.php');
$PAGE->set_title(get_string('authplugin_user','local_authplugin'));
$PAGE->set_heading(get_string('authplugin_user','local_authplugin'));
$PAGE->set_pagelayout('admin');

require_login();

//if admin, display a selectors so we can update contributor, site and sitecourseid
$userselector = new \local_authplugin\userselector('selecteduser', array());
$selecteduser = $userselector->get_selected_user();


//set up renderer and nav
$renderer = $PAGE->get_renderer('local_authplugin');
echo $renderer->header(get_string('authplugin_user', 'local_authplugin'),2);
echo $renderer->user_selection_form($userselector);



if($selecteduser){
    global $DB;
    $authplugin_user = $DB->get_record(constants::USER_TABLE, array('userid'=>$selecteduser->id));
    if($authplugin_user) {
        $siteitems = usersitemanager::get_usersites_fordisplay($selecteduser->id);
        $subsitems = usersubmanager::get_usersubs_fordisplay($selecteduser->id);

        echo $renderer->show_user_summary($selecteduser, $authplugin_user);
        echo $renderer->add_siteitem_link($selecteduser);
        echo $renderer->show_siteitems_list($siteitems, $selecteduser);
        echo $renderer->add_subsitem_link($selecteduser);
        echo $renderer->show_subsitems_list($subsitems, $selecteduser);
    }
}else{
    echo $renderer->heading(get_string('nouserselected', 'local_authplugin'), 3, 'main');
}

echo $renderer->footer();