<?php
require_once 'DbConnector.php';
$config = require 'config.php';
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

if (!isset($_SESSION['register_csrf_token'])) {
    $_SESSION['register_csrf_token'] = bin2hex(random_bytes(32));
}

$username = null;
$password = null;
$password2 = null;
$registerError = false;
$passwordMismatch = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!array_key_exists('csrf_token', $_POST) || $_POST['csrf_token'] !== $_SESSION['register_csrf_token']) {
        exit('CSRF token non valido.');
    }

    if (array_key_exists('username', $_POST)) {
        $username = $_POST['username'];
    }
    if (array_key_exists('password', $_POST)) {
        $password = $_POST['password'];
    }
    if (array_key_exists('password2', $_POST)) {
        $password2 = $_POST['password2'];
    }

    if (empty($username) || empty($password) || empty($password2)) {
        $registerError = true;
    } elseif ($password !== $password2) {
        $passwordMismatch = true;
    } else {
        $connection = new DbConnector($config['host'], $config['user'], $config['pass'], $config['name']);
        $statement = $connection->prepare('SELECT * FROM user WHERE username = :username');
        $statement->bindParam(':username', $username);
        $statement->execute();
        $user = $statement->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $registerError = true;
        } else {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
            $statement = $connection->prepare('INSERT INTO user (username, password, role) VALUES (:username, :password, :role)');
            $role = 'PAZIENTE';
            $statement->bindParam(':username', $username);
            $statement->bindParam(':password', $hashedPassword);
            $statement->bindParam(':role', $role);
            $statement->execute();
            header('Location: login.php');
            exit();
        }
    }
}
?>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Registrazione Paziente - Prenotazione Visite</title>
        <link rel="stylesheet" href="register.css" />
    </head>
    <body>
        <form method="post">
            <h2>Registrazione Paziente</h2>
            <div>
                <label>Username</label>
                <input type="text" name="username" value="<?= htmlspecialchars($username ?? '') ?>" required />
            </div>
            <div>
                <label>Password</label>
                <input type="password" name="password" required />
            </div>
            <div>
                <label>Conferma Password</label>
                <input type="password" name="password2" required />
            </div>
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['register_csrf_token'] ?>" />
            <?php if ($passwordMismatch) { ?>
                <span>Le password non coincidono.</span>
            <?php } ?>
            <?php if ($registerError) { ?>
                <span>Username già in uso o campi vuoti.</span>
            <?php } ?>
            <button type="submit">Registrati</button>
            <div style="text-align: center; margin-top: 15px;">
                <a href="login.php"><button type="button">Torna al Login</button></a>
            </div>
        </form>
    </body>
</html>