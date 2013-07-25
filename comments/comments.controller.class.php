<?php 
class WPLeadsCommentsController{
	function processSub(){
		$post=WPLeadsInterface::sanitizeData($_REQUEST);
		if(isset($post["joinlist"])){ //awesome - they want to join our list :)
			$firstname=$post["author"];
			$lastname="WPLeads Generated";
			$email=$post["email"];
			//go through each integration type to determine the necessity for each
			$settings=WPLeadsInterface::getAllIntegrations();
			if($settings["mailchimp"]["configured"]) WPLeadsMailChimpController::list_subscribe($firstname,$lastname,$email);
			if($settings["constantcontact"]["configured"]) WPLeadsConstantContactController::list_subscribe($firstname,$lastname,$email);
			if($settings["salesforce"]["configured"]) WPLeadsSalesForceController::list_subscribe($firstname,$lastname,$email);
		}
	}
}
