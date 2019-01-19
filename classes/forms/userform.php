<?php
/**
 * Created by PhpStorm.
 * User: ishineguy
 * Date: 2018/05/12
 * Time: 23:31
 */

namespace local_authplugin\forms;

defined('MOODLE_INTERNAL') || die();

class userform extends baseform
{
    public function custom_definition() {
        //add user selector
        $this->setUsersField('userid', get_string('user')) ;

        //reseller id
        $this->_form->addElement('text', 'resellerid', get_string('resellerid', 'local_authplugin'));
        $this->_form->setType('resellerid', PARAM_INT);
        $this->_form->setDefault('resellerid', 0);

        //AWS Access ID
        $this->_form->addElement('text', 'awsaccessid', get_string('awsaccessid', 'local_authplugin'));
        $this->_form->setType('awsaccessid', PARAM_TEXT);
        $this->_form->setDefault('awsaccessid', '');

        //AWS Access Secret
        $this->_form->addElement('text', 'awsaccesssecret', get_string('awsaccesssecret', 'local_authplugin'));
        $this->_form->setType('awsaccesssecret', PARAM_TEXT);
        $this->_form->setDefault('awsaccesssecret', '');
    }

}