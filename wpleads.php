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
require_once 'mailchimp/MCAPI.class.php';
require_once 'mailchimp/mailchimp.controller.class.php';
require_once 'mailchimp/mailchimp.view.class.php';
//constant contact configuration
require_once 'constantcontact/cc_class.php';
require_once 'constantcontact/constantcontact.controller.class.php';
require_once 'constantcontact/constantcontact.view.class.php';
//Email Account Configuration
require_once 'emailaccount/emailaccount.controller.class.php';
require_once 'emailaccount/emailaccount.view.class.php';
//SalesForce.com
require_once 'salesforce/soapclient/SforceEnterpriseClient.php';
require_once 'salesforce/salesforce.controller.class.php';
require_once 'salesforce/salesforce.view.class.php';
//general interface configuration
require_once 'interface/interface.class.php';
require_once 'interface/interface.view.class.php';
require_once 'interface/interface.plugin.php';
//comments integration configuration
require_once 'comments/comments.controller.class.php';
require_once 'comments/comments.view.class.php';

function wpleads_install() {
	//run install routines, setup basic Integration variables within the options environment.
	add_option('wplead_active', 'true', '', 'yes');
	add_option('wplead_settings', '', '', 'yes');
	//mailchimp variables
	add_option('wpleads_mailchimp_apikey', '', '', 'yes');
	add_option('wpleads_mailchimp_listid', '', '', 'yes');
	add_option('wpleads_mailchimp_last_updated', array('last_updated'=>date('m/d/Y h:i:s')), '', 'yes');
	add_option('wpleads_mailchimp_configured', array('configured'=>0), '', 'yes');
	//constant contact variables
	add_option('wpleads_constantcontact_apiusername', '', '', 'yes');
	add_option('wpleads_constantcontact_apipassword', '', '', 'yes');
	add_option('wpleads_constantcontact_apikey', '', '', 'yes');
	add_option('wpleads_constantcontact_listid', '', '', 'yes');
	add_option('wpleads_constantcontact_last_updated', array('last_updated'=>date('m/d/Y h:i:s')), '', 'yes');
	add_option('wpleads_constantcontact_configured', array('configured'=>0), '', 'yes');
	//email address variables
	add_option('wpleads_emailaccount_email', '', '', 'yes');
	add_option('wpleads_emailaccount_last_updated', array('last_updated'=>date('m/d/Y h:i:s')), '', 'yes');
	add_option('wpleads_emailaccount_configured', array('configured'=>0), '', 'yes');
	//Salesforce.com
	add_option('wpleads_salesforce_apiusername', '', '', 'yes');
	add_option('wpleads_salesforce_apipassword', '', '', 'yes');
	add_option('wpleads_salesforce_apikey', '', '', 'yes');
	add_option('wpleads_salesforce_listid', '', '', 'yes');
	add_option('wpleads_salesforce_last_updated', array('last_updated'=>date('m/d/Y h:i:s')), '', 'yes');
	add_option('wpleads_salesforce_configured', array('configured'=>0), '', 'yes');
	//setup
	add_option('wpleads_setup_registration_activated', array('activated'=>0), '', 'yes');
	add_option('wpleads_setup_registration_firstlast', array('firstlast'=>1), '', 'yes');
	add_option('wpleads_setup_registration_text', array('text'=>'Join our Mailing List?'), '', 'yes');
	add_option('wpleads_setup_registration_selected', array('selected'=>1), '', 'yes');
	add_option('wpleads_setup_commentform_activated', array('comment_activated'=>0), '', 'yes');
	add_option('wpleads_setup_commentform_text', array('comment_text'=>'Join our Mailing List?'), '', 'yes');
	add_option('wpleads_setup_commentform_selected', array('comment_selected'=>1), '', 'yes');
}


function wpleads_uninstall() {
	$settings = WPLeadsInterface::get_wp_settings();
	if ( empty( $settings['delete_data'] ) )
		return;

	delete_option('wplead_active'); //set the activation flag to false
	delete_option('wplead_settings'); //reset the API key to null.
	
	// mailchimp
	delete_option('wpleads_mailchimp_apikey');
	delete_option('wpleads_mailchimp_listid');
	delete_option('wpleads_mailchimp_last_updated');
	delete_option('wpleads_mailchimp_configured');

	// constant contact variables
	delete_option('wpleads_constantcontact_apiusername');
	delete_option('wpleads_constantcontact_apipassword');
	delete_option('wpleads_constantcontact_apikey');
	delete_option('wpleads_constantcontact_listid');
	delete_option('wpleads_constantcontact_last_updated');
	delete_option('wpleads_constantcontact_configured');

	// email account
	delete_option('wpleads_emailaccount_email');
	delete_option('wpleads_emailaccount_last_updated');
	delete_option('wpleads_emailaccount_configured');

	// SalesForce.com
	delete_option('wpleads_salesforce_apiusername');
	delete_option('wpleads_salesforce_apipassword');
	delete_option('wpleads_salesforce_apikey');
	delete_option('wpleads_salesforce_listid');
	delete_option('wpleads_salesforce_last_updated');
	delete_option('wpleads_salesforce_configured');

	// setup
	delete_option('wpleads_setup_registration_activated');
	delete_option('wpleads_setup_registration_firstlast');
	delete_option('wpleads_setup_registration_selected');
	delete_option('wpleads_setup_registration_text');
	delete_option('wpleads_setup_commentform_activated');
	delete_option('wpleads_setup_commentform_selected');
	delete_option('wpleads_setup_commentform_text');
	delete_option('wpleads_setup_delete_data_activated');
}


//register basic activation / uninstall hooks
register_activation_hook(__FILE__, 'wpleads_install');
register_uninstall_hook(__FILE__, 'wpleads_uninstall');

//define some basic variables for the system.
define('WPLEADS_PLUGINPATH', '/'.plugin_basename(dirname(__FILE__)).'/');

//bring in the Jquery
add_action( 'wp_enqueue_scripts', 'wpleads_enqueue_script' );
function wpleads_enqueue_script() {
	wp_enqueue_script('jquery');
}


?>
