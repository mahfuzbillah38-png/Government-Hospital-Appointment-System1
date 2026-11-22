<?php
include "config.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $conn->prepare("SELECT a.*, d.name AS doctor, d.room_no, p.dept_name, s.slot_time
    FROM appointments a
    JOIN doctors d ON a.doctor_id = d.doctor_id
    JOIN departments p ON d.dept_id = p.dept_id
    JOIN time_slots s ON a.slot_id = s.slot_id
    WHERE a.appt_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$a = $stmt->get_result()->fetch_assoc();
if (!$a) { header("Location: doctors.php"); exit; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Appointment Token</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <header>
    <h1>Government Hospital</h1>
    <nav><a href="index.html">Home</a><a href="doctors.php">Doctors</a></nav>
  </header>

  <main>
    <div class="token">
      <h2>Appointment Confirmed</h2>
      <p>Your Token Number</p>
      <div class="token-no"><?= h($a['token_no']) ?></div>

      <table>
        <tr><th>Patient</th><td><?= h($a['patient_name']) ?> (<?= $a['age'] ?>, <?= h($a['gender']) ?>)</td></tr>
        <tr><th>Phone</th><td><?= h($a['phone']) ?></td></tr>
        <tr><th>Doctor</th><td><?= h($a['doctor']) ?></td></tr>
        <tr><th>Department</th><td><?= h($a['dept_name']) ?></td></tr>
        <tr><th>Room</th><td><?= h($a['room_no']) ?></td></tr>
        <tr><th>Date</th><td><?= date("d M Y", strtotime($a['appt_date'])) ?></td></tr>
        <tr><th>Time</th><td><?= date("h:i A", strtotime($a['slot_time'])) ?></td></tr>
      </table>

      <p class="note">Please arrive 15 minutes early and show this token at the reception.</p>
      <button class="btn" onclick="window.print()">Print Token</button>
      <a class="btn" href="doctors.php">Book Another</a>
    </div>
  </main>

  <footer>&copy; 2026 Government Hospital Appointment System</footer>
</body>
</html>
