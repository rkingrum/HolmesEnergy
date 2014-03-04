<?php
	ob_start();

	include "../../config/db_connect.php";
	include "../../scripts/php/functions.php";
	sec_session_start();

	if (isset($_GET['username'], $_GET['password'])) {
		$username = $_GET['username'];
		$password = hash('sha512', $_GET['password']);
		if (login($username, $password, $mysqli) == true)
			echo "Login Successful.";
		else
			echo "Invalid username/password combination.";
	}
	else
		echo "Please complete all fields.";
		
	ob_end_flush();
?>