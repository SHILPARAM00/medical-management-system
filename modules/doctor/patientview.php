<?php
session_start();
require_once "../../config/db_connect.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'doctor') {
    header("Location: ../../login.php");
    exit;
}

$patient_id = $_GET['id'] ?? 0;

/* USER DETAILS */
$user = $conn->query("
    SELECT u.name, u.email, p.*
    FROM users u
    LEFT JOIN patients p ON u.user_id = p.user_id
    WHERE u.user_id = $patient_id
")->fetch_assoc();

/* APPOINTMENTS */
$appointments = $conn->query("
    SELECT *
    FROM appointment
    WHERE patient_id = $patient_id
    ORDER BY appointment_date DESC
");

/* PRESCRIPTIONS */
$prescriptions = $conn->query("
    SELECT *
    FROM prescription
    WHERE patient_id = $patient_id
    ORDER BY created_at DESC
");

/* LAB REPORTS */
$reports = $conn->query("
    SELECT *
    FROM lab_reports
    WHERE patient_id = $patient_id
    ORDER BY report_date DESC
");
?>

<!DOCTYPE html>
<html>
<head>
<title>Patient View</title>

<style>
body{
    margin:0;
    font-family:Arial;
    background:#f4f7fb;
    padding:20px;
}

.card{
    background:white;
    padding:20px;
    border-radius:12px;
    margin-bottom:20px;
    box-shadow:0 5px 15px rgba(0,0,0,0.05);
}

h2,h3{
    color:#2b6cb0;
}

.small{
    color:#777;
}
</style>
</head>

<body>

<h2>👤 Patient Full History</h2>

<!-- PROFILE -->
<div class="card">
<h3><?php echo $user['name']; ?></h3>
<p>Email: <?php echo $user['email']; ?></p>
<p>Phone: <?php echo $user['phone']; ?></p>
<p>Gender: <?php echo $user['gender']; ?></p>
<p>Emergency Contact: <?php echo $user['emergency_phone']; ?></p>
</div>

<!-- APPOINTMENTS -->
<div class="card">
<h3>📅 Appointment History</h3>

<?php while($a = $appointments->fetch_assoc()): ?>
<p>
<b><?php echo $a['appointment_date']; ?></b> -
<?php echo $a['doctor_name']; ?> -
<?php echo $a['status']; ?>
</p>
<hr>
<?php endwhile; ?>

</div>

<!-- PRESCRIPTIONS -->
<div class="card">
<h3>💊 Prescriptions</h3>

<?php while($p = $prescriptions->fetch_assoc()): ?>
<p>
<b><?php echo $p['medicine_name']; ?></b><br>
Dosage: <?php echo $p['dosage']; ?><br>
Duration: <?php echo $p['duration']; ?>
</p>
<hr>
<?php endwhile; ?>

</div>

<!-- LAB REPORTS -->
<div class="card">
<h3>🧪 Lab Reports</h3>

<?php while($r = $reports->fetch_assoc()): ?>
<p>
<b><?php echo $r['report_name']; ?></b><br>
Date: <?php echo $r['report_date']; ?><br>
Status: <?php echo $r['status']; ?>
</p>
<hr>
<?php endwhile; ?>

</div>

</body>
</html>