<?php
// This client for local_authplugin is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//

/**
 * XMLRPC client for Moodle 2 - local_authplugin
 *
 * This script does not depend of any Moodle code,
 * and it can be called from a browser.
 *
 * @author Jerome Mouneyrac
 */

/// MOODLE ADMINISTRATION SETUP STEPS
// 1- Install the plugin
// 2- Enable web service advance feature (Admin > Advanced features)
// 3- Enable XMLRPC protocol (Admin > Plugins > Web services > Manage protocols)
// 4- Create a token for a specific user and for the service 'My service' (Admin > Plugins > Web services > Manage tokens)
// 5- Run this script directly from your browser: you should see 'Hello, FIRSTNAME'


//DISABLE DISABLE DIASABLE
//we want to keep this so we can use it, but lets disable it.
//return;


/// SETUP - NEED TO BE CHANGED
//localhost user token
//$token = '7d98bdefae0d305180a9c047abd0bae8';

//localhost admin token
$token = 'ff4c4c49dff8defad2562d169a72281b';
$domainname = 'http://localhost/moodle';




if(true) {
    //REGISTER A USER / UPDATE / Add a subscription
    $functionname = 'local_authplugin_update_authplugin_user';
    $params = array();
    $params['wstoken'] = $token;
    $params['wsfunction'] = $functionname;
    $params['username'] = 'russell';
    $params['firstname'] = 'Russelliusx';
    $params['lastname'] = 'Crowie';
    $params['email'] = 'russelius@poodll.com';
    //the following are legacy/nnt need fields
    $params['expiredate'] = '1526971366';
    $params['subscriptionid'] = '2511';
    $params['transactionid'] = '991972';
    $params['awsaccessid'] = 'AWS123';
    $params['awsaccesssecret'] = 'AWSxxx';
}else if(false) {
    //RESET a users API secret
    $functionname = 'local_authplugin_reset_authplugin_secret';
    $params = array();
    $params['wstoken'] = $token;
    $params['wsfunction'] = $functionname;
    $params['username'] = 'russell';
    $params['currentsecret'] = 'WRy0PbL7XUB1nj9x';//'Password-123';
}else if(false) {
            //Update a users SITE urls
            $functionname = 'local_authplugin_update_authplugin_sites';
            $params = array();
            $params['wstoken'] = $token;
            $params['wsfunction'] = $functionname;
            $params['username'] = 'russell';
            $params['url1'] = 'http://localhost';
            $params['url2'] = 'https://russell.poodll.com';
            $params['url3'] = '';
            $params['url4'] = '';
            $params['url5'] = '';
}else if(false) {
/// FETCH UPLOAD FUNCTION NAME
    $functionname = 'local_authplugin_fetch_some_details';

/// PARAMETERS
    $params = array();
    $params['wstoken'] = $token;
    $params['wsfunction'] = $functionname;
    $params['reqtype'] = 'audio';
    $params['parent'] = 'http://localhost';
    $params['appid'] = 'mod_somethingorother';
    $params['textparam1'] = 'textparam1';
    $params['textparam2'] = 'textparam2';
    $params['textparam3'] = 'textparam3';
    $params['textparam4'] = 'textparam4';
    $params['intparam1'] = 0;
    $params['intparam2'] = 0;
    $params['intparam3'] = 0;
    $params['intparam4'] = 0;
}else{
    echo "at least ONE (and only one) API call in client needs to be marked 'true'";
    return;
}

//Token fetcher
/*
http://localhost/moodle/local/authplugin/authtoken.php?username=russell&password=Password-123&service=authplugin
Response:

{token:4ed876sd87g6d8f7g89fsg6987dfh78d}
*/

///REST Call
$restURL = $domainname . "/webservice/rest/server.php";
//params
$params['moodlewsrestformat']='json';//xml
$query = http_build_query($params);
//put it together
$restURL.= '?' . $query;

require_once('./curl.php');
$curl = new curl;
$resp = $curl->post($restURL);
print_r($resp);
die;

///// XML-RPC CALL
header('Content-Type: text/plain');
$serverurl = $domainname . '/webservice/xmlrpc/server.php'. '?wstoken=' . $token;
require_once('./curl.php');
$curl = new curl;
$post = xmlrpc_encode_request($functionname, array($mediatype,$parent));
$resp = xmlrpc_decode($curl->post($serverurl, $post));
print_r($resp);
