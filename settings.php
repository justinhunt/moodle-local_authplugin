<?php  //$Id: settings.php,v 0.0.0.1 2010/01/15 22:40:00 thomw Exp $


/**
 *
 * This is a class containing settings for the authplugin plugin
 *
 * @package   local_authplugin
 * @copyright 2016 Poodll Co. Ltd (https://poodll.com)
 * @author    Justin Hunt
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/

defined('MOODLE_INTERNAL') || die;

//if ($ADMIN->fulltree) {

    // Ensure the configurations for this site are set
    if ($hassiteconfig ) {

        // Create the new settings page
        $settings = new admin_settingpage('local_authplugin',get_string('authpluginsettings', 'local_authplugin'));
        // Create
        $ADMIN->add('localplugins', $settings );

        $ADMIN->add('root', new admin_category('authplugin', new lang_string('pluginname', 'local_authplugin')));
        $ADMIN->add('authplugin', new admin_externalpage('authplugin/authplugin_user',
        new lang_string('authplugin_user', 'local_authplugin'),
        new moodle_url('/local/authplugin/authplugin_user.php')));
        $ADMIN->add('authplugin', new admin_externalpage('authplugin/authplugin_subscription',
            new lang_string('authplugin_subscription', 'local_authplugin'),
            new moodle_url('/local/authplugin/authplugin_subscription.php')));
    }
//}