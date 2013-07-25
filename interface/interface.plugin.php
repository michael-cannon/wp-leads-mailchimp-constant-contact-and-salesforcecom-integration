<?php
/*******************************************************************************
*This file is used to manage class independent plugin structural components
*Necessary to generate the Plugin infrastructure within the WP framework
********************************************************************************/
add_action('admin_menu', 'wpleads_menu');
if(WPLeadsInterface::integrationSet()){
	if(WPLeadsInterface::commentFormActivated()){
		add_action('comment_form','wpleads_comments_form');
		add_action('comment_post','wpleads_comments_post');
	}
	if(WPLeadsInterface::registrationFormActivated()){ 
		add_action("register_form","wpleads_registration_form"); 
		add_action("register_post","wpleads_registration_form_post",10,3);
		add_action("user_register","wpleads_registration_form_registered");
	}
}
function wpleads_comments_form(){
	WPLeadsCommentsView::commentForm();
}
function wpleads_comments_post(){
	WPLeadsCommentsController::processSub();
}
function wpleads_menu() {
	add_menu_page("WPLeads Configuration", "WP-Leads", "manage_options", "wpleads_setup", "wpleads_setup_display");
	add_submenu_page("wpleads_setup","WP-Leads Setup","Setup","manage_options","wpleads_setup","wpleads_setup_display");
	add_submenu_page("wpleads_setup","WP-Leads Integrations","Integrations","manage_options","wpleads_configuration","wpleads_configuration_options");
}
function wpleads_registration_form(){
	WPLeadsInterfaceView::registrationForm();
}
function wpleads_registration_form_post($login,$email,$errors){
	WPLeadsInterface::processSync($login,$email,$errors);
}
function wpleads_registration_form_registered($user_id,$password="",$meta=array()){
	$setup=WPLeadsInterface::get_wp_settings();
	if($setup["firstlast"]){
		$post=WPLeadsInterface::sanitizeData($_REQUEST);
		$userdata=array();
		$userdata["id"]=$user_id;
		$userdata["first_name"]=$post["first_name"];
		$userdata["last_name"]=$post["last_name"];
		wp_update_user($userdata);
	}
}
function wpleads_setup_display(){
	if (!current_user_can('manage_options'))  {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}
	WPLeadsInterfaceView::head();
	if(isset($_REQUEST["wpleads_setup_post"])){
		$process=WPLeadsInterface::processSetup();
		WPleadsInterfaceView::wpleadsSetup($process);
	}else{
		WPLeadsInterfaceView::wpleadsSetup();
	}	
}
function wpleads_configuration_options() {
	if (!current_user_can('manage_options'))  {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}
	WPLeadsInterfaceView::head();
	if(isset($_REQUEST["configure_mailchimp"])){
		WPLeadsMailchimpView::configureMailchimp();
	}elseif(isset($_REQUEST["configure_mailchimp_post"])){
		$process=WPLeadsMailChimpController::configureMailchimp();
		WPLeadsMailchimpView::configureMailchimp($process);
	}elseif(isset($_REQUEST["configure_constantcontact"])){
		WPLeadsConstantcontactView::configureConstantcontact();
	}elseif(isset($_REQUEST["configure_constantcontact_post"])){
		$process=WPLeadsconstantcontactController::configureConstantcontact();
		WPLeadsConstantcontactView::configureConstantcontact($process);
	}elseif(isset($_REQUEST["configure_sugarcrm"])){
		WPLeadsSugarcrmView::configureSugarcrm();
	}elseif(isset($_REQUEST["configure_sugarcrm_post"])){
		$process=WPLeadssugarcrmController::configureSugarcrm();
		WPLeadsSugarcrmView::configureSugarcrm($process);
	}elseif(isset($_REQUEST["configure_sugarcrm"])){
		WPLeadsSugarcrmView::configureSugarcrm();
	}elseif(isset($_REQUEST["configure_sugarcrm_post"])){
		$process=WPLeadssugarcrmController::configureSugarcrm();
		WPLeadsSugarcrmView::configureSugarcrm($process);
	}elseif(isset($_REQUEST["configure_salesforce"])){
		WPLeadsSalesforceView::configureSalesforce();
	}elseif(isset($_REQUEST["configure_salesforce_post"])){
		$process=WPLeadssalesforceController::configureSalesforce();
		WPLeadsSalesforceView::configureSalesforce($process);
	}elseif(isset($_REQUEST["configure_emailaccount"])){
		WPLeadsEmailaccountView::configureEmailaccount();
	}elseif(isset($_REQUEST["configure_emailaccount_post"])){
		$process=WPLeadsemailaccountController::configureEmailaccount();
		WPLeadsEmailaccountView::configureEmailaccount($process);
	}else{
		WPLeadsInterfaceView::configurationTable();
	}
	WPLeadsInterfaceView::foot();
}

function wpleads_help_display(){
	if (!current_user_can('manage_options'))  {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}
	echo '<div class="wrap">';
	echo '<p>This is Going to be Very Helpful :)</p>';
	echo '</div>';
}
