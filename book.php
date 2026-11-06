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

    if (array_key_exists('doctor_id', $_POST) &&
        array_key_exists('appointment_date', $_POST) &&
        array_key_exists('appointment_time', $_POST)) {

        $doctorId = (int) $_POST['doctor_id'];
        $appointmentDate = $_POST['appointment_date'];
        $appointmentTime = $_POST['appointment_time'];
        $patientId = $_SESSION['user_id'];

        $connection = new DbConnector($config['host'], $config['user'], $config['pass'], $config['name']);

        $checkStatement = $connection->prepare('SELECT id FROM appointment
                                WHERE doctor_id = :doctor_id
                                  AND appointment_date = :appointment_date
                                  AND appointment_time = :appointment_time
                                  AND cancelled_at IS NULL');
        $checkStatement->bindParam(':doctor_id', $doctorId);
        $checkStatement->bindParam(':appointment_date', $appointmentDate);
        $checkStatement->bindParam(':appointment_time', $appointmentTime);
        $checkStatement->execute();
        $existing = $checkStatement->fetch(PDO::FETCH_ASSOC);

        if (!$existing) {
            $statement = $connection->prepare('INSERT INTO appointment (patient_id, doctor_id, appointment_date, appointment_time)
                                  VALUES (:patient_id, :doctor_id, :appointment_date, :appointment_time)');
            $statement->bindParam(':patient_id', $patientId);
            $statement->bindParam(':doctor_id', $doctorId);
            $statement->bindParam(':appointment_date', $appointmentDate);
            $statement->bindParam(':appointment_time', $appointmentTime);
            $statement->execute();
        }
    }
}

header('Location: dashboard.php');
exit;