<?php
/**
 * Created by PhpStorm.
 * User: ishineguy
 * Date: 2018/05/12
 * Time: 23:31
 */

namespace local_authplugin\forms;

defined('MOODLE_INTERNAL') || die();

class usersiteform extends baseform
{
    public function custom_definition() {
        //add wild card

        $this->_form->addElement('hidden', 'userid');
        $this->_form->setType('userid', PARAM_INT);


        $this->_form->addElement('text', 'url1', get_string('site', 'local_authplugin'));
        $this->_form->setType('url1', PARAM_TEXT);


    }

}