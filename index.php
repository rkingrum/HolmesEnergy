<!DOCTYPE HTML>
<html>
	<head>
		<title>Holmes AutoPilot</title>
	</head>
	<body>
		<?php
			if (session_status() == PHP_SESSION_NONE)
				session_start($_COOKIE["PHPSESSID"]);
			include "config/db_connect.php";
			include "scripts/php/functions.php";
			if (login_check($mysqli) == true)
				echo "<span>".$_SESSION["username"]."</span>";
		?>
		<form name="login" action="scripts/php/login.php" method="get" >
			<input type="text" name="username" />
			<input type="password" name="password" />
			<input type="submit" value="submit" />
		</form>
		<form name="createAccount" action="scripts/php/createAccount.php" method="get" >
			<input type="text" name="username" />
			<input type="text" name="email" />
			<input type="password" name="password" />
			<input type="password" name="passwordConfirm" />
			<input type="text" name="authCode" />
			<input type="submit" value="submit" />
		</form>
		<a href="scripts/php/logout.php">Logout</a>
	</body>
</html>