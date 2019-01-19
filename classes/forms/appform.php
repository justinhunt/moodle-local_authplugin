<?php
/**
 * Created by PhpStorm.
 * User: ishineguy
 * Date: 2018/05/12
 * Time: 23:31
 */

namespace local_authplugin\forms;

defined('MOODLE_INTERNAL') || die();


class appform  extends baseform
{
    public function custom_definition() {
    //add wild card
        global $DB;

        //subscription id
        $this->_form->addElement('text', 'appid', get_string('appid', 'local_authplugin'));
        $this->_form->setType('appid', PARAM_TEXT);
        $this->_form->setDefault('appid', '');
        //transaction id
        $this->_form->addElement('text', 'appname', get_string('appname', 'local_authplugin'));
        $this->_form->setType('appname', PARAM_TEXT);
        $this->_form->setDefault('appname', '');

    }


}