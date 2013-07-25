<?
class WPLeadsSalesForceView{
	/**
     * Displays the administrator's SalesForce Integration configuration form to retrieve the SalesForce API Key
     *
     * @param array $messages if provided, delivers status updates to the user, as they occur.
     *
     */
	function configureSalesforce($messages=null){
		//define the current key.  If current key is now invalid, reset to null and display error
		$check=WPLeadsSalesForceController::get_valid_salesforce_key();
		$currentCredentials=WPLeadsSalesForceController::get_wp_settings();
		$lists=WPLeadsSalesForceController::get_lists();
		if(WPLeadsInterface::isError($check)) { 
			$messages["errors"]=array("Your SalesForce.com API key is no longer valid.  Please enter a new API Key below.");
			$currentKey=null;
		}
		WPLeadsInterfaceView::formJquery();
		?>
		<div id="icon-link-manager" class="icon32">
			<br></div>
			<h2>SalesForce.com Configuration <a href="?page=wpleads_configuration" class="add-new-h2">Go Back</a>
			</h2>
			<?php WPLeadsInterfaceView::displayMessages($messages);?>
			<form method="post" action="?page=wpleads_configuration">
				<input type="hidden" name="configure_salesforce_post" value="true" />
				<div id="poststuff" class="metabox-holder has-right-sidebar">
					<div id="side-info-column" class="inner-sidebar">
						<div id="side-sortables" class="meta-box-sortables ui-sortable">
							<div id="linksubmitdiv" class="postbox ">
								<div class="handlediv" title="Click to toggle">
									<br></div>
									<h3 class="hndle">
										<span>Save Configuration</span>
									</h3>
									<div class="inside">
										<div class="submitbox" id="submitlink">
											<div id="major-publishing-actions">
												<div id="publishing-action">
													<img style="display: none;" src="images/wpspin_light.gif" id="wpleads_save_icon" />
													<input name="save" class="button-primary" id="wpleads_save_button" tabindex="4" accesskey="p" value="Save Configuration" type="submit"/>
												</div>
												<div class="clear"></div>
											</div>
											<div class="clear"></div>
										</div>
									</div>
								</div>
								<?php echo WPLeadsInterfaceView::helpWPLeadsPlease(); ?>
							</div>
						</div>
									
						<div id="post-body">
							<div id="post-body-content">
								<div class="stuffbox">
									<h3>
										<label for="link_name">SalesForce.com API Credentials</label>
									</h3>
									<div class="inside">
										<table width="100%">
											<tr><td style="width:30%">Username:</td><td style="width:70%"> <input name="salesforce_apiusername" size="50" tabindex="1" value="<?php echo $currentCredentials["apiusername"]; ?>" type="text" /></td></tr>
											<tr><td style="width:30%">Password:</td><td style="width:70%"> <input name="salesforce_apipassword" size="50" tabindex="2" value="<?php echo $currentCredentials["apipassword"]; ?>" type="password"/></td></tr>
											<tr><td style="width:30%">SalesForce Token:</td><td style="width:70%"> <input name="salesforce_api_key" size="50" tabindex="3" value="<?php echo $currentCredentials["apikey"]; ?>" type="text" /></td></tr>
										</table>
										<p>If you do not know how to generate your SalesForce API Token, <a href="http://www.salesforce.com/us/developer/docs/api/Content/sforce_api_concepts_security.htm" target="_blank">click here for more information</a>.</p>
									</div>
								</div>
												
								<div class="stuffbox">
									<h3>
										<label for="link_url">Additional SalesForce.com Options </label>
									</h3>
													
									<div class="inside">
										<?php
										if($lists!=""){
											echo $lists;
										}else{
											echo "<p>You must set your API Credentials before you can configure this option.</p>";
										}
										?>
									</div>
								</div>							
							</div>
						</div>
					</div>						
				</form>
<?
	}
}
