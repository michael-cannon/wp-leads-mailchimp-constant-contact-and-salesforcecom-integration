<?php
class WPLeadsCommentsView{
	function commentForm(){
		$form=WPLeadsInterface::get_wp_settings();
		$checked=($form["comment_selected"])?" checked":"";
		?>
		<p style="clear: both;">
		<label style="display: block;"><?php echo $form["comment_text"]; ?> 
		<input style="width: 10px; margin-left: 5px;" type="checkbox" name="joinlist" tabindex="26" <?php echo $checked; ?> />
		</label>
		</p>
		<?
	}
}
?>
