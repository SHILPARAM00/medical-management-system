<?php
session_start();
require_once "../../config/db_connect.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'doctor') {
    header("Location: ../../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$name = $_SESSION['name'];
$today = date("Y-m-d");

/* Get doctor_id */
$getDoctor = $conn->query("SELECT doctor_id FROM doctors WHERE user_id = $user_id");
$doctorData = $getDoctor->fetch_assoc();
$doctor_id = $doctorData['doctor_id'];

/* Today's appointments */
$todayAppointments = $conn->query("
    SELECT a.*, u.name as patient_name
    FROM appointment a
    JOIN users u ON a.patient_id = u.user_id
    WHERE a.doctor_id = $doctor_id 
    AND a.appointment_date = '$today'
");

/* Total patients */
$totalPatients = $conn->query("
    SELECT COUNT(DISTINCT patient_id) as total
    FROM appointment 
    WHERE doctor_id = $doctor_id
")->fetch_assoc()['total'];

$todayPatients = $todayAppointments->num_rows;

/* Next patient */
$nextPatient = $conn->query("
    SELECT u.name, a.appointment_time, a.appointment_date
    FROM appointment a
    JOIN users u ON a.patient_id = u.user_id
    WHERE a.doctor_id = $doctor_id
    AND a.appointment_date >= CURDATE()
    ORDER BY a.appointment_date ASC, a.appointment_time ASC
    LIMIT 1
")->fetch_assoc();

/* Recent patients */
$recentPatients = $conn->query("
    SELECT u.user_id, u.name, p.gender, p.age
    FROM appointment a
    JOIN users u ON a.patient_id = u.user_id
    JOIN patients p ON u.user_id = p.user_id
    WHERE a.doctor_id = $doctor_id
    ORDER BY a.appointment_date DESC
    LIMIT 4
");

/* Chart */
$chartLabels = [];
$chartData = [];

for($i=6; $i>=0; $i--){
    $date = date("Y-m-d", strtotime("-$i days"));
    $label = date("D", strtotime($date));

    $count = $conn->query("
        SELECT COUNT(*) as total
        FROM appointment
        WHERE doctor_id = $doctor_id
        AND appointment_date = '$date'
    ")->fetch_assoc()['total'];

    $chartLabels[] = $label;
    $chartData[] = $count;
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Doctor Dashboard</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    font-family:Arial;
    background:#f4f7fb;
}

.container{
    display:flex;
}

/* SIDEBAR */
.sidebar{
    width:260px;
    background:#fff;
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
}

.menu a:hover,
.menu a.active{
    background:#e6f0fa;
    color:#2b6cb0;
}

/* MAIN */
.main{
    flex:1;
    padding:25px;
}

/* HEADER */
.header{
    display:flex;
    justify-content:space-between;
    align-items:center;
}

.profile{
    display:flex;
    gap:15px;
    align-items:center;
}

.profile i{
    font-size:18px;
    color:#2b6cb0;
    background:#fff;
    padding:10px;
    border-radius:50%;
    box-shadow:0 3px 8px rgba(0,0,0,0.08);
}

/* CARDS */
.cards{
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:15px;
    margin-top:20px;
}

.card{
    background:white;
    padding:20px;
    border-radius:14px;
    box-shadow:0 5px 15px rgba(0,0,0,0.05);
}

/* GRID */
.grid{
    display:grid;
    grid-template-columns:2fr 1fr;
    gap:20px;
    margin-top:20px;
}

.item{
    display:flex;
    justify-content:space-between;
    padding:12px 0;
    border-bottom:1px solid #eee;
}

.status{
    padding:5px 10px;
    border-radius:10px;
    font-size:12px;
    background:#e6f0fa;
}

.small{
    color:#777;
    font-size:13px;
}

button{
    background:#2b6cb0;
    color:white;
    border:none;
    padding:6px 12px;
    border-radius:6px;
    cursor:pointer;
}

input[type="date"]{
    width:100%;
    padding:10px;
    border:1px solid #ccc;
    border-radius:8px;
}
</style>
</head>

<body>

<div class="container">

<!-- SIDEBAR -->
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
        <a href="patients.php">👥 Patients</a>
        <a href="prescription.php">💊 Prescriptions</a>
        <a href="profile.php">👤 Profile</a>
        <a href="../../logout.php">🚪 Logout</a>
    </div>
</div>

<!-- MAIN -->
<div class="main">

<div class="header">
    <div>
        <h2>Welcome Back, Dr. <?php echo $name; ?> 👋</h2>
        <p class="small">Manage your appointments and patients</p>
    </div>

    <div class="profile">
        <i class="fa fa-bell"></i>
        <i class="fa fa-user"></i>
    </div>
</div>

<!-- STATS -->
<div class="cards">

    <div class="card">
        <h4>Total Patients</h4>
        <h2><?php echo $totalPatients; ?></h2>
    </div>

    <div class="card">
        <h4>Today Patients</h4>
        <h2><?php echo $todayPatients; ?></h2>
    </div>

    <div class="card">
        <h4>Appointments</h4>
        <h2><?php echo $todayAppointments->num_rows; ?></h2>
    </div>

    <div class="card">
        <h4>Status</h4>
        <h2>Active</h2>
    </div>

</div>

<div class="grid">

<!-- LEFT -->
<div>

<div class="card">
<h3>Today's Schedule</h3>

<?php if($todayAppointments->num_rows == 0): ?>
<p>No appointments today</p>
<?php endif; ?>

<?php while($a = $todayAppointments->fetch_assoc()): ?>
<div class="item">
    <div>
        <b><?php echo $a['appointment_time']; ?></b> - <?php echo $a['patient_name']; ?>
    </div>
    <div class="status"><?php echo $a['status']; ?></div>
</div>
<?php endwhile; ?>
</div>

<div class="card" style="margin-top:20px;">
<h3>Recent Patients</h3>

<?php while($p = $recentPatients->fetch_assoc()): ?>
<p>
    <b><?php echo $p['name']; ?></b>
    <button onclick="viewPatient(<?php echo $p['user_id']; ?>)">View</button>
</p>
<p class="small"><?php echo $p['gender']; ?> | <?php echo $p['age']; ?> yrs</p>
<hr style="margin:10px 0;border:0;border-top:1px solid #eee;">
<?php endwhile; ?>

</div>

</div>

<!-- RIGHT -->
<div>

<div class="card">
<h3>Next Patient</h3>

<?php if($nextPatient): ?>
<p><b><?php echo $nextPatient['name']; ?></b></p>
<p><?php echo $nextPatient['appointment_date']; ?></p>
<p><?php echo $nextPatient['appointment_time']; ?></p>
<?php else: ?>
<p>No upcoming patient</p>
<?php endif; ?>

</div>

<div class="card" style="margin-top:20px;">
<h3>Weekly Appointments</h3>
<canvas id="chart" style="max-height:220px;"></canvas>
</div>

<div class="card" style="margin-top:20px;">
<h3>Select Date</h3>

<input type="date" id="datePicker">
<div id="dateAppointments" style="margin-top:15px;"></div>

</div>

</div>
</div>

</div>
</div>

<script>
function viewPatient(id){
    window.location.href = "patientview.php?id=" + id;
}

const labels = <?php echo json_encode($chartLabels); ?>;
const data = <?php echo json_encode($chartData); ?>;

new Chart(document.getElementById("chart"), {
    type: "line",
    data: {
        labels: labels,
        datasets: [{
            label: "Appointments",
            data: data,
            borderColor: "#2b6cb0",
            fill: false,
            tension: 0.3
        }]
    }
});
</script>

</body>
</html>