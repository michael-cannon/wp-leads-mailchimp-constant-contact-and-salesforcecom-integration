<?
class WPLeadsConstantContactController{
	/**
     * Processes the ConstantContact API Key request found at WPLeads -> ConstantContact Integration
     * No parameters are required.  
     *
     * @return array on error.  Typical errors include: invalid API Key, or No API Key provided.  On Success, ConstantContact Integration settings are updated
     * with the new API key.
     */
	function configureConstantcontact(){
		//configuration variable in options - wplead_constantcontact_settings
		//check that the API key was provided.  If not, return with error
		$apikey=trim($_REQUEST["constantcontact_api_key"]);
		$username=trim($_REQUEST["constantcontact_apiusername"]);
		$password=trim($_REQUEST["constantcontact_apipassword"]);
		if($apikey=="" || $username=="" || $password==""){
			return array("errors"=>array("All API Credentials are required.  Please try again."));
		}
		
		//test the API to make certain it is valid
		$api = new WPLeads_CC_List($username,$password,$apikey); 
		$test = $api->testCredentials();
		if(empty($test)){
			return array("errors"=>array("No Constant Contact lists were returned for the credentials you provided.  Please try again."));
		}
		
		//first - if the current key is null - and we've successfully created a new key - let's congratulate them.
		$currentKey=WPLeadsConstantContactController::get_valid_constantcontact_key();
		if($currentKey==""){
			$messages["successes"][]="Success!  Now you can select a Constant Contact list below :)";
			WPLeadsConstantContactController::set_constantcontact_last_updated();
			WPLeadsConstantContactController::set_constantcontact_configured(0);
		}
		
		//now that we know the API key is valid, enter it into the system for future use.
		WPLeadsConstantContactController::set_constantcontact_apiusername($username);
		WPLeadsConstantContactController::set_constantcontact_apipassword($password);
		WPLeadsConstantContactController::set_constantcontact_apikey($apikey);
		
		//If the key has already been set - they are changing the list settings.
		if($apikey==$currentKey && $currentKey!=""){ //make sure we're not changing the key
			WPLeadsConstantContactController::set_constantcontact_listid($_REQUEST["constantcontact_list_id"]);
			if($_REQUEST["constantcontact_list_id"]){
				$messages["successes"][]="Perfect. Your leads are now being sent to your Constant Contact account. Awesome.";
				WPLeadsConstantContactController::set_constantcontact_last_updated();
				WPLeadsConstantContactController::set_constantcontact_configured(1);
			}else{
				$messages["successes"][]="You have chosen not to send leads to Constant Contact.";
				WPLeadsConstantContactController::set_constantcontact_last_updated();
				WPLeadsConstantContactController::set_constantcontact_configured(0);
			}
		}
		//let the user know how everything went :) 
		return $messages;
	}

	/**
     * Requests a valid ConstantContact API key.  Looks up the wplead_constantcontact_settings apiKey option, then tests the provided API key against
     * the ConstantContact Servers.
     *
     * No parameters are required.
     *
     * @return string containing the API key on success.  On Error, return array with the corresponding error message.
     * 
     */
	function get_valid_constantcontact_key(){
		//check to make sure this is not the initial configuration
		$settings=get_option("wpleads_constantcontact_apikey");
		if(!empty($settings["apikey"])){
			$creds=WPLeadsConstantContactController::get_wp_settings();
			$api = new WPLeads_CC_List($creds["apiusername"],$creds["apipassword"],$creds["apikey"]); 
			$test = $api->testCredentials();
			//if the current key is no longer valid, reset the key to null and return an error to the user, requesting a new key.  Otherwise
			//return the current key.
			if(empty($test)){
				WPLeadsConstantContactController::set_constantcontact_apiusername("");
				WPLeadsConstantContactController::set_constantcontact_apipassword("");
				WPLeadsConstantContactController::set_constantcontact_apikey("");
				WPLeadsConstantContactController::set_constantcontact_listid("");
				WPLeadsConstantContactController::set_constantcontact_last_updated();
				WPLeadsConstantContactController::set_constantcontact_configured(0);
				return array("Your API Credentials are no longer returning lists. Please enter new credentials below.");
			}else{
				return $settings["apikey"];
			}
			//if the key is currently null, then this is the initial configuration, return nothing.
		}
	}
	
	/**
     * Retrieves the ConstantContact API key with the get_valid_constantcontact_key function above, then retrieves the ConstantContact lists associated with the retrieved Key.
     *
     * No parameters are required.
     *
     * @return string containing a select box with all ConstantContact Lists associated with the API key.  
     * If the API key is no longer valid, return nothing.  This avoids an empty ConstantContact List Integration option within the Add / Edit Wplead dialogs.
     * 
     */
	function get_lists(){
		$key=WPLeadsConstantContactController::get_wp_settings();
		$listSelection=null;
		$currentConstantContactID=null;
		if(!empty($key["apikey"])){
			$currentConstantContactID=WPLeadsConstantContactController::get_list_id();
			$api = new WPLeads_CC_List($key["apiusername"],$key["apipassword"],$key["apikey"]); 
			$lists=$api->getLists();
			$listSelection="<select name='constantcontact_list_id'>";
			$listSelection.="<option value='0'>Do not send to Constant Contact</option>";
			foreach($lists as $list){
				$selected=($list["id"]==$currentConstantContactID)?" selected":"";
				$listSelection.="<option value='{$list["id"]}'$selected>{$list["title"]}</option>";
			}
			$listSelection.="</select>";
		}
		return $listSelection;
	}
	
	function get_list_id(){
		$settings=get_option("wpleads_constantcontact_listid"); 
		if(!empty($settings["listid"])){
			return $settings["listid"];
		}
	}
	
	/**
     * Subscribes new attendees to the ConstantContact List associated with the corresponding Wplead.
     *
     * @param string $email - the email address to subscribe
     * 
     */
	function list_subscribe($firstname,$lastname,$email){
		$settings=WPLeadsConstantContactController::get_wp_settings();
		$ccContactOBJ = new WPLeads_CC_Contact($settings["apiusername"],$settings["apipassword"],$settings["apikey"]);
		$postFields["lists"]=array($settings["listid"]);
		$postFields["first_name"]=$firstname;
		$postFields["last_name"]=$lastname;
		$postFields["email_address"]=$email;
		$contactXML = $ccContactOBJ->createContactXML(null,$postFields);
		if (!$ccContactOBJ->addSubscriber($contactXML)) {
		}
	}
	function set_constantcontact_apiusername($key){
		update_option('wpleads_constantcontact_apiusername', array("apiusername"=>$key));
	}
	function set_constantcontact_apipassword($key){
		update_option('wpleads_constantcontact_apipassword', array("apipassword"=>$key));
	}
	function set_constantcontact_apikey($key){
		update_option('wpleads_constantcontact_apikey', array("apikey"=>$key));
	}
	function set_constantcontact_listid($listid){
		update_option('wpleads_constantcontact_listid', array("listid"=>$listid));
	}
	function set_constantcontact_last_updated(){
		update_option('wpleads_constantcontact_last_updated', array("last_updated"=>date("m/d/Y h:i:s")));
	}
	function set_constantcontact_configured($isConfigured){
		update_option('wpleads_constantcontact_configured', array("configured"=>$isConfigured));
	}
	function get_wp_settings(){
		$apiusername=get_option("wpleads_constantcontact_apiusername");
		$apipassword=get_option("wpleads_constantcontact_apipassword");
		$apikey=get_option("wpleads_constantcontact_apikey");
		$listid=get_option("wpleads_constantcontact_listid");
		$last_updated=get_option("wpleads_constantcontact_last_updated");
		$configured=get_option("wpleads_constantcontact_configured");
		$return = array(
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
