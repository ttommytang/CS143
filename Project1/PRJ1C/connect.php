<?php
	//this section is for database connection
	$db = new mysqli('localhost', 'cs143', '', 'CS143');
	if($db->connect_errno > 0){
    	die('Unable to connect to database [' . $db->connect_error . ']');
	}
?>