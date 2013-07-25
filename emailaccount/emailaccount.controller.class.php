<?
class WPLeadsEmailAccountController{
	/**
     * Processes the EmailAccount request found at WPLeads -> EmailAccount Integration
     * No parameters are required.  
     *
     * @return array on error.  Typical errors include: invalid email address.  On Success, EmailAccount Integration settings are updated
     * with the new API key.
     */
	function configureEmailaccount(){
		$email=trim($_REQUEST["emailaccount_email"]);
		//if the email address is valid - enter it into the system.
		if(WPLeadsEmailAccountController::realEmail($email,false)){
			WPLeadsEmailAccountController::set_emailaccount_email($email);
			WPLeadsEmailAccountController::set_emailaccount_last_updated();
			WPLeadsEmailAccountController::set_emailaccount_configured(1);
			$messages["successes"]=array("Awesome. All leads will now be sent to your email address :)");
		}else{
			if($email==""){
				$messages["successes"]=array("You've chosen to not receive email alerts :)");
				WPLeadsEmailAccountController::set_emailaccount_email("");
				WPLeadsEmailAccountController::set_emailaccount_last_updated();
				WPLeadsEmailAccountController::set_emailaccount_configured(0);
			}else{
				$messages["errors"]=array("The email address you provided is not valid.  Please try again.");
			}
		}
		//let the user know everything went well :) 
		return $messages;
	}
	/**
     * Subscribes new attendees to the EmailAccount List associated with the corresponding Wplead.  Upon successful subscription, adds an attendee_id to wplead_id
     * relationship for possible backward integration.
     *
     * @param string $email - the email address to subscribe
     * 
     */
	function list_subscribe($wplead_id,$attendee_id,$attendee_fname,$attendee_lname,$attendee_email){
		WPLeadsEmailAccountController::get_wp_settings();
		$ccContactOBJ = new WPLeads_CC_Contact($settings["apiusername"],$settings["apipassword"],$settings["apikey"]);
		$postFields["lists"]=array($settings["listid"]);
		$postFields["email_address"]=$_POST["user_email"];
		$contactXML = $ccContactOBJ->createContactXML(null,$postFields);
		if (!$ccContactOBJ->addSubscriber($contactXML)) {
			//error handling fun
		}
	}
	function realEmail($email,$acceptempty=false){
		if($acceptempty){
			if(!eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,6})$", rtrim(ltrim($email))) && $email!='') {
				return false;
			}
		}else{
			if(!eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,6})$", rtrim(ltrim($email)))){
				return false;
			}
		}
		return true;                                         
	}
	function set_emailaccount_email($email){
		update_option('wpleads_emailaccount_email', array("email"=>$email));
	}
	function set_emailaccount_last_updated(){
		update_option('wpleads_emailaccount_last_updated', array("last_updated"=>date("m/d/Y h:i:s")));
	}
	function set_emailaccount_configured($isConfigured){
		$isConfigured=($isConfigured)?"Yes":"No";
		update_option('wpleads_emailaccount_configured', array("configured"=>$isConfigured));
	}
	function get_wp_settings(){
		$email=get_option("wpleads_emailaccount_email");
		$last_updated=get_option("wpleads_emailaccount_last_updated");
		$configured=get_option("wpleads_emailaccount_configured");
		return array(
			"email"=>$email["email"],
			"last_updated"=>$last_updated["last_updated"],
			"configured"=>$configured["configured"]
			);
	}
}
?>
