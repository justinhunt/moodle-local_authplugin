<?php
/**
 * Created by PhpStorm.
 * User: ishineguy
 * Date: 2018/05/12
 * Time: 23:31
 */

namespace local_authplugin\forms;

defined('MOODLE_INTERNAL') || die();

class usersubform extends baseform
{
    public function custom_definition() {

        $this->_form->addElement('hidden', 'userid');
        $this->_form->setType('userid', PARAM_INT);

        //add subscriptions selector
        $this->setSubsField('subscriptionid', get_string('subscription','local_authplugin')) ;

        //expiredate
        $dateopts = array(
            'startyear' => 2018,
            'stopyear'  => 2030,
            'timezone'  => 99,
            'optional'  => false
        );
        $this->_form->addElement('date_selector', 'expiredate', get_string('expiredate', 'local_authplugin'),$dateopts);

        //transaction id
        $this->_form->addElement('text', 'transactionid', get_string('transactionid', 'local_authplugin'));
        $this->_form->setType('transactionid', PARAM_TEXT);
        $this->_form->setDefault('transactionid', '');
    }
}