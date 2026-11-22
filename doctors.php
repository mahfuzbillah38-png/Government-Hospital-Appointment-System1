<?php
include "config.php";

$dept = isset($_GET['dept']) ? (int)$_GET['dept'] : 0;
$depts = $conn->query("SELECT * FROM departments ORDER BY dept_name");

if ($dept > 0) {
    $stmt = $conn->prepare("SELECT d.*, p.dept_name FROM doctors d
        JOIN departments p ON d.dept_id = p.dept_id WHERE d.dept_id = ? ORDER BY d.name");
    $stmt->bind_param("i", $dept);
} else {
    $stmt = $conn->prepare("SELECT d.*, p.dept_name FROM doctors d
        JOIN departments p ON d.dept_id = p.dept_id ORDER BY p.dept_name, d.name");
}
$stmt->execute();
$doctors = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Doctor Directory</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <header>
    <h1>Government Hospital</h1>
    <nav><a href="index.html">Home</a><a href="doctors.php">Doctors</a></nav>
  </header>

  <main>
    <h2>Doctor Directory</h2>

    <form method="get" class="filter">
      <label>Department:</label>
      <select name="dept" onchange="this.form.submit()">
        <option value="0">All Departments</option>
        <?php while ($d = $depts->fetch_assoc()): ?>
          <option value="<?= $d['dept_id'] ?>" <?= $dept == $d['dept_id'] ? 'selected' : '' ?>>
            <?= h($d['dept_name']) ?>
          </option>
        <?php endwhile; ?>
      </select>
      <noscript><button class="btn">Filter</button></noscript>
    </form>

    <table>
      <tr><th>Name</th><th>Department</th><th>Qualification</th><th>Room</th><th>Visiting Days</th><th></th></tr>
      <?php if ($doctors->num_rows == 0): ?>
        <tr><td colspan="6">No doctors found.</td></tr>
      <?php endif; ?>
      <?php while ($r = $doctors->fetch_assoc()): ?>
        <tr>
          <td><?= h($r['name']) ?></td>
          <td><?= h($r['dept_name']) ?></td>
          <td><?= h($r['qualification']) ?></td>
          <td><?= h($r['room_no']) ?></td>
          <td><?= h($r['visiting_days']) ?></td>
          <td><a class="btn small" href="book.php?doctor_id=<?= $r['doctor_id'] ?>">Book</a></td>
        </tr>
      <?php endwhile; ?>
    </table>
  </main>

  <footer>&copy; 2026 Government Hospital Appointment System</footer>
</body>
</html>
