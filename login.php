<?php
require_once 'DbConnector.php';
$config = require 'config.php';
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

if (!isset($_SESSION['login_csrf_token'])) {
    $_SESSION['login_csrf_token'] = bin2hex(random_bytes(32));
}

$loginError = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!array_key_exists('csrf_token', $_POST) || $_POST['csrf_token'] !== $_SESSION['login_csrf_token']) {
        exit('CSRF token non valido.');
    }

    $connection = new DbConnector($config['host'], $config['user'], $config['pass'], $config['name']);
    $statement = $connection->prepare('SELECT * 
                                        FROM user 
                                        WHERE username = :username');
    $statement->bindParam(':username', $_POST['username']);
    $statement->execute();
    $user = $statement->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($_POST['password'], $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        header('Location: dashboard.php');
        exit();
    } else {
        $loginError = true;
    }
}
?>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Login - By Ali</title>
        <link rel="stylesheet" href="login.css" />
    </head>
    <body>
        <form action="" method="post">
            <h2>Login</h2>
            <div>
                <label>Username</label>
                <input type="text" name="username" value="<?= array_key_exists('username', $_POST) ? htmlspecialchars($_POST['username']) : '' ?>" required />
            </div>
            <div>
                <label>Password</label>
                <input type="password" name="password" required />
            </div>
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['login_csrf_token'] ?>" />
            <button type="submit">Accedi</button>
            <?php if ($loginError) { ?>
                <div style="color: #c41e3a; background-color: #ffe5e5; padding: 12px; border-left: 4px solid #c41e3a; margin-top: 15px; border-radius: 4px;">Username o password errati.</div>
            <?php } ?>
            <div style="text-align: center; margin-top: 20px;">
                <a href="register.php"><button type="button">Registrati</button></a>
            </div>
        </form>
    </body>
</html>
