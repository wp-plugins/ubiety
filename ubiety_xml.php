<?php
require_once('ubiety_config.php');
require_once('lib/Pusher.php');
@header("Cache-Control: no-cache, must-revalidate");
@header("Pragma: no-cache");
@header("Content-Type: application/json");
if(!session_id()) { @session_start(); }
global $current_user;
get_currentuserinfo();
$return = Array('success' => false);
$pusher = new Pusher(get_option('ubiety_app_key'), get_option('ubiety_app_secret'), get_option('ubiety_app_id'));
try {
	switch($_GET['action']) {
		case "pusherauth":
			if(!is_user_logged_in()) {
				throw new Exception("User not logged in");
			}
			//deep copy hack
			$user_data = unserialize(serialize($current_user->data));
			//don't need password or login in our user info.
			unset($user_data->user_pass);
			unset($user_data->user_login);
			echo $pusher->presence_auth($_POST['channel_name'], $_POST['socket_id'], $user_data->ID, $user_data);
			exit;
			break;
		case "sendmsg":
			$msg = !empty($_POST['msg']) ? strip_tags($_POST['msg']) : null;
			$socket_id = !empty($_POST['socket_id']) ? $_POST['socket_id'] : null;
			$userid = !empty($_POST['userid']) ? $_POST['userid'] : null;
			if(!$msg) {
				throw new Exception("Can't have empty message");
			}
			if(!$socket_id) {
				throw new Exception("No socket id supplied");
			}
			if(!$userid) {
				throw new Exception("No user id supplied");
			}
			$data = Array('userid' => $userid, 'msg' => $msg);
			$pusher->trigger('presence-ubiety', 'message', $data, $socket_id);
			$return['success'] = true;
		default:
	}
} catch(Exception $e) {
	$return['msg'] = $e->getMessage();
}
echo json_encode($return);
?>
