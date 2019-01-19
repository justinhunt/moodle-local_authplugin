<?php
/**
 * Created by PhpStorm.
 * User: ishineguy
 * Date: 2018/05/12
 * Time: 23:31
 */

namespace local_authplugin\forms;

defined('MOODLE_INTERNAL') || die();


class subform  extends baseform
{
    public function custom_definition() {
    //add wild card
        global $DB;

        //subscription id
        $this->_form->addElement('text', 'subscriptionid', get_string('subscriptionid', 'local_authplugin'));
        $this->_form->setType('subscriptionid', PARAM_INT);
        $this->_form->setDefault('subscriptionid', 0);
        //transaction id
        $this->_form->addElement('text', 'subscriptionname', get_string('subscriptionname', 'local_authplugin'));
        $this->_form->setType('subscriptionname', PARAM_TEXT);
        $this->_form->setDefault('subscriptionname', '');

        //add apps selector
        $this->setAppsField('apps', get_string('apps','local_authplugin')) ;

        //add wild card
        $this->_form->addElement('selectyesno', 'wildcard', get_string('wildcard', 'local_authplugin'));
        $this->_form->setDefault('wildcard', 0);

    }


}