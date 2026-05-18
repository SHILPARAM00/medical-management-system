<?php
session_start();
require_once "../../config/db_connect.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit;
}

$patient_id = $_SESSION['user_id'];
$name = $_SESSION['name'];

/* FETCH DATA */

// Upcoming Appointment
$appt = $conn->query("
    SELECT * FROM appointment
    WHERE patient_id = '$patient_id'
    ORDER BY appointment_date ASC, appointment_time ASC
    LIMIT 1
");
$appointment = $appt->fetch_assoc();

// Recent Prescriptions
$prescriptions = $conn->query("
    SELECT * FROM prescription
    WHERE patient_id = '$patient_id'
    ORDER BY created_at DESC
    LIMIT 5
");

// Latest Health Record
$health = $conn->query("
    SELECT * FROM health_records
    WHERE patient_id = '$patient_id'
    ORDER BY created_at DESC
    LIMIT 1
");
$healthData = $health->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
<title>Patient Dashboard</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    font-family:Arial, sans-serif;
    background:#f4f7fb;
}

.container{
    display:flex;
}

/* Sidebar */
.sidebar{
    width:260px;
    background:#ffffff;
    min-height:100vh;
    padding:20px;
    box-shadow:2px 0 10px rgba(0,0,0,0.05);
}

.logo-box{
    display:flex;
    align-items:center;
    gap:10px;
    margin-bottom:25px;
    padding-bottom:15px;
    border-bottom:1px solid #eee;
}

.logo-box img{
    width:45px;
    height:45px;
    border-radius:10px;
    object-fit:cover;
}

.logo-text{
    font-size:16px;
    font-weight:bold;
    color:#2b6cb0;
    line-height:1.3;
}

.menu a{
    display:block;
    padding:12px;
    margin-bottom:8px;
    text-decoration:none;
    color:#333;
    border-radius:8px;
    transition:0.3s;
}

.menu a:hover,
.menu a.active{
    background:#e6f0fa;
    color:#2b6cb0;
}

/* Main */
.main{
    flex:1;
    padding:25px;
}

/* Header */
.header{
    display:flex;
    justify-content:space-between;
    align-items:center;
}

.profile{
    display:flex;
    align-items:center;
    gap:15px;
}

.profile img{
    width:42px;
    height:42px;
    border-radius:50%;
}

.notify{
    font-size:20px;
    cursor:pointer;
    color:#2b6cb0;
}

/* Cards */
.card{
    background:#fff;
    padding:20px;
    border-radius:14px;
    margin-top:20px;
    box-shadow:0 5px 20px rgba(0,0,0,0.05);
}

.grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:20px;
}

h3{
    margin-bottom:15px;
    color:#222;
}

.small{
    font-size:13px;
    color:#777;
}

/* Health */
.health-box{
    display:grid;
    grid-template-columns:repeat(2,1fr);
    gap:12px;
}

.health-item{
    background:#f8fafc;
    border-radius:10px;
    padding:15px;
    text-align:center;
}

.health-item i{
    font-size:22px;
    color:#2b6cb0;
    margin-bottom:8px;
}

.health-item h4{
    font-size:14px;
    margin-bottom:5px;
}

.health-item p{
    font-weight:bold;
}

/* Prescriptions */
.prescription-box{
    padding:12px 0;
    border-bottom:1px solid #eee;
}

/* Quick Actions */
.actions{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:15px;
}

.action-card{
    background:#fff;
    padding:18px;
    text-align:center;
    border-radius:12px;
    cursor:pointer;
    transition:0.3s;
    box-shadow:0 5px 15px rgba(0,0,0,0.05);
}

.action-card:hover{
    transform:translateY(-5px);
    background:#f0f7ff;
}

.action-card i{
    font-size:24px;
    color:#2b6cb0;
    margin-bottom:10px;
}

.action-card p{
    font-size:14px;
    font-weight:600;
}
</style>
</head>

<body>

<div class="container">

<!-- Sidebar -->
<div class="sidebar">

    <div class="logo-box">
        <img src="../../frontend/assets/images/logo.png.jpeg">
        <div class="logo-text">
            Medical Management<br>System
        </div>
    </div>

    <div class="menu">
        <a href="#" class="active">🏠 Dashboard</a>
        <a href="appointments.php">📅 Appointments</a>
        <a href="prescriptions.php">💊 Prescriptions</a>
        <a href="bills.php">💰 Bills</a>
        <a href="profile.php">👤 Profile</a>
        <a href="../../logout.php">🚪 Logout</a>
    </div>

</div>

<!-- Main -->
<div class="main">

<!-- Header -->
<div class="header">
    <div>
        <h2>Welcome Back, <?php echo $name; ?> 👋</h2>
        <p class="small">Your health summary today</p>
    </div>

    <div class="profile">
        <i class="fa fa-bell notify"></i>
        <img src="https://i.pravatar.cc/100">
    </div>
</div>

<!-- Grid -->
<div class="grid">

<!-- Appointment -->
<div class="card">
<h3>Upcoming Appointment</h3>

<?php if($appointment): ?>
<p><b>Date:</b> <?php echo $appointment['appointment_date']; ?></p>
<p><b>Time:</b> <?php echo $appointment['appointment_time']; ?></p>
<p><b>Doctor ID:</b> <?php echo $appointment['doctor_id']; ?></p>
<?php else: ?>
<p>No appointment found</p>
<?php endif; ?>

</div>

<!-- Health -->
<div class="card">
<h3>Health Overview</h3>

<?php if($healthData): ?>
<div class="health-box">

<div class="health-item">
<i class="fa fa-heartbeat"></i>
<h4>Heart Rate</h4>
<p><?php echo $healthData['heart_rate']; ?> bpm</p>
</div>

<div class="health-item">
<i class="fa fa-stethoscope"></i>
<h4>Blood Pressure</h4>
<p><?php echo $healthData['blood_pressure']; ?></p>
</div>

<div class="health-item">
<i class="fa fa-vial"></i>
<h4>Sugar</h4>
<p><?php echo $healthData['sugar']; ?></p>
</div>

<div class="health-item">
<i class="fa fa-weight-scale"></i>
<h4>Weight</h4>
<p><?php echo $healthData['weight']; ?> kg</p>
</div>

</div>

<p class="small" style="margin-top:12px;">
Updated: <?php echo $healthData['created_at']; ?>
</p>

<?php else: ?>
<p>No health data available</p>
<?php endif; ?>

</div>

</div>

<!-- Prescriptions -->
<div class="card">
<h3>Recent Prescriptions</h3>

<?php
if($prescriptions->num_rows > 0){
while($p = $prescriptions->fetch_assoc()){
?>
<div class="prescription-box">
<p><b>Medicine:</b> <?php echo $p['medicine_name']; ?></p>
<p class="small">Dosage: <?php echo $p['dosage']; ?></p>
<p class="small">Date: <?php echo $p['created_at']; ?></p>
</div>
<?php
}
}else{
echo "<p>No prescriptions found</p>";
}
?>

</div>

<!-- Quick Actions -->
<div class="card">
<h3>Quick Actions</h3>

<div class="actions">

<div class="action-card" onclick="go('book.php')">
<i class="fa fa-calendar-plus"></i>
<p>Book Appointment</p>
</div>

<div class="action-card" onclick="go('appointments.php')">
<i class="fa fa-calendar-check"></i>
<p>My Appointments</p>
</div>

<div class="action-card" onclick="go('bills.php')">
<i class="fa fa-credit-card"></i>
<p>Pay Bills</p>
</div>

<div class="action-card" onclick="go('prescriptions.php')">
<i class="fa fa-pills"></i>
<p>Prescriptions</p>
</div>

<div class="action-card" onclick="go('profile.php')">
<i class="fa fa-user"></i>
<p>Profile</p>
</div>

<div class="action-card">
<i class="fa fa-notes-medical"></i>
<p>Health Records</p>
</div>

</div>

</div>

</div>

<script>
function go(page){
    window.location.href = page;
}
</script>

</body>
</html>