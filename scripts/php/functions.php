<?php
	function sec_session_start() {
		$session_name = 'login_session';
		$secure = false;
		$httponly = true;
		
		ini_set('session.use_only_cookies', 1);
		$cookieParams = session_get_cookie_params();
		session_set_cookie_params(
			$cookieParams["lifetime"], 
			$cookieParams["path"],
			$cookieParams["domain"],
			$secure,
			$httponly);
		session_start();
		session_regenerate_id();
	}
	
	function login($username, $password, $mysqli) {
		$query = "SELECT id, 
					email, 
					password, 
					salt 
				FROM users
				WHERE username = ?
				LIMIT 1";
		if ($stmt = $mysqli->prepare($query)) {
			$stmt->bind_param('s', $username);
			$stmt->execute();
			$stmt->store_result();
			$stmt->bind_result($user_id, $email, $db_password, $salt);
			$stmt->fetch();
			$password = hash('sha512', $password.$salt);
			
			if($stmt->num_rows == 1) {
				if(checkbrute($user_id, $mysqli) == true) {
					echo "Please wait 1 hour before logging in again!";
					return false;
				}
				else {
					if ($db_password == $password) {
						$user_browser = $_SERVER['HTTP_USER_AGENT'];
						$user_id = preg_replace("/[^0-9]+/", "", $user_id);
						$_SESSION['user_id'] = $user_id;
						$username = preg_replace("/[^a-zA-Z0-9_\-]+/", "", $username);
						$_SESSION['username'] = $username;
						$_SESSION['login_string'] = hash('sha512', $password.$user_browser);
						return true;
					}
					else {
						$now = time();
						$query = "INSERT INTO login_attempts (user_id, time)
									VALUES ('$user_id', '$now')";
						$mysqli->query($query);
						echo "Password is incorrect.";
						return false;
					}
				}
			}
			else {
				return false;
			}
		}
	}
	
	function checkbrute($user_id, $mysqli) {
		$now = time();
		$valid_attempts = $now - (60);
		$query = "SELECT time
					FROM login_attempts
					WHERE user_id = ?
						AND time > '$valid_attempts'";
		
		if ($stmt = $mysqli->prepare($query)) {
			$stmt->bind_param('i', $user_id);
			$stmt->execute();
			$stmt->store_result();
			if ($stmt->num_rows > 5) {
				return true;
			}
			else {
				return false;
			}
		}
	}
	
	function login_check($mysqli) {
		if (session_status() == PHP_SESSION_NONE)
			echo "NO SESSION.";
		if(isset($_SESSION['user_id'], $_SESSION['username'], $_SESSION['login_string'])) {
			$user_id = $_SESSION['user_id'];
			$login_string = $_SESSION['login_string'];
			$username = $_SESSION['username'];
			$user_browser = $_SERVER['HTTP_USER_AGENT'];
			$query = "SELECT password 
						FROM users 
						WHERE id = ? 
						LIMIT 1";

			if ($stmt = $mysqli->prepare($query)) {
				$stmt->bind_param('i', $user_id);
				$stmt->execute();
				$stmt->store_result();

				if ($stmt->num_rows == 1) {
					$stmt->bind_result($password);
					$stmt->fetch();
					$login_check = hash('sha512', $password.$user_browser);
					if($login_check == $login_string)
						return true;
					else
						return false;
				}
				else
					return false;
			}
			else
				return false;
		}
		else
			return false;
	}					
?>