<?php
class WPLeadsInterface{
	function processSetup(){
		$messages=array();
		$text=trim($_REQUEST["text"]);
		$comment_text=trim($_REQUEST["comment_text"]);
		if($text=="" || $comment_text==""){
			$messages["errors"][]="Error: The Join Our Mailing List text is required.  Please try again :)";
			return $messages;
		}
		WPLeadsInterface::set_wpleads_setup_registration_firstlast($_REQUEST["firstlast"]);
		WPLeadsInterface::set_wpleads_setup_registration_selected($_REQUEST["selected"]);
		WPLeadsInterface::set_wpleads_setup_registration_text($text);
		WPLeadsInterface::set_wpleads_setup_commentform_selected($_REQUEST["comment_selected"]);
		WPLeadsInterface::set_wpleads_setup_commentform_text($comment_text);
		if($_REQUEST["activated"] || $_REQUEST["comment_activated"]){
			if(WPLeadsInterface::integrationSet()){
				WPLeadsInterface::set_wpleads_setup_registration_activated($_REQUEST["activated"]);
				WPLeadsInterface::set_wpleads_setup_commentform_activated($_REQUEST["comment_activated"]);
				if($_REQUEST["activated"] && $_REQUEST["comment_activated"]){
					$messages["successes"][]="Perfect.  Users are now being asked to join your mailing list while registering and commenting.";
				}elseif($_REQUEST["activated"]){
					$messages["successes"][]="Yah! Users are now being asked to join your mailing list while registering.";
				}elseif($_REQUEST["comment_activated"]){
					$messages["successes"][]="Great. Users will now be asked to join your mailing list while commenting on your site.";
				}
			}else{
				$messages["errors"][]="Error: At least one integration type (MailChimp, Salesforce, or Constant Contact) must be configured before the system can be activated.";
			}
		}else{
			WPLeadsInterface::set_wpleads_setup_registration_activated($_REQUEST["activated"]);
			WPLeadsInterface::set_wpleads_setup_commentform_activated($_REQUEST["comment_activated"]);
			$messages["successes"][]="Oh No. Users are not being asked to join your mailing list at all.";
		}
		return $messages;
	}
	function processSync($login,$email,$errors){
		$post=WPLeadsInterface::sanitizeData($_REQUEST);
		if(empty($errors->errors)){
			//first, let's make sure the first and last name fields are entered - in case the user wants them added
			$setup=WPLeadsInterface::get_wp_settings();
			if($setup["firstlast"]){
				if($post["first_name"]==""){
					$errors->add('empty_realname',"<strong>Error:</strong> Please enter your first name");
				}
				if($post["last_name"]==""){
					$errors->add('empty_realname',"<strong>Error:</strong> Please enter your last name");
				}
			}
		}
		//now that we've successfully passed through the registration form's error handling, lets check to see if the user wants to join our mailing list :)-
		if(isset($post["joinlist"])){ //awesome - they do
			//first, define the first and last names (dependent upon the configuration)
			$firstname=($setup["firstlast"])?$post["first_name"]:"WPLeads First";
			$lastname=($setup["firstlast"])?$post["last_name"]:"WPLeads Last";
			//go through each integration type to determine the necessity for each
			$settings=WPLeadsInterface::getAllIntegrations();
			if($settings["mailchimp"]["configured"]) WPLeadsMailChimpController::list_subscribe($firstname,$lastname,$email);
			if($settings["constantcontact"]["configured"]) WPLeadsConstantContactController::list_subscribe($firstname,$lastname,$email);
			if($settings["salesforce"]["configured"]) WPLeadsSalesForceController::list_subscribe($firstname,$lastname,$email);
		}
	}
	function integrationSet(){
		$mailchimp=WPLeadsMailChimpController::get_wp_settings();
		$constantcontact=WPLeadsConstantContactController::get_wp_settings();
		$salesforce=WPLeadsSalesForceController::get_wp_settings();
		if($mailchimp["configured"] || $constantcontact["configured"] || $salesforce["configured"]){
			return true;
		}
		return false;
	}
	function registrationFormActivated(){
		$setup=WPLeadsInterface::get_wp_settings();
		if($setup["activated"]) return true;
		return false;
	}
	function commentFormActivated(){
		$setup=WPLeadsInterface::get_wp_settings();
		if($setup["comment_activated"]) return true;
		return false;
	}
	function deactivateRegistrationForm(){
		WPLeadsInterface::set_wpleads_setup_registration_activated(0);
	}
	function getConfiguredText($bool){
		if($bool) return "Yes";
		return "No";
	}
	function fullTrim($post){
		if(is_array($post)){
			foreach($post as $key=>$value){
				if(!is_array($value)){
					$values[$key]=trim($value);
				}else{
					$values[$key]=$value;
				}
			}
		}else{
			$values=trim($post);
		}
		return $values;
	}
	function sanitizeData($post){
		if(is_array($post)){
			foreach($post as $key=>$value){
				if(!is_array($value)){
					$values[$key]=stripslashes(htmlspecialchars($value,ENT_QUOTES));
				}else{
					$values[$key]=$value;
				}
			}
		}else{
			$values=stripslashes(htmlspecialchars($post,ENT_QUOTES));
		}
		return $values;
	}
	/**
	* Basic function designed to determine if a process is in error.
	*
	* @param mixed $process
	*
	* @return boolean true if $process is an array.  boolean false if $process is not an array.
	*/
	function isError($process){
		if(is_array($process)) return true;
		return false;
	}
	function getAllIntegrations(){
		$constantcontact=WPLeadsConstantContactController::get_wp_settings();
		$mailchimp=WPLeadsMailChimpController::get_wp_settings();
		$salesforce=WPLeadsSalesForceController::get_wp_settings();
		return array("constantcontact"=>$constantcontact,"mailchimp"=>$mailchimp,"salesforce"=>$salesforce);
	}
	function set_wpleads_setup_registration_activated($isActivated){
		update_option('wpleads_setup_registration_activated', array("activated"=>$isActivated));
	}
	function set_wpleads_setup_registration_selected($isSelected){
		update_option('wpleads_setup_registration_selected', array("selected"=>$isSelected));
	}
	function set_wpleads_setup_registration_firstlast($isFirstlast){
		update_option('wpleads_setup_registration_firstlast', array("firstlast"=>$isFirstlast));
	}
	function set_wpleads_setup_registration_text($text){
		update_option("wpleads_setup_registration_text",array("text"=>$text));
	}
	function set_wpleads_setup_commentform_activated($isActivated){
		update_option('wpleads_setup_commentform_activated', array("comment_activated"=>$isActivated));
	}
	function set_wpleads_setup_commentform_selected($isSelected){
		update_option('wpleads_setup_commentform_selected', array("comment_selected"=>$isSelected));
	}
	function set_wpleads_setup_commentform_text($text){
		update_option("wpleads_setup_commentform_text",array("comment_text"=>$text));
	}
	function get_wp_settings(){
		$activated=get_option("wpleads_setup_registration_activated");
		$firstlast=get_option("wpleads_setup_registration_firstlast");
		$text=get_option("wpleads_setup_registration_text");
		$selected=get_option("wpleads_setup_registration_selected");
		$comment_activated=get_option("wpleads_setup_commentform_activated");
		$comment_text=get_option("wpleads_setup_commentform_text");
		$comment_selected=get_option("wpleads_setup_commentform_selected");
		$return=array(
			"activated"=>$activated["activated"],
			"firstlast"=>$firstlast["firstlast"],
			"text"=>$text["text"],
			"selected"=>$selected["selected"],
			"comment_activated"=>$comment_activated["comment_activated"],
			"comment_text"=>$comment_text["comment_text"],
			"comment_selected"=>$comment_selected["comment_selected"]
			);
		$return=WPLeadsInterface::sanitizeData($return);
		return $return;
	}
}
