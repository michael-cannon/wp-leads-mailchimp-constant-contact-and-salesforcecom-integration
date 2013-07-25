<?
class WPLeadsMailChimpController{
	/**
     * Processes the MailChimp API Key request found at WPLeads -> MailChimp Integration
     * No parameters are required.  
     *
     * @return array on error.  Typical errors include: invalid API Key, or No API Key provided.  On Success, MailChimp Integration settings are updated
     * with the new API key.
     */
	function configureMailchimp(){
		//configuration variable in options - wplead_mailchimp_settings
		//check that the API key was provided.  If not, return with error
		$apikey=trim($_REQUEST["mailchimp_api_key"]);
		if($apikey==""){
			return array("errors"=>array("An API Key is Required."));
		}
		
		//test the API to make certain it is valid
		$api=new WPLeadsMCAPI($apikey,1);
		$api->lists();
		if($api->errorCode!=""){
			return array("errors"=>array("An error occurred while attempting to connect to the MailChimp server.  Error: $api->errorMessage"));
		}
		
		//first - if the current key is null - and we've successfully created a new key - let's congratulate them.
		$currentKey=WPLeadsMailChimpController::get_valid_mailchimp_key();
		if($currentKey==""){
			$messages["successes"][]="Success!  Now you can select a MailChimp list below :)";
			WPLeadsMailChimpController::set_mailchimp_last_updated();
			WPLeadsMailChimpController::set_mailchimp_configured(0);
		}
		
		//now that we know the API key is valid, enter it into the system for future use.
		WPLeadsMailChimpController::set_mailchimp_apikey($apikey);
		
		//If the key has already been set - they are changing the list settings.
		if($apikey==$currentKey && $currentKey!=""){ //make sure we're not changing the key
			WPLeadsMailChimpController::set_mailchimp_listid($_REQUEST["mailchimp_list_id"]);
			if($_REQUEST["mailchimp_list_id"]){
				$messages["successes"][]="Perfect. Your leads are now being sent to your MailChimp account. Awesome.";
				WPLeadsMailChimpController::set_mailchimp_last_updated();
				WPLeadsMailChimpController::set_mailchimp_configured(1);
			}else{
				$messages["successes"][]="Your API Key is Set. In order to complete the integration, please choose a MailChimp List below.";
				WPLeadsMailChimpController::set_mailchimp_last_updated();
				WPLeadsMailChimpController::set_mailchimp_configured(0);
			}
		}
		//let the user know everything went well :) 
		return $messages;
	}

	/**
     * Requests a valid MailChimp API key.  Looks up the wplead_mailchimp_settings apiKey option, then tests the provided API key against
     * the MailChimp Servers.
     *
     * No parameters are required.
     *
     * @return string containing the API key on success.  On Error, return array with the corresponding error message.
     * 
     */
	function get_valid_mailchimp_key(){
		//check to make sure this is not the initial configuration
		$settings=get_option("wpleads_mailchimp_apikey");
		if(!empty($settings["apikey"])){
			$api=new WPLeadsMCAPI($settings["apikey"],1);
			$api->lists();
			//if the current key is no longer valid, reset the key to null and return an error to the user, requesting a new key.  Otherwise
			//return the current key.
			if($api->errorCode !== ""){
				WPLeadsMailChimpController::set_mailchimp_apikey("");
				WPLeadsMailChimpController::set_mailchimp_listid("");
				WPLeadsMailChimpController::set_mailchimp_last_updated();
				WPLeadsMailChimpController::set_mailchimp_configured(0);
				return array("The API Key previously used ({$settings["apikey"]}) is no longer valid.  Please enter a new API key.");
			}else{
				return $settings["apikey"];
			}
			//if the key is currently null, then this is the initial configuration, return nothing.
		}
	}
	
	/**
     * Retrieves the MailChimp API key with the get_valid_mailchimp_key function above, then retrieves the MailChimp lists associated with the retrieved Key.
     *
     * No parameters are required.
     *
     * @return string containing a select box with all MailChimp Lists associated with the API key.  
     * If the API key is no longer valid, return nothing.  This avoids an empty MailChimp List Integration option within the Add / Edit Wplead dialogs.
     * 
     */
	function get_lists(){
		$key=WPLeadsMailChimpController::get_valid_mailchimp_key();
		$listSelection=null;
		$currentMailChimpID=null;
		if(!is_array($key) && !empty($key)){
			$currentMailChimpID=WPLeadsMailChimpController::get_list_id();
			$api=new WPLeadsMCAPI($key,1);
			$lists=$api->lists();
			$listSelection="<select name='mailchimp_list_id'>";
			$listSelection.="<option value='0'>Do not send to MailChimp</option>";
			foreach($lists["data"] as $listVars){
				$selected=($listVars["id"]==$currentMailChimpID)?" selected":"";
				$listSelection.="<option value='{$listVars["id"]}'$selected>{$listVars["name"]}</option>";
			}
			$listSelection.="</select>";
		}
		return $listSelection;
	}
	
	function get_list_id(){
		$settings=get_option("wpleads_mailchimp_listid"); 
		if(!empty($settings["listid"])){
			return $settings["listid"];
		}
	}
	
	/**
     * Subscribes new attendees to the MailChimp List associated with the corresponding Wplead.  Upon successful subscription, adds an attendee_id to wplead_id
     * relationship for possible backward integration.
     *
     * @param string $email - the email address to subscribe
     * 
     */
	function list_subscribe($fname,$lname,$email){
		$mailchimp=WPLeadsMailChimpController::get_wp_settings();
		$mailChimpListID=$mailchimp["listid"];
		//check to make sure the list ID is valid and available
		if($mailChimpListID){
			$mailChimpKey=WPLeadsMailChimpController::get_valid_mailchimp_key();
			//make certain the key is still valid with the MailChimp Servers
			if(!is_array($mailChimpKey) && !empty($mailChimpKey)){
				$api = new WPLeadsMCAPI($mailChimpKey);
				$merge_vars = array("FNAME"=>$fname,"LNAME"=>$lname);
				//subscribe the attendee to the selected MailChimp list
				$api->listSubscribe($mailChimpListID,$email,$merge_vars);
			}
		}
	}
	function set_mailchimp_apikey($key){
		update_option('wpleads_mailchimp_apikey', array("apikey"=>$key));
	}
	function set_mailchimp_listid($listid){
		update_option('wpleads_mailchimp_listid', array("listid"=>$listid));
	}
	function set_mailchimp_last_updated(){
		update_option('wpleads_mailchimp_last_updated', array("last_updated"=>date("m/d/Y h:i:s")));
	}
	function set_mailchimp_configured($isConfigured){
		update_option('wpleads_mailchimp_configured', array("configured"=>$isConfigured));
	}
	function get_wp_settings(){
		$apikey=get_option("wpleads_mailchimp_apikey");
		$listid=get_option("wpleads_mailchimp_listid");
		$last_updated=get_option("wpleads_mailchimp_last_updated");
		$configured=get_option("wpleads_mailchimp_configured");
		$return=array(
			"apikey"=>$apikey["apikey"],
			"listid"=>$listid["listid"],
			"last_updated"=>$last_updated["last_updated"],
			"configured"=>$configured["configured"]
			);
		$return=WPLeadsInterface::sanitizeData($return);
		return $return;
	}
}
?>
