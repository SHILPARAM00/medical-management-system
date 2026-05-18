<?php
session_start();

/* CLEAR SESSION VARIABLES */
$_SESSION = [];

/* DESTROY SESSION */
session_destroy();

/* REMOVE SESSION COOKIE (BEST PRACTICE) */
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 3600,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

/* REDIRECT TO LOGIN PAGE */
header("Location: login.php");
exit;
?>