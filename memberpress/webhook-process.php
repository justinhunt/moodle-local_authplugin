<?php


require_once('authplugin_helper.php');


//get json from POST body
$raw_json = file_get_contents('php://input');
if(empty($raw_json)){
	poodll_log('no raw json'. "\r\n");
	return;
}

//convert to php object
$messageobject = json_decode($raw_json);
if(!$messageobject){
	poodll_log('decoded into nothing'. "\r\n");
	return;
}


/* call PoodLL functions */
switch($messageobject->event){

		
	case "transaction-completed":	
		poodll_log('entered event transaction-completed' . "\r\n");
		$membershipid = $messageobject->data->member->id;
		$nicename = $messageobject->data->member->nicename;
		$subscription =  $messageobject->data->membership;
		$subscriptionid =  $subscription->id;
		$transactionid = $messageobject->data->id;
		$starttime = time();
		if($subscription->trial){
			$expiretime = strtotime('+' . $subscription->trial_days . ' days', $starttime);
		}else{
			$expiretime = strtotime($messageobject->data->expires_at);
		}
		$expiretime_string =  date("Y-m-d H:i:s", $expiretime); 
		
		$user = wppoodll_process_transaction_completed($membershipid,$nicename,$expiretime_string,$subscriptionid,$transactionid,$messageobject->data);
		poodll_log($user);
		break;
		

}

/* Create AWS keys 
*
* we will just maintain a separate poodll table of aws keys and membershippress wont know about it.
* we will read and use them at wpppoodll_process_membership_codes, and set them via the webhook at create_aws_keys
 */
function wppoodll_process_transaction_completed($membershipid,$nicename,$expiretime, $subscriptionid,$transactionid, $data){
	 poodll_log('wppoodll_process_transaction_completed:' . $membershipid .':' . $nicename  . ':' . $subscriptionid . "\r\n");
	 
	

    
    $ret = poodll_authplugin_updateuser($data->member->username,
        $data->member->first_name,
        $data->member->last_name,
        $data->member->email,
        $expiretime,
        $subscriptionid,
        $transactionid
    );
    if($ret) {
        poodll_log('updated authplugin user:' . print_r($ret,true));
    }

    //set the URLs for the site
    $url1='';
    if(property_exists($data->member->profile,'mepr_moodle_site_url_1')){
        $url1=$data->member->profile->mepr_moodle_site_url_1;
    }
    $url2='';
    if(property_exists($data->member->profile,'mepr_moodle_site_url_2')){
        $url2=$data->member->profile->mepr_moodle_site_url_2;
    }
    $url3='';
    if(property_exists($data->member->profile,'mepr_moodle_site_url_3')){
        $url3=$data->member->profile->mepr_moodle_site_url_3;
    }
    $url4='';
    if(property_exists($data->member->profile,'mepr_moodle_site_url_4')){
        $url4=$data->member->profile->mepr_moodle_site_url_4;
    }
    $url5='';
    if(property_exists($data->member->profile,'mepr_moodle_site_url_5')){
        $url5=$data->member->profile->mepr_moodle_site_url_5;
    }
    $ret =poodll_update_authplugin_sites(
        $data->member->username,
        $url1, $url2, $url3, $url4, $url5
    );
    if($ret) {
        poodll_log('updated authplugin sites:' . print_r($ret,true));
    }

}
