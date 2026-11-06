<?php
require_once 'DbConnector.php';
$config = require 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SESSION['role'] !== 'PAZIENTE') {
    header('Location: dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!array_key_exists('csrf_token', $_POST) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        exit('CSRF token non valido.');
    }

    if (array_key_exists('appointment_id', $_POST)) {
        $appointmentId = (int) $_POST['appointment_id'];
        $patientId = $_SESSION['user_id'];

        $connection = new DbConnector($config['host'], $config['user'], $config['pass'], $config['name']);

        
        $statement = $connection->prepare('UPDATE appointment
                              SET cancelled_at = NOW()
                              WHERE id = :id
                                AND patient_id = :patient_id
                                AND cancelled_at IS NULL');
        $statement->bindParam(':id', $appointmentId);
        $statement->bindParam(':patient_id', $patientId);
        $statement->execute();
    }
}

header('Location: dashboard.php');
exit;
