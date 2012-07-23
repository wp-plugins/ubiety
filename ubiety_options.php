<?php
if(is_admin()) {
	add_action('admin_menu','ubiety_add_menu');
	add_action('admin_init','ubiety_regset');
}
function ubiety_regset() {
	register_setting('ubiety','ubiety_bottom_color');
	register_setting('ubiety','ubiety_header_color');
	register_setting('ubiety', 'ubiety_app_id');
	register_setting('ubiety', 'ubiety_app_key');
	register_setting('ubiety', 'ubiety_app_secret');
}
function ubiety_add_menu() {
	add_plugins_page('Ubiety Configuration','Ubiety Configuration','administrator',__FILE__,'ubiety_options');
}
function ubiety_options() {
?>
<div class='wrap'>
<h2>Ubiety Configuration</h2>
<form method='post' action='options.php'>
<?php
settings_fields('ubiety');
$ubiety_bottom_color_current=get_option('ubiety_bottom_color');
$ubiety_header_color_current=get_option('ubiety_header_color');
$ubiety_app_id = get_option('ubiety_app_id');
$ubiety_app_key = get_option('ubiety_app_key');
$ubiety_app_secret = get_option('ubiety_app_secret');
?>
<div>&nbsp;</div>
<div style="font-weight:bold">Note: You must go to <a href="http://pusher.com">pusher.com</a> and register for free API account to use this plugin.  Click 'sign up' on the home page.</div>
<table class='form-table'>
<tr valign='top'>
	<th scope='row'>Pusher APP ID</th>
	<td><input name='ubiety_app_id' type='text' value='<?php echo $ubiety_app_id; ?>'/></td>
</tr>
<tr valign='top'>
	<th scope='row'>Pusher APP Key</th>
	<td><input name='ubiety_app_key' type='text' value='<?php echo $ubiety_app_key; ?>'/></td>
</tr>
<tr valign='top'>
	<th scope='row'>Pusher APP Secret</th>
	<td><input name='ubiety_app_secret' type='text' value='<?php echo $ubiety_app_secret; ?>'/></td>
</tr>
<tr valign='top'>
<th scope='row'>Ubiety Bar Color:</th>
<td><input name='ubiety_bottom_color' type='text' value='<?php echo $ubiety_bottom_color_current;?>'/></td>
</tr>
<tr valign='top'>
<th scope='row'>Ubiety Window Header Color:</th>
<td><input name='ubiety_header_color' type='text' value='<?php echo $ubiety_header_color_current;?>'/></td>
</tr>
</table>
<input type='hidden' name='action=' value='update'/>
<input type='hidden' name='options' value='ubiety_bottom_color,ubiety_header_color,ubiety_app_id,ubiety_app_key,ubiety_app_secret'>
<p class='submit'>
<input type='submit' class='button-primary' value='<?php _e('Save Changes') ?>'/>
<?php
if(isset($_GET['settings-updated'])) { echo "<div style='font-weight:bold;'>Options Saved!</div>\n"; }
?>
</p>
</form>
</div>
<?php } ?>
