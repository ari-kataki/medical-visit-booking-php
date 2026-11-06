<?php
require_once 'DbConnector.php';
$config = require 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$connection = new DbConnector($config['host'], $config['user'], $config['pass'], $config['name']);
$role = $_SESSION['role'];
$userId = $_SESSION['user_id'];
$isPaziente = $role === 'PAZIENTE';
$isMedico = $role === 'MEDICO';

if ($isPaziente) {
    $statement = $connection->prepare('SELECT appointment.id,
                                 appointment.appointment_date,
                                 appointment.appointment_time,
                                 appointment.status,
                                 user.username AS doctor_name
                          FROM appointment
                          JOIN user ON user.id = appointment.doctor_id
                          WHERE appointment.patient_id = :patient_id
                            AND appointment.cancelled_at IS NULL
                          ORDER BY appointment.appointment_date DESC
                          LIMIT 0, 10');
    $statement->bindParam(':patient_id', $userId);
    $statement->execute();
    $appointments = $statement->fetchAll(PDO::FETCH_ASSOC);

    $doctorStatement = $connection->prepare('SELECT id, username FROM user WHERE role = :role');
    $roleMedico = 'MEDICO';
    $doctorStatement->bindParam(':role', $roleMedico);
    $doctorStatement->execute();
    $doctors = $doctorStatement->fetchAll(PDO::FETCH_ASSOC);
}

if ($isMedico) {
    $statement = $connection->prepare('SELECT appointment.id,
                                 appointment.appointment_date,
                                 appointment.appointment_time,
                                 appointment.status,
                                 user.username AS patient_name
                          FROM appointment
                          JOIN user ON user.id = appointment.patient_id
                          WHERE appointment.doctor_id = :doctor_id
                            AND appointment.cancelled_at IS NULL
                          ORDER BY appointment.appointment_date DESC
                          LIMIT 0, 10');
    $statement->bindParam(':doctor_id', $userId);
    $statement->execute();
    $appointments = $statement->fetchAll(PDO::FETCH_ASSOC);
}
?>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Dashboard - Prenotazione Visite</title>
        <link rel="stylesheet" href="dashboard.css" />
    </head>
    <body>
        <h1 class="Header">Prenotazione Visite</h1>
        <h3 class="credit">Sistema di Gestione Appuntamenti</h3>
        
        <div class="header-container">
            <span class="user-info">Utente: <strong><?= htmlspecialchars($_SESSION['username']) ?></strong> (<?= htmlspecialchars($role) ?>)</span>
            <form method="post" action="logout.php" style="margin: 0;">
                <button class="logout">Logout</button>
            </form>
        </div>

        <?php if ($isPaziente) { ?>
            <h3>Prenota una visita</h3>
            <form action="book.php" method="post">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>" />
                <div class="form-group">
                    <label>Medico</label>
                    <select name="doctor_id" required>
                        <option value="">-- Seleziona un medico --</option>
                        <?php for ($i = 0; $i < count($doctors); $i++) { ?>
                            <option value="<?= $doctors[$i]['id'] ?>"><?= htmlspecialchars($doctors[$i]['username']) ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Data</label>
                    <input type="date" name="appointment_date" required />
                </div>
                <div class="form-group">
                    <label>Ora</label>
                    <input type="time" name="appointment_time" required />
                </div>
                <button type="submit">Prenota Visita</button>
            </form>

            <h3>Le tue prenotazioni</h3>
            <?php if (count($appointments) > 0) { ?>
                <?php for ($i = 0; $i < count($appointments); $i++) {
                    $appt = $appointments[$i];
                ?>
                    <div class="appointment">
                        <div class="appointment-username">Medico: <?= htmlspecialchars($appt['doctor_name']) ?></div>
                        <div class="appointment-content">
                            <div>Data: <?= htmlspecialchars($appt['appointment_date']) ?> ore <?= htmlspecialchars($appt['appointment_time']) ?></div>
                            <div>Stato: <strong><?= htmlspecialchars($appt['status']) ?></strong></div>
                        </div>
                        <?php if ($appt['status'] === 'PENDING') { ?>
                            <div class="button-group">
                                <form action="cancel.php" method="post" style="flex: 1;">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>" />
                                    <input type="hidden" name="appointment_id" value="<?= $appt['id'] ?>" />
                                    <button type="submit" style="width: 100%; margin: 0;">Annulla</button>
                                </form>
                            </div>
                        <?php } ?>
                    </div>
                <?php } ?>
            <?php } else { ?>
                <div class="appointment" style="background-color: #ccc; text-align: center;">Nessuna prenotazione</div>
            <?php } ?>

        <?php } ?>

        <?php if ($isMedico) { ?>
            <h3>Le tue visite</h3>
            <?php if (count($appointments) > 0) { ?>
                <?php for ($i = 0; $i < count($appointments); $i++) {
                    $appt = $appointments[$i];
                ?>
                    <div class="appointment">
                        <div class="appointment-username">Paziente: <?= htmlspecialchars($appt['patient_name']) ?></div>
                        <div class="appointment-content">
                            <div>Data: <?= htmlspecialchars($appt['appointment_date']) ?> ore <?= htmlspecialchars($appt['appointment_time']) ?></div>
                            <div>Stato: <strong><?= htmlspecialchars($appt['status']) ?></strong></div>
                        </div>

                        <?php if ($appt['status'] === 'PENDING') { ?>
                            <div class="button-group">
                                <form action="manage.php" method="post" style="flex: 1;">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>" />
                                    <input type="hidden" name="appointment_id" value="<?= $appt['id'] ?>" />
                                    <input type="hidden" name="action" value="CONFIRMED" />
                                    <button type="submit" style="width: 100%; margin: 0;">Conferma</button>
                                </form>

                                <form action="manage.php" method="post" style="flex: 1;">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>" />
                                    <input type="hidden" name="appointment_id" value="<?= $appt['id'] ?>" />
                                    <input type="hidden" name="action" value="REFUSED" />
                                    <button type="submit" style="width: 100%; margin: 0; background-color: #d9534f;">Rifiuta</button>
                                </form>
                            </div>
                        <?php } ?>

                        <?php if ($appt['status'] === 'CONFIRMED') { ?>
                            <div class="button-group">
                                <form action="manage.php" method="post" style="flex: 1;">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>" />
                                    <input type="hidden" name="appointment_id" value="<?= $appt['id'] ?>" />
                                    <input type="hidden" name="action" value="DONE" />
                                    <button type="submit" style="width: 100%; margin: 0; background-color: #5cb85c;">Segna come effettuata</button>
                                </form>
                            </div>
                        <?php } ?>
                    </div>
                <?php } ?>
            <?php } else { ?>
                <div class="appointment" style="background-color: #ccc; text-align: center;">Nessuna visita programmata</div>
            <?php } ?>
        <?php } ?>
    </body>
</html>
