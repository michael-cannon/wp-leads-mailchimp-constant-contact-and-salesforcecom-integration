<?php
/*
Plugin Name: WP-Leads (Free) - MailChimp, Constant Contact, and SalesForce.com Integration
Plugin URI: http://www.wpleads.com
Description: Integrating with MailChimp, Constant Contact, and SalesForce.com is now a snap.  Once configured, users can be asked to join your mailing list through the WordPress Registration process or through the Comments form.
Version: 1.2
Author: Anthony Leon
Author URI: http://www.wpleads.com
License: GPLv2
Usage: Set as many or as few integration options as you like.  Once configured, user information will be sent automatically to your Lead Management Systems, such as MailChimp, Constant Contact, and SalesForce.com.  Simple.  For more detailed instructions, visit http://www.WPLeads.com 

    Copyright (c) 2010-2011  Anthony Leon (email : questions@wpleads.com)

	The name WP-Leads(tm) is a trademark of TalkLearn.com

	This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
/*
	A NOTE ABOUT LICENSE:

	While this plugin is released as free and open-source under the GPL2
	license, that does not mean it is "public domain." You are free to modify
	and redistribute as long as you comply with the license. Any derivative 
	work MUST be GPL licensed and available as open source.  You also MUST give 
	proper attribution to the original author, copyright holder, and trademark
	owner.  This means you cannot change two lines of code and claim copyright 
	of the entire work as your own.  If you are unsure or have questions about 
	how a derivative work you are developing complies with the license, 
	copyright, trademark, or if you do not understand the difference between
	open source and public domain, contact the original author at:
	questions@wpleads.com.


	INSTALLATION PROCEDURE:
	
	For complete installation and usage instructions,
	visit http://www.wpleads.com
*/
ini_set('soap.wsdl_cache_enabled', 0);
//mail chimp configuration
require_once("mailchimp/MCAPI.class.php"); 
require_once("mailchimp/mailchimp.controller.class.php"); 
require_once("mailchimp/mailchimp.view.class.php"); 
//constant contact configuration
require_once("constantcontact/cc_class.php");
require_once("constantcontact/constantcontact.controller.class.php");
require_once("constantcontact/constantcontact.view.class.php");
//Email Account Configuration
require_once("emailaccount/emailaccount.controller.class.php");
require_once("emailaccount/emailaccount.view.class.php");
//SalesForce.com
require_once("salesforce/soapclient/SforceEnterpriseClient.php");
require_once("salesforce/salesforce.controller.class.php");
require_once("salesforce/salesforce.view.class.php");
//general interface configuration
require_once("interface/interface.class.php");
require_once("interface/interface.view.class.php");
require_once("interface/interface.plugin.php"); 
//comments integration configuration
require_once("comments/comments.controller.class.php");
require_once("comments/comments.view.class.php");

function wpleads_install(){
	//run install routines, setup basic Integration variables within the options environment.
	add_option("wplead_active","true","","yes");
	update_option("wplead_active","true");
	add_option("wplead_settings","","","yes");
	//mailchimp variables
	add_option("wpleads_mailchimp_apikey","","","yes");
	add_option("wpleads_mailchimp_listid","","","yes");
	add_option("wpleads_mailchimp_last_updated","","","yes");
	update_option("wpleads_mailchimp_last_updated", array("last_updated"=>date("m/d/Y h:i:s")));
	add_option("wpleads_mailchimp_configured","","","yes");
	update_option("wpleads_mailchimp_configured", array("configured"=>0));
	//constant contact variables
	add_option("wpleads_constantcontact_apiusername","","","yes");
	add_option("wpleads_constantcontact_apipassword","","","yes");
	add_option("wpleads_constantcontact_apikey","","","yes");
	add_option("wpleads_constantcontact_listid","","","yes");
	add_option("wpleads_constantcontact_last_updated","","","yes");
	update_option("wpleads_constantcontact_last_updated", array("last_updated"=>date("m/d/Y h:i:s")));
	add_option("wpleads_constantcontact_configured","","","yes");
	update_option("wpleads_constantcontact_configured", array("configured"=>0));
	//email address variables
	add_option("wpleads_emailaccount_email","","","yes");
	add_option("wpleads_emailaccount_last_updated","","","yes");
	update_option("wpleads_emailaccount_last_updated", array("last_updated"=>date("m/d/Y h:i:s")));
	add_option("wpleads_emailaccount_configured","","","yes");
	update_option("wpleads_emailaccount_configured", array("configured"=>0));
	//Salesforce.com
	add_option("wpleads_salesforce_apiusername","","","yes");
	add_option("wpleads_salesforce_apipassword","","","yes");
	add_option("wpleads_salesforce_apikey","","","yes");
	add_option("wpleads_salesforce_listid","","","yes");
	add_option("wpleads_salesforce_last_updated","","","yes");
	update_option("wpleads_salesforce_last_updated", array("last_updated"=>date("m/d/Y h:i:s")));
	add_option("wpleads_salesforce_configured","","","yes");
	update_option("wpleads_salesforce_configured", array("configured"=>0));
	//setup
	add_option("wpleads_setup_registration_activated","","","yes");
	update_option("wpleads_setup_registration_activated",array("activated"=>0));
	add_option("wpleads_setup_registration_firstlast","","","yes");
	update_option("wpleads_setup_registration_firstlast",array("firstlast"=>1));
	add_option("wpleads_setup_registration_text","","","yes");
	update_option("wpleads_setup_registration_text",array("text"=>"Join our Mailing List?"));
	add_option("wpleads_setup_registration_selected","","","yes");
	update_option("wpleads_setup_registration_selected",array("selected"=>1));
	add_option("wpleads_setup_commentform_activated","","","yes");
	update_option("wpleads_setup_commentform_activated",array("comment_activated"=>0));
	add_option("wpleads_setup_commentform_text","","","yes");
	update_option("wpleads_setup_commentform_text",array("comment_text"=>"Join our Mailing List?"));
	add_option("wpleads_setup_commentform_selected","","","yes");
	update_option("wpleads_setup_commentform_selected",array("comment_selected"=>1));
}

function wpleads_deactivate(){
	update_option("wplead_active","false"); //set the activation flag to false
	update_option('wplead_settings', ""); //reset the API key to null.
	//mailchimp
	update_option('wpleads_mailchimp_apikey','');
	update_option('wpleads_mailchimp_listid','');
	update_option('wpleads_mailchimp_last_updated','');
	update_option('wpleads_mailchimp_configured','');
	//constant contact variables
	update_option("wpleads_constantcontact_apiusername",'');
	update_option("wpleads_constantcontact_apipassword",'');
	update_option("wpleads_constantcontact_apikey",'');
	update_option("wpleads_constantcontact_listid",'');
	update_option("wpleads_constantcontact_last_updated",'');
	update_option("wpleads_constantcontact_configured",'');
	//email account
	update_option("wpleads_emailaccount_email",'');
	update_option("wpleads_emailaccount_last_updated",'');
	update_option("wpleads_emailaccount_configured",'');
	//SalesForce.com
	update_option("wpleads_salesforce_apiusername",'');
	update_option("wpleads_salesforce_apipassword",'');
	update_option("wpleads_salesforce_apikey",'');
	update_option("wpleads_salesforce_listid",'');
	update_option("wpleads_salesforce_last_updated",'');
	update_option("wpleads_salesforce_configured",'');
	//setup
	update_option("wpleads_setup_registration_activated","");
	update_option("wpleads_setup_registration_firstlast","");
	update_option("wpleads_setup_registration_selected","");
	update_option("wpleads_setup_registration_text","");
	update_option("wpleads_setup_commentform_activated","");
	update_option("wpleads_setup_commentform_selected","");
	update_option("wpleads_setup_commentform_text","");
	
}

//register basic activation / deactivation hooks for the MailChimp Integration
register_activation_hook(__FILE__,"wpleads_install");
register_deactivation_hook(__FILE__,"wpleads_deactivate");

//define some basic variables for the system.
define("WPLEADS_PLUGINPATH","/".plugin_basename(dirname(__FILENAME__))."/");

//bring in the Jquery
wp_enqueue_script("jquery");
?>
