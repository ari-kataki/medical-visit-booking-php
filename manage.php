<?php
require_once 'DbConnector.php';
$config = require 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SESSION['role'] !== 'MEDICO') {
    header('Location: dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!array_key_exists('csrf_token', $_POST) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        exit('CSRF token non valido.');
    }

    if (array_key_exists('appointment_id', $_POST) && array_key_exists('action', $_POST)) {
        $appointmentId = (int) $_POST['appointment_id'];
        $action = $_POST['action'];
        $doctorId = $_SESSION['user_id'];
        $allowed = ['CONFIRMED', 'REFUSED', 'DONE'];

        if (in_array($action, $allowed)) {
            $connection = new DbConnector($config['host'], $config['user'], $config['pass'], $config['name']);

            $statement = $connection->prepare('UPDATE appointment
                                  SET status = :status
                                  WHERE id = :id
                                    AND doctor_id = :doctor_id
                                    AND cancelled_at IS NULL');
            $statement->bindParam(':status', $action);
            $statement->bindParam(':id', $appointmentId);
            $statement->bindParam(':doctor_id', $doctorId);
            $statement->execute();
        }
    }
}

header('Location: dashboard.php');
exit;
