<?php
if(!file_exists('../../../wp-config.php') || !file_exists('../../../wp-includes/wp-db.php')) {
	if(!file_exists('wp-config.php') || !file_exists('wp-includes/wp-db.php')) {
		die("Could not find your wp-config.php, did you install under `wp-content/plugins/ubiety`?");
	}
	else {
		include_once('wp-config.php');
		include_once('wp-includes/wp-db.php');
	}
}
else {
	include_once('../../../wp-config.php');
	include_once('../../../wp-includes/wp-db.php');
}
?>
