<?php
include "config.php";

$doctor_id = isset($_REQUEST['doctor_id']) ? (int)$_REQUEST['doctor_id'] : 0;

$stmt = $conn->prepare("SELECT d.*, p.dept_name FROM doctors d
    JOIN departments p ON d.dept_id = p.dept_id WHERE d.doctor_id = ?");
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$doc = $stmt->get_result()->fetch_assoc();
if (!$doc) { header("Location: doctors.php"); exit; }

$today = date("Y-m-d");
$max   = date("Y-m-d", strtotime("+14 days"));
$date  = isset($_REQUEST['date']) ? $_REQUEST['date'] : $today;
if ($date < $today || $date > $max) { $date = $today; }

$error = "";

// ---- Save appointment ----
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name    = trim($_POST['patient_name']);
    $age     = (int)$_POST['age'];
    $gender  = $_POST['gender'];
    $phone   = trim($_POST['phone']);
    $slot_id = (int)($_POST['slot_id'] ?? 0);
    $problem = trim($_POST['problem']);

    if ($name == "" || $age < 1 || $age > 120 || !preg_match('/^[0-9+]{10,15}$/', $phone) || $slot_id == 0) {
        $error = "Please fill all fields correctly and select a time slot.";
    } else {
        $ins = $conn->prepare("INSERT INTO appointments
            (patient_name, age, gender, phone, doctor_id, appt_date, slot_id, problem)
            VALUES (?,?,?,?,?,?,?,?)");
        $ins->bind_param("sissisis", $name, $age, $gender, $phone, $doctor_id, $date, $slot_id, $problem);
        if ($ins->execute()) {
            $id = $conn->insert_id;
            // Token: serial number of this doctor's appointment for the day
            $c = $conn->prepare("SELECT COUNT(*) FROM appointments WHERE doctor_id=? AND appt_date=? AND appt_id<=?");
            $c->bind_param("isi", $doctor_id, $date, $id);
            $c->execute();
            $c->bind_result($serial);
            $c->fetch();
            $c->close();
            $token = sprintf("D%02d-%s-%03d", $doctor_id, date("md", strtotime($date)), $serial);
            $u = $conn->prepare("UPDATE appointments SET token_no=? WHERE appt_id=?");
            $u->bind_param("si", $token, $id);
            $u->execute();
            header("Location: token.php?id=" . $id);
            exit;
        } else {
            $error = "Sorry, that slot was just booked. Please choose another slot.";
        }
    }
}

// ---- Available slots for the chosen date ----
$sl = $conn->prepare("SELECT s.slot_id, s.slot_time,
    (SELECT COUNT(*) FROM appointments a
      WHERE a.doctor_id=? AND a.appt_date=? AND a.slot_id=s.slot_id) AS taken
    FROM time_slots s ORDER BY s.slot_time");
$sl->bind_param("is", $doctor_id, $date);
$sl->execute();
$slots = $sl->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Book Appointment</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <header>
    <h1>Government Hospital</h1>
    <nav><a href="index.html">Home</a><a href="doctors.php">Doctors</a></nav>
  </header>

  <main>
    <h2>Book Appointment</h2>
    <div class="info">
      <b><?= h($doc['name']) ?></b> &mdash; <?= h($doc['dept_name']) ?><br>
      <?= h($doc['qualification']) ?> | Room <?= h($doc['room_no']) ?> | <?= h($doc['visiting_days']) ?>
    </div>

    <?php if ($error): ?><p class="error"><?= h($error) ?></p><?php endif; ?>

    <!-- Date picker (reloads page to show slots for that date) -->
    <form method="get" class="filter">
      <input type="hidden" name="doctor_id" value="<?= $doctor_id ?>">
      <label>Appointment Date:</label>
      <input type="date" name="date" value="<?= h($date) ?>" min="<?= $today ?>" max="<?= $max ?>"
             onchange="this.form.submit()">
    </form>

    <form method="post" class="form">
      <input type="hidden" name="doctor_id" value="<?= $doctor_id ?>">
      <input type="hidden" name="date" value="<?= h($date) ?>">

      <label>Available Time Slots (<?= h($date) ?>)</label>
      <div class="slots">
        <?php while ($s = $slots->fetch_assoc()): ?>
          <label class="slot <?= $s['taken'] ? 'taken' : '' ?>">
            <input type="radio" name="slot_id" value="<?= $s['slot_id'] ?>" <?= $s['taken'] ? 'disabled' : '' ?>>
            <?= date("h:i A", strtotime($s['slot_time'])) ?>
          </label>
        <?php endwhile; ?>
      </div>

      <label>Patient Name</label>
      <input type="text" name="patient_name" required maxlength="80">

      <label>Age</label>
      <input type="number" name="age" min="1" max="120" required>

      <label>Gender</label>
      <select name="gender" required>
        <option>Male</option><option>Female</option><option>Other</option>
      </select>

      <label>Phone</label>
      <input type="text" name="phone" required placeholder="01XXXXXXXXX">

      <label>Problem / Symptoms</label>
      <textarea name="problem" rows="3" maxlength="200"></textarea>

      <button class="btn" type="submit">Confirm Appointment</button>
    </form>
  </main>

  <footer>&copy; 2026 Government Hospital Appointment System</footer>
</body>
</html>
