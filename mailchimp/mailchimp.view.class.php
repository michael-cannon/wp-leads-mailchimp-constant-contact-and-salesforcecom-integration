<?
class WPLeadsMailChimpView{
	/**
     * Displays the administrator's MailChimp Integration configuration form to retrieve the MailChimp API Key
     *
     * @param array $messages if provided, delivers status updates to the user, as they occur.
     *
     */
	function configureMailchimp($messages=null){
		//define the current key.  If current key is now invalid, reset to null and display error
		$currentKey=WPLeadsMailChimpController::get_valid_mailchimp_key();
		$lists=WPLeadsMailChimpController::get_lists();
		if(WPLeadsInterface::isError($currentKey)) { 
			$messages["errors"]=array("Your MailChimp API key is no longer valid.  Please enter a new API Key below.");
			$currentKey=null;
		}
		WPLeadsInterfaceView::formJquery();
		?>
		<div id="icon-link-manager" class="icon32">
			<br></div>
			<h2>MailChimp Configuration <a href="?page=wpleads_configuration" class="add-new-h2">Go Back</a>
			</h2>
			<?php WPLeadsInterfaceView::displayMessages($messages);?>
			<form method="post" action="?page=wpleads_configuration">
				<input type="hidden" name="configure_mailchimp_post" value="true" />
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
										<label for="link_name">MailChimp API Key</label>
									</h3>
									<div class="inside">
										<input name="mailchimp_api_key" size="50" tabindex="1" value="<?php echo $currentKey; ?>" id="link_name" type="text"/>
										<p>Note: If you do not already have a MailChimp API Key, <a href="http://kb.mailchimp.com/article/where-can-i-find-my-api-key/" target="_blank">follow these instructions to create one.</a></p>
									</div>
								</div>
												
								<div class="stuffbox">
									<h3>
										<label for="link_url">MailChimp List Selection</label>
									</h3>
													
									<div class="inside">
										<?php
										if($lists!=""){
											echo $lists;
										}else{
											echo "<p>You must set your API Key before you can configure this option.</p>";
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
