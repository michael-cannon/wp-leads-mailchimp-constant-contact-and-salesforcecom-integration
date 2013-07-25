<?
class WPLeadsEmailAccountView{
	/**
     * Displays the administrator's EmailAccount Integration configuration form to retrieve the EmailAccount API Key
     *
     * @param array $messages if provided, delivers status updates to the user, as they occur.
     *
     */
	function configureEmailaccount($messages=null){
		//define the current key.  If current key is now invalid, reset to null and display error
		$currentCredentials=WPLeadsEmailAccountController::get_wp_settings();
		WPLeadsInterfaceView::formJquery();
		?>
		<div id="icon-link-manager" class="icon32">
			<br></div>
			<h2>Email Account Configuration <a href="?page=wpleads_configuration" class="add-new-h2">Go Back</a>
			</h2>
			<?php WPLeadsInterfaceView::displayMessages($messages);?>
			<form method="post" action="?page=wpleads_configuration">
				<input type="hidden" name="configure_emailaccount_post" value="true" />
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
							</div>
						</div>
									
						<div id="post-body">
							<div id="post-body-content">
								<div class="stuffbox">
									<h3>
										<label for="link_name">Enter your Email Address</label>
									</h3>
									<div class="inside">
										<input name="emailaccount_email" size="50" tabindex="1" value="<?php echo $currentCredentials["email"]; ?>" type="text" />
										<p>Help: To receive an email alert every time your contact form is submitted, enter a valid email address above.</p>
									</div>
								</div>					
							</div>
						</div>
					</div>						
				</form>
<?
	}
}
