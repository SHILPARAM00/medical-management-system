<?php
session_start();
require_once "../../config/db_connect.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

$id = $_GET['id'];

$data = $conn->query("
SELECT 
a.*,
pu.name as patient_name,
du.name as doctor_name,
d.specialization
FROM appointment a
JOIN users pu ON a.patient_id = pu.user_id
JOIN doctors d ON a.doctor_id = d.doctor_id
JOIN users du ON d.user_id = du.user_id
WHERE a.appointment_id = $id
")->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
<title>View Appointment</title>
<style>
body{
font-family:Arial;
background:#f4f7fb;
padding:30px;
}
.card{
background:white;
padding:25px;
border-radius:12px;
box-shadow:0 5px 20px rgba(0,0,0,0.08);
max-width:600px;
margin:auto;
}
h2{
color:#2b7cff;
}
p{
font-size:16px;
margin:10px 0;
}
</style>
</head>
<body>

<div class="card">
<h2>Appointment Details</h2>

<p><b>Patient:</b> <?php echo $data['patient_name']; ?></p>
<p><b>Doctor:</b> <?php echo $data['doctor_name']; ?></p>
<p><b>Specialization:</b> <?php echo $data['specialization']; ?></p>
<p><b>Date:</b> <?php echo $data['appointment_date']; ?></p>
<p><b>Time:</b> <?php echo $data['appointment_time']; ?></p>
<p><b>Location:</b> <?php echo $data['location']; ?></p>
<p><b>Status:</b> <?php echo $data['status']; ?></p>

</div>

</body>
</html>