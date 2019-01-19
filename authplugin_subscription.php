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
use \local_authplugin\subscription\submanager;
use \local_authplugin\subscription\appmanager;

$context = context_system::instance();

/// Set up the page header
$PAGE->set_context($context);
$PAGE->set_url('/local/authplugin/authplugin_subscription.php');
$PAGE->set_title(get_string('authplugin_subscription','local_authplugin'));
$PAGE->set_heading(get_string('authplugin_subscription','local_authplugin'));
$PAGE->set_pagelayout('admin');

require_login();



//set up renderer and nav
$renderer = $PAGE->get_renderer('local_authplugin');
echo $renderer->header(get_string('authplugin_subscription', 'local_authplugin'),2);

global $DB;

$subs = submanager::get_subs();
$apps = appmanager::get_apps();
echo $renderer->add_sub_link();
echo $renderer->show_subs_list($subs);
echo $renderer->add_app_link();
echo $renderer->show_apps_list($apps);
echo $renderer->footer();