<?php
session_start();
require_once "../../config/db_connect.php";

if (!isset($_GET['id'])) {
    die("Invalid request");
}

$appointment_id = $_GET['id'];

/* FETCH APPOINTMENT DETAILS */
$stmt = $conn->prepare("
    SELECT 
        a.*,
        u.name AS patient_name,
        doc.name AS doctor_name,
        d.specialization
    FROM appointment a
    LEFT JOIN users u ON a.patient_id = u.user_id
    LEFT JOIN doctors d ON a.doctor_id = d.doctor_id
    LEFT JOIN users doc ON d.user_id = doc.user_id
    WHERE a.appointment_id = ?
");

$stmt->bind_param("i", $appointment_id);
$stmt->execute();

$result = $stmt->get_result();
$data = $result->fetch_assoc();

if(!$data){
    die("Appointment not found");
}

$status = strtolower(trim($data['status']));
?>

<!DOCTYPE html>
<html>
<head>
<title>View Appointment</title>

<style>

body{
    margin:0;
    font-family:Arial;
    background:#f4f7fb;
}

.container{
    display:flex;
    justify-content:center;
    align-items:center;
    height:100vh;
}

.card{
    background:white;
    padding:30px;
    width:520px;
    border-radius:12px;
    box-shadow:0 5px 20px rgba(0,0,0,0.1);
}

h2{
    color:#2b6cb0;
    margin-bottom:20px;
}

.info{
    margin:12px 0;
    font-size:15px;
}

.label{
    font-weight:bold;
    color:#444;
}

/* STATUS BADGE */
.badge{
    padding:6px 12px;
    border-radius:20px;
    font-size:12px;
    font-weight:bold;
    display:inline-block;
}

.pending{
    background:#fff3cd;
    color:#856404;
}

.confirmed{
    background:#d4edda;
    color:#155724;
}

.cancelled{
    background:#f8d7da;
    color:#721c24;
}

.completed{
    background:#d1ecf1;
    color:#0c5460;
}

.back{
    display:inline-block;
    margin-top:20px;
    padding:10px 15px;
    background:#2b6cb0;
    color:white;
    text-decoration:none;
    border-radius:6px;
}

.back:hover{
    opacity:0.9;
}

</style>
</head>

<body>

<div class="container">

<div class="card">

<h2>Appointment Details</h2>

<div class="info">
<span class="label">Patient:</span>
<?php echo $data['patient_name']; ?>
</div>

<div class="info">
<span class="label">Doctor:</span>
Dr. <?php echo $data['doctor_name']; ?>
</div>

<div class="info">
<span class="label">Specialization:</span>
<?php echo $data['specialization']; ?>
</div>

<div class="info">
<span class="label">Date:</span>
<?php echo $data['appointment_date']; ?>
</div>

<div class="info">
<span class="label">Time:</span>
<?php echo $data['appointment_time']; ?>
</div>

<div class="info">
<span class="label">Problem:</span>
<?php echo $data['problem']; ?>
</div>

<div class="info">
<span class="label">Status:</span>

<span class="badge <?php echo $status; ?>">
<?php echo ucfirst($status); ?>
</span>

</div>

<a class="back" href="appointments.php">← Back</a>

</div>

</div>

</body>
</html>