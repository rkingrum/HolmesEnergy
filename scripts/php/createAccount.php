<?php
	ob_start();

	include "../../config/db_connect.php";
	$errors = array();
	
	if (isset($_GET['username'], $_GET['email'], $_GET['password'], $_GET['passwordConfirm'], $_GET['authCode'])) {
		$username = filter_var($_GET['username'], FILTER_SANITIZE_STRING);
		$email = filter_var(filter_var($_GET['email'], FILTER_SANITIZE_EMAIL), FILTER_VALIDATE_EMAIL);
		$password = $_GET['password'];
		$passwordConfirm = $_GET['passwordConfirm'];
		$authCode = filter_var($_GET['authCode'], FILTER_SANITIZE_STRING);
		
		if ($authCode != "1590")
			$errors[] = "The authorization code is incorrect.";
		
		if ($username == "" || $email == "" || $password == "" || $passwordConfirm == "")
			$errors[] = "You must fill out all of the fields.";
			
		if (!preg_match('/^[a-zA-Z0-9_-]{1,30}$/', $username))
			$errors[] = "Usernames can only contain letters, numbers, underscores, and dashes.  It must also be under 30 characters long.";
	
		if (strcmp($password, $passwordConfirm) != 0)
			$errors[] = "The passwords must match.";
			
		if (empty($errors)) {
			$password = filter_var(hash('sha512', $password), FILTER_SANITIZE_STRING);

			if (!filter_var($email, FILTER_VALIDATE_EMAIL))
				$errors[] = "Not a valid email.";
				
			if (strlen($password) != 128)
				$errors[] = "Password isn't valid.";
			
			$prep_stmt = "SELECT id FROM users WHERE email = ? LIMIT 1";
			if ($stmt = $mysqli->prepare($prep_stmt)) {
				$stmt->bind_param('s', $email);
				$stmt->execute();
				$stmt->store_result();
				
				if ($stmt->num_rows == 1)
					$errors[] = "A user with this email already exists.";
			}
			else {
				$errors[] = "An error was encountered while connecting to the database.";
			}
			
			if (empty($errors)) {
				$random_salt = hash('sha512', uniqid(mt_rand(1, mt_getrandmax()), true));
				$password = hash('sha512', $password.$random_salt);
				
				$query = "INSERT INTO users (username, email, password, salt)
								VALUES (?, ?, ?, ?)";
				if ($insert_stmt = $mysqli->prepare($query)) {
					$insert_stmt->bind_param("ssss", $username, $email, $password, $random_salt);
					if ($insert_stmt->execute()) {
						echo "You have successfully registered.";
						include "login.php";
					}
					else
						echo "There is an issue with the database.  Please try again later.";
				}
			}
			else
				foreach ($errors as $value)
					echo $value."<br />";
		}
		else
			foreach ($errors as $value)
				echo $value."<br />";
	}
	
	ob_end_flush();
?>