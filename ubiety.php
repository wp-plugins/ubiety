<?php
/*
Plugin Name: Ubiety
Plugin URI: http://paydensutherland.com
Description: This plugin provides instant messaging between two or more parties viewing your blog.
Version: 0.0.1
Author: Payden Sutherland
Author URI: http://paydensutherland.com
License: GPL2


  Copyright 2012  Payden Sutherland  (email: payden@paydensutherland.com)

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

require_once('ubiety_options.php');
ob_start(); //output buffering to buffer any headers
define('UBIETY_VERSION','0.0.1');
define('UBIETY_PATH',dirname(__FILE__));
register_activation_hook(__FILE__,'activate_me');
register_deactivation_hook(__FILE__,'deactivate_me');
add_action('init','ubiety_script');
add_action('wp_print_styles','ubiety_style');
add_action('wp_login','ubiety_wp_login');
add_filter('wp_print_footer_scripts','ubiety_footer');

global $wpdb;

if(!function_exists('ubiety_style')) {
	function ubiety_style() {
		wp_register_style('ubiety_style',plugins_url('/ubiety.css.php',__FILE__));
		wp_enqueue_style('ubiety_style');
	}
}
if(!function_exists('ubiety_script')) {
	function ubiety_script() {
		if(preg_match('/^\/wp-admin\//',$_SERVER['PHP_SELF']) || preg_match('/^\/wp-login/',$_SERVER['PHP_SELF'])) {
			return;
		}
		wp_register_script('swfobject',plugins_url('web-socket-js/swfobject.js', __FILE__));
		wp_register_script('web_socket',plugins_url('web-socket-js/web_socket.js', __FILE__));
		wp_register_script('ubiety_script',plugins_url('ubiety.js.php',__FILE__),array('jquery'));
		wp_register_script('jquery-ui','https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.15/jquery-ui.min.js',array('jquery'));
		wp_enqueue_script('swfobject');
		wp_enqueue_script('web_socket');
		wp_enqueue_script('ubiety_script');
		wp_enqueue_script('jquery-ui');
	}
}
if(!function_exists('ubiety_wp_login')) {
	function ubiety_wp_login($user_name) {
		if(!($data=get_userdatabylogin($user_name))) { return; }
		//login hook
	}
}
if(!function_exists('ubiety_footer')) {
	function ubiety_footer() {
		$user=get_currentuserinfo();
		if(preg_match('/^\/wp-admin\//',$_SERVER['PHP_SELF']) || preg_match('/^\/wp-login/',$_SERVER['PHP_SELF'])) {
			return;
		}
		echo "<div id='ubiety-bar'>\n";
		echo "<div style='float:left;font-weight:bold;'>Ubiety v".get_option('ubiety_version')."</div>\n";
		echo "<div style='float:right;font-weight:bold;'><a href='javascript:void(0);' id='open-online'>Chat&nbsp;(<span id='online-count'></span>)</a></div>\n";
		echo "</div>\n";
		//Do main ubiety window
		?>
		<div id='ubiety-window' style='display:none;'>
			<table id='ubiety-window-table' align='center' width='100%' cellpadding='3' cellspacing='1' style='background-color:#000;border-collapse:separate;''>
			<tr id='ubiety-window-title' class='tableheader'>
				<td colspan='2'><div style='display:inline-table;float:left;'>Ubiety v<?php echo get_option('ubiety_version');?></div><div style='display:inline-table;float:right;'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href='javascript:void(0);' id='closeX'>X</a></div></td>
			</tr>
			<tr class='tabledata'>
				<td><input type="text" id="change_name" size="15"/>&nbsp;<input type="button" id="change_name_btn" value="Change Name" style="float:right;"/><td rowspan='2' style='width:25%;text-align:center;vertical-align:top;border-left:1px solid black'><strong>Online</strong><div id='online-list'></div></td>
			</tr>
			<tr class='tabledata'>
				<td>
					<div id='ubiety-messages'>
					</div>
				</td>
			</tr>
			<tr class='tabledata'>
				<td colspan='2'><input type='text' id='msg' style='width:98%;'/></td>
			</tr>
			</table>
		</div>
		<?php
	}
}
if(!function_exists('activate_me')) {
	function activate_me() {
		global $wpdb;
		
		if(get_option('ubiety_version')=='') {
			add_option('ubiety_version',UBIETY_VERSION);
		} else if(get_option('ubiety_version')!=UBIETY_VERSION) {
			update_option('ubiety_version',UBIETY_VERSION); $upgrade=true;
		}
		
		if(get_option('ubiety_bottom_color')=='') {
			add_option('ubiety_bottom_color','#c0c0c0');
		}
		
		if(get_option('ubiety_header_color')=='') {
			add_option('ubiety_header_color','#2a4480');
		}
		
		if(get_option('ubiety_app_id') == '') {
			add_option('ubiety_app_id', 'APP_ID');
		}
		
		if(get_option('ubiety_app_key') == '') {
			add_option('ubiety_app_key', 'APP_KEY');
		}
		
		if(get_option('ubiety_app_secret') == '') {
			add_option('ubiety_app_secret', 'APP_SECRET');
		}
		return true;
	}
}
if(!function_exists('deactivate_me')) {
	function deactivate_me() {
		//nothing yet.
	}
}
?>
