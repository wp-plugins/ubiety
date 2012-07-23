<?php
require_once 'ubiety_config.php';
@header("Cache-Control: no-cache, must-revalidate");
@header("Pragma: no-cache");
@header("Content-Type: text/css");
?>
#ubiety-bar {
	font-size: 10pt;
	position:fixed;
	left:0px;
	bottom:0px;
	z-index:100000;
	width:100%;
	background-color:<?php echo (get_option('ubiety_bottom_color')!=''?get_option('ubiety_bottom_color'):"#c0c0c0");?>;
	color:#000;
	text-align:left;
	padding:0px;
	margin:0px;
	border:1px solid black;
}
#ubiety-bar div {
	margin-left:10px;
	margin-right:10px;
}
#ubiety-bar a {
	color:#000;
	text-decoration:none;
}
#ubiety-window {
	font-size: 10pt;
	z-index:100000;
}
.tableheader {
	background-color:<?php echo (get_option('ubiety_header_color')!=''?get_option('ubiety_header_color'):"#2a4480");?>;
	font-weight:bold;
	text-align:center;
	color:#fff;
}
.tabledata {
	background-color:#f0f0f0;
	text-align:left;
	color:#000;
}
#ubiety-messages {
	height:250px;
	overflow-y:auto;
	width:300px;
}
#online-count {
	padding:0px;
	margin:0px;
	display:inline;
}
#online-list ul {
	list-style-type: none;
	text-align: left;
}
#online-list {
	width:100%;
	height:250px;
	overflow-y:auto;
}
#online {
	width:450px;
}
#online_tbl td {
	padding:5px;
}
#ubiety-window {
	position:fixed;
	width:450px;
	background-color:#000;
	text-align:left;
}
#ubiety-window-table td {
	padding: 3px !important;
}
