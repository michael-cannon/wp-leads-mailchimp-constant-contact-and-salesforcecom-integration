<?php
class WPLeadsInterfaceView{
	function head(){
		?>
		<div style="margin-top: 15px;">
			<div class="wrap">
			<?
	}
	function foot(){
		?>
			</div>
		</div>
		<?
	}
	function formJquery(){
		?>
				<script type="text/javascript">
				$wpleads = jQuery.noConflict();
				$wpleads(document).ready(function(){
						$wpleads("#message").slideDown(1000);
						$wpleads("#wpleads_save_button").click(function(){
								$wpleads("#wpleads_save_icon").show();
						});
				});
				</script>
		<?
	}
	function configurationTable(){
		$mailchimp=WPLeadsMailChimpController::get_wp_settings();
		$constantcontact=WPLeadsConstantContactController::get_wp_settings();
		$email=WPLeadsEmailAccountController::get_wp_settings();
		$salesforce=WPLeadsSalesForceController::get_wp_settings();
		?>
		<div id="icon-options-general" class="icon32">
			<br></div>
		<h2>WP Leads Integration Options</h2>
		<p>Remember: you can use one or all of the systems below.</p>
		
		<table class="widefat">
			<thead>
				<tr>
					<th>Integration Type</th>
					<th>Setup?</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><a href="<?php echo $_SERVER["REQUEST_URI"];?>&configure_constantcontact=true">Constant Contact</a></td>
					<td><?php echo WPLeadsInterface::getConfiguredText($constantcontact["configured"]); ?></td>
				</tr>
				<tr>
					<td><a href="<?php echo $_SERVER['REQUEST_URI'];?>&configure_mailchimp=true">MailChimp</a></td>
					<td><?php echo WPLeadsInterface::getConfiguredText($mailchimp["configured"]); ?></td>
				</tr>
				<tr>
					<td><a href="<?php echo $_SERVER['REQUEST_URI'];?>&configure_salesforce=true">SalesForce.com</a></td>
					<td><?php echo WPLeadsInterface::getConfiguredText($salesforce["configured"]); ?></td>
				</tr>
			</tbody>
		</table>
		<?
	}
	function wpleadsSetup($messages=null){
		$setup=WPLeadsInterface::get_wp_settings();
		WPLeadsInterfaceView::formJquery();
		?>
		<div id="icon-options-general" class="icon32">
			<br></div>
			<h2>WP Leads Setup <a href="?page=wpleads_configuration" class="add-new-h2">Go Back</a></h2>
			<?php WPLeadsInterfaceView::displayMessages($messages);?>
			<form method="post" action="?page=wpleads_setup">
				<input type="hidden" name="wpleads_setup_post" value="true" />
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
										<label for="link_name">Registration Form Setup</label>
									</h3>
									<div class="inside">
										<table width="100%">
											<tr><td style="width: 30%">Join Our Mailing List Text:</td><td style="width: 70%"><input name="text" size="50" tabindex="1" value="<?php echo $setup["text"]; ?>" type="text" /></td></tr>
											<tr><td style="width: 30%">Default to Yes?:</td><td style="width: 70%"><select name="selected"><option value="1">Yes</option><option value="0"<?if(!$setup["selected"]) { echo " selected";}?>>No</option></select></td></tr>
											<tr><td style="width: 30%">Include first and last name?:</td><td style="width: 70%"><select name="firstlast"><option value="1">Yes</option><option value="0"<?if(!$setup["firstlast"]) { echo " selected";}?>>No</option></select></td></tr>
											<tr><td style="width: 30%">Activate:</td><td style="width: 70%"><select name="activated"><option value="1">Yes</option><option value="0"<?if(!$setup["activated"]) { echo " selected";}?>>No</option></select></td></tr>
										</table>
									</div>
								</div>
								
								<div class="stuffbox">
									<h3>
										<label for="link_name">Comment Form Setup</label>
									</h3>
									<div class="inside">
										<table width="100%">
											<tr><td style="width: 30%">Join Our Mailing List Text:</td><td style="width: 70%"><input name="comment_text" size="50" tabindex="1" value="<?php echo $setup["comment_text"]; ?>" type="text" /></td></tr>
											<tr><td style="width: 30%">Default to Yes?:</td><td style="width: 70%"><select name="comment_selected"><option value="1">Yes</option><option value="0"<?if(!$setup["comment_selected"]) { echo " selected";}?>>No</option></select></td></tr>
											<tr><td style="width: 30%">Activate:</td><td style="width: 70%"><select name="comment_activated"><option value="1">Yes</option><option value="0"<?if(!$setup["comment_activated"]) { echo " selected";}?>>No</option></select></td></tr>
										</table>
									</div>
								</div>
												
								<div class="stuffbox">
									<h3>
										<label for="link_url">Helpful Information</label>
									</h3>
													
									<div class="inside">
										<h4>Join Our Mailing List Text</h4> <p>This is used to ask whether the user would like to join your mailing list</p>
										<h4>Default to Yes</h4> <p>Set the default response to 'Yes'. </p>
										<h4>Include first and last name?</h4> <p>Select Yes to retrieve the user's first and last name during the registration process.</p>
										<h4>Activate:</h4> <p>By activating WP-Leads on the Registration Form - users will be asked to join your mailing list during the WordPress registration process found at (wp-login.php?action=register).  By activating WP-Leads on the Comments Form - users will be asked to join as they are commenting on your site. The user's email address is seamlessly sent to one or all of the following Lead Management Systems (MailChimp, Constant Contact, or SalesForce.com).  You can configure these integrations by <br /><a href="?page=wpleads_configuration">clicking here.</a></p> 
									</div>
								</div>							
							</div>
						</div>
					</div>						
				</form>
	<?
	}
	function registrationForm(){
		$post=WPLeadsInterface::sanitizeData($_REQUEST);
		$firstname=(isset($post["firstname"]))?$post["firstname"]:"";
		$lastname=(isset($post["lastname"]))?$post["lastname"]:"";
		$form=WPLeadsInterface::get_wp_settings();
		$checked=($form["selected"])?" checked":"";
		if($form["firstlast"]){
			$html='
						<div width="100%">
							<p>
								<label style="display: block; margin-bottom: 5px;"> First Name 
								<input type="text" name="first_name" class="input" tabindex="24" value="'.$firstname.'" />
								</label>
							</p>
						</div>
						<div width="100%">
							<p>
								<label style="display: block; margin-bottom: 5px;"> Last Name 
								<input type="text" name="last_name" class="input" tabindex="25" value="'.$lastname.'" />
								</label>
							</p>
						</div>
					';
		}
		$html .= '
						<div width="100%" style="padding-bottom: 10px;">
							<p>
								<label style="display: block; margin-bottom: 5px;">'.$form["text"].' 
								<input type="checkbox" name="joinlist" class="checkbox" tabindex="26"'.$checked.' />
								</label>
							</p>
						</div>
		';
		echo $html;
	}
	function helpWPLeadsPlease(){
		?>
		<iframe src="http://www.wpleads.com/sponsor/sponsor.html" style="width: 100%; height: 500px; padding: 0px; margin: 0px; border: 0px;">
		<div style="width: 100%">
			<h4>Sponsored Services</h4>
			<a href="http://secure.hostgator.com/~affiliat/cgi-bin/affiliates/clickthru.cgi?id=occamrazor" target="_blank"><?php echo '<img src="' .plugins_url( 'images/hostgator.gif' , dirname(__FILE__) ). '" > ';?></a><a href="http://themeforest.net/category/wordpress?ref=think144" target="_blank"><?php echo '<img src="' .plugins_url( 'images/themeforest.gif' , dirname(__FILE__) ). '" > ';?></a>
		</div>
		</iframe>
		<?
	}
	/*************************************************************
	*All error functionality below is used to facilitate system
	*messages as they occur - reporting success and error messages.
	**************************************************************/
	function displayMessages($messages){
		WPLeadsInterfaceView::displaySuccess($messages["successes"]);
		WPLeadsInterfaceView::displayErrors($messages["errors"]);
	}
	function displayErrors($errors){
		if(is_array($errors) && !empty($errors)){
			echo "<div id=\"message\" class=\"updated below-h2\" style=\"display: none\">";
			foreach($errors as $error){
				echo "<p><span class='error'>$error</span></p>";
			}
			echo "</div>";
		}
	}
	function displaySuccess($successes){
		if(is_array($successes) && !empty($successes)){
			echo "<div id=\"message\" class=\"updated below-h2\" style=\"display: none\">";
			foreach($successes as $success){
				echo "<p><span class='success'>$success</span></p>";
			}
			echo "</div>";
		}elseif(!empty($successes)){
			echo "<div id=\"message\" class=\"updated below-h2\" style=\"display: none\">";
			echo "<p><span class='success'>$successes</span></p>";
			echo "</div>";
		}
	}
	function errorClass($messages,$field,$justClassName=false){
		if(is_array($messages)){
			if(!empty($messages["errorFields"])){
				if(in_array($field,$messages["errorFields"])){
					if(!$justClassName){
						print_r(" class=\"error\"");
					}else{
						print_r(" error");
					}
				}
			}
		}
	}
}
