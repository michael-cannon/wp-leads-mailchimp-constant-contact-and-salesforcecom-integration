<?
class WPLeadsSalesForceController{
	/**
     * Processes the SalesForce API Key request found at WPLeads -> SalesForce Integration
     * No parameters are required.  
     *
     * @return array on error.  Typical errors include: invalid API Key, or No API Key provided.  On Success, SalesForce Integration settings are updated
     * with the new API key.
     */
	function configureSalesforce(){
		//configuration variable in options - wplead_salesforce_settings
		//check that the API key was provided.  If not, return with error
		$apikey=trim($_REQUEST["salesforce_api_key"]);
		$username=trim($_REQUEST["salesforce_apiusername"]);
		$password=trim($_REQUEST["salesforce_apipassword"]);
		if($apikey=="" || $username=="" || $password==""){
			return array("errors"=>array("All API Credentials are required.  Please try again."));
		}
		
		//test the API to make certain it is valid
		$api = WPLeadsSalesForceController::getSalesForceConnection(array("apiusername"=>$username,"apipassword"=>$password,"apikey"=>$apikey));
		if(is_array($api)){
			return array("errors"=>array("Bummer. The SalesForce.com Credentials you provided are not valid.  Error Message: ".$api["error"]));
		}
		
		//first - if the current key is null - and we've successfully created a new key - let's congratulate them.
		$currentKey=WPLeadsSalesForceController::get_valid_salesforce_key();
		if($currentKey==""){
			$messages["successes"][]="Success!  Now you can decide whether to create a Contact or Lead within your SalesForce.com account below :)";
			WPLeadsSalesForceController::set_salesforce_last_updated();
			WPLeadsSalesForceController::set_salesforce_configured(0);
		}
		
		//now that we know the API key is valid, enter it into the system for future use.
		WPLeadsSalesForceController::set_salesforce_apiusername($username);
		WPLeadsSalesForceController::set_salesforce_apipassword($password);
		WPLeadsSalesForceController::set_salesforce_apikey($apikey);
		
		//If the key has already been set - they are changing the list settings.
		if($apikey==$currentKey && $currentKey!=""){ //make sure we're not changing the key
			WPLeadsSalesForceController::set_salesforce_listid($_REQUEST["salesforce_list_id"]);
			if($_REQUEST["salesforce_list_id"]){
				if($_REQUEST["salesforce_list_id"]=="1"){
					$messages["successes"][]="Great. All submissions will now create a new SalesForce.com contact.  Awesome.";
				}elseif($_REQUEST["salesforce_list_id"]=="2"){
					$messages["successes"][]="Perfect. Your leads are now being sent to your SalesForce.com account. Awesome.";
				}
				WPLeadsSalesForceController::set_salesforce_last_updated();
				WPLeadsSalesForceController::set_salesforce_configured(1);
			}else{
				$messages["successes"][]="You have chosen to not send leads to Sales Force.";
				WPLeadsSalesForceController::set_salesforce_last_updated();
				WPLeadsSalesForceController::set_salesforce_configured(0);
			}
		}
		//let the user know everything went well :) 
		return $messages;
	}

	/**
     * Requests a valid SalesForce API key.  Looks up the wplead_salesforce_settings apiKey option, then tests the provided API key against
     * the SalesForce Servers.
     *
     * No parameters are required.
     *
     * @return string containing the API key on success.  On Error, return array with the corresponding error message.
     * 
     */
	function get_valid_salesforce_key(){
		//check to make sure this is not the initial configuration
		$settings=get_option("wpleads_salesforce_apikey");
		if(!empty($settings["apikey"])){
			$api = WPLeadsSalesForceController::getSalesForceConnection(); 
			//if the current key is no longer valid, reset the key to null and return an error to the user, requesting a new key.  Otherwise
			//return the current key.
			if(is_array($api)){
				WPLeadsSalesForceController::set_salesforce_apiusername("");
				WPLeadsSalesForceController::set_salesforce_apipassword("");
				WPLeadsSalesForceController::set_salesforce_apikey("");
				WPLeadsSalesForceController::set_salesforce_listid("");
				WPLeadsSalesForceController::set_salesforce_last_updated();
				WPLeadsSalesForceController::set_salesforce_configured(0);
				return array("Your SalesForce.com API Credentials are no longer valid. Please enter new credentials below.");
			}else{
				return $settings["apikey"];
			}
			//if the key is currently null, then this is the initial configuration, return nothing.
		}
	}
	
	/**
     * Retrieves the SalesForce API key with the get_valid_salesforce_key function above, then retrieves the SalesForce lists associated with the retrieved Key.
     *
     * No parameters are required.
     *
     * @return string containing a select box with all SalesForce Lists associated with the API key.  
     * If the API key is no longer valid, return nothing.  This avoids an empty SalesForce List Integration option within the Add / Edit Wplead dialogs.
     * 
     */
	function get_lists(){
		$key=WPLeadsSalesForceController::get_wp_settings();
		$listSelection=null;
		$currentSalesForceID=null;
		if(!empty($key["apikey"])){
			$currentSalesForceID=WPLeadsSalesForceController::get_list_id();
			$listSelection="<select name='salesforce_list_id'>";
			$listSelection.="<option value='0'>Do not send to Sales Force</option>";
			$listSelection.=($currentSalesForceID=="1")?"<option value='1' selected>Create a New Contact</option>":"<option value='1'>Create a New Contact</option>";
			$listSelection.=($currentSalesForceID=="2")?"<option value='2' selected>Create a New Lead</option>":"<option value='2'>Create a New Lead</option>";
			$listSelection.="</select>";
		}
		return $listSelection;
	}
	/**
     * returns the list id for the SalesForce integration
     *
     * @return string listid
     *
     */
	function get_list_id(){
		$settings=get_option("wpleads_salesforce_listid"); 
		if(!empty($settings["listid"])){
			return $settings["listid"];
		}
	}
	
	/**
     * Subscribes new attendees to the SalesForce List associated with the corresponding Wplead.  Upon successful subscription, adds an attendee_id to wplead_id
     * relationship for possible backward integration.
     *
     * @param string $email - the email address to subscribe
     * 
     */
	function list_subscribe($firstname,$lastname,$email){
		$settings=WPLeadsSalesForceController::get_wp_settings();
		$type=($settings["listid"]==1)?"Contact":"Lead";
		$sf = WPLeadsSalesForceController::getSalesForceConnection(null,true);
		$postFields["firstName"]=$firstname;
		$postFields["lastName"]=$lastname;
		$postFields["Email"]=$email;
		if($type=="Lead"){
			$postFields["Company"]="WP-Leads Generated";
		}else{
			$postFields["Description"]="WP-Leads Generated";
		}
		$result=$sf->create(array($postFields),$type);
	}
	function getSalesForceConnection($credentials=null,$frontend=false){
		$loc=($frontend)?"":"../";
		$creds=(empty($credentials))?WPLeadsSalesForceController::get_wp_settings():$credentials;
		$crmHandle = new SforceEnterpriseClient();
		try {
			$crmHandle->createConnection($loc."wp-content/plugins/wp-leads-mailchimp-constant-contact-and-salesforcecom-integration/salesforce/soapclient/enterprise.wsdl.xml");
		} catch (Exception $e) {
			return array("error"=>$e->faultstring);
		}
		
		try {
			$crmHandle->login($creds["apiusername"], $creds["apipassword"] . $creds["apikey"]);
		} catch (Exception $e) {
			return array("error"=>$e->faultstring);
		}
		return $crmHandle;
	}
	function set_salesforce_apiusername($key){
		update_option('wpleads_salesforce_apiusername', array("apiusername"=>$key));
	}
	function set_salesforce_apipassword($key){
		update_option('wpleads_salesforce_apipassword', array("apipassword"=>$key));
	}
	function set_salesforce_apikey($key){
		update_option('wpleads_salesforce_apikey', array("apikey"=>$key));
	}
	function set_salesforce_listid($listid){
		update_option('wpleads_salesforce_listid', array("listid"=>$listid));
	}
	function set_salesforce_last_updated(){
		update_option('wpleads_salesforce_last_updated', array("last_updated"=>date("m/d/Y h:i:s")));
	}
	function set_salesforce_configured($isConfigured){
		update_option('wpleads_salesforce_configured', array("configured"=>$isConfigured));
	}
	function get_wp_settings(){
		$apiusername=get_option("wpleads_salesforce_apiusername");
		$apipassword=get_option("wpleads_salesforce_apipassword");
		$apikey=get_option("wpleads_salesforce_apikey");
		$listid=get_option("wpleads_salesforce_listid");
		$last_updated=get_option("wpleads_salesforce_last_updated");
		$configured=get_option("wpleads_salesforce_configured");
		$return=array(
			"apiusername"=>$apiusername["apiusername"],
			"apipassword"=>$apipassword["apipassword"],
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
