<?php
require_once dirname(__FILE__) . '/conf.php';

session_start();

$userId = FALSE;

# Check whether a pair of user and password are valid; returns true if valid.
function areUserAndPasswordValid($user, $password) {
	global $db, $userId;

    $query = $db->prepare('SELECT userId, password FROM users WHERE username = :username');

    $query->bindValue(':username', $user, SQLITE3_TEXT);

    $result = $stmt->execute();

	$row = $result->fetchArray();

	if (password_hash($password) == $row['password'])
	{
		$userId = $row['userId'];
		$_SESSION['userId'] = $userId;
		return TRUE;
	}
	else
		return FALSE;
}

# On login
if (isset($_POST['username']) && isset($_POST['password'])) {		
	$_SESSION['user'] = $_POST['username'];
	$_SESSION['password'] = $_POST['password'];
}

# On logout
if (isset($_POST['Logout'])) {
	# Delete session information
	unset($_SESSION['user']);
	unset($_SESSION['password']);
	unset($_SESSION['userId']);

    session_destroy();
    setcookie("PHPSESSID", FALSE);

	header("Location: index.php");
}


# Check user and password
if (isset($_SESSION['user']) && isset($_SESSION['password'])) {
	if (areUserAndPasswordValid($_SESSION['user'], $_SESSION['password'])) {
		$login_ok = TRUE;
		$error = "";
	} else {
		$login_ok = FALSE;
		$error = "Invalid user or password.<br>";
	}
} else {
	$login_ok = FALSE;
	$error = "This page requires you to be logged in.<br>";
}

if ($login_ok == FALSE) {

?>
    <!doctype html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport"
              content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <link rel="stylesheet" href="css/style.css">
        <title>Práctica RA3 - Authentication page</title>
    </head>
    <body>
    <header class="auth">
        <h1>Authentication page</h1>
    </header>
    <section class="auth">
        <div class="message">
            <?= $error ?>
        </div>
        <section>
            <div>
                <h2>Login</h2>
                <form action="#" method="post">
                    <label>User</label>
                    <input type="text" name="username"><br>
                    <label>Password</label>
                    <input type="password" name="password"><br>
                    <input type="submit" value="Login">
                </form>
            </div>

            <div>
                <h2>Logout</h2>
                <form action="#" method="post">
                    <input type="submit" name="Logout" value="Logout">
            </div>
        </section>
    </section>
    <footer>
        <h4>Puesta en producción segura</h4>
        < Please <a href="http://www.donate.co?amount=100&amp;destination=ACMEScouting/"> donate</a> >
    </footer>
    <?php
    exit (0);
}

setcookie('user', $_SESSION['user']);
setcookie('password', $_SESSION['password']);


?>
