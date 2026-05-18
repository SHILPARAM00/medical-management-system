<?php
session_start();
require_once "../../config/db_connect.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'doctor') {
    header("Location: ../../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

/* GET REAL doctor_id */
$getDoctor = $conn->query("
    SELECT doctor_id
    FROM doctors
    WHERE user_id = $user_id
");

$doctor = $getDoctor->fetch_assoc();
$doctor_id = $doctor['doctor_id'];

/* FETCH APPOINTMENTS */
$appointments = $conn->query("
    SELECT a.*, u.name as patient_name
    FROM appointment a
    LEFT JOIN users u ON a.patient_id = u.user_id
    WHERE a.doctor_id = $doctor_id
    ORDER BY a.appointment_date ASC, a.appointment_time ASC
");
?>

<!DOCTYPE html>
<html>
<head>
<title>Doctor Appointments</title>

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

<style>
body{
    margin:0;
    font-family:Arial;
    background:#f4f7fb;
}

.container{
    display:flex;
}

/* SIDEBAR SAME AS DASHBOARD */
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
    padding:20px;
}

/* HEADER */
.header{
    display:flex;
    justify-content:space-between;
    align-items:center;
}

/* TABLE BOX */
.table{
    margin-top:20px;
    background:white;
    border-radius:12px;
    padding:20px;
    box-shadow:0 5px 20px rgba(0,0,0,0.05);
}

/* TABLE */
table{
    width:100%;
    border-collapse:collapse;
}

th, td{
    padding:12px;
    text-align:left;
    border-bottom:1px solid #eee;
}

th{
    color:#2b6cb0;
    background:#f8fbff;
}

/* STATUS */
.badge{
    padding:6px 10px;
    border-radius:20px;
    font-size:12px;
    font-weight:bold;
}

.confirmed{
    background:#d4edda;
    color:#155724;
}

.pending{
    background:#fff3cd;
    color:#856404;
}

.cancelled{
    background:#f8d7da;
    color:#721c24;
}

.completed{
    background:#d1ecf1;
    color:#0c5460;
}

/* BUTTONS */
.btn{
    padding:6px 10px;
    border:none;
    border-radius:5px;
    cursor:pointer;
    margin:2px;
    font-size:12px;
}

.btn-green{ background:#28a745; color:white; }
.btn-blue{ background:#2b6cb0; color:white; }
.btn-red{ background:#dc3545; color:white; }

.empty{
    text-align:center;
    padding:30px;
    color:#777;
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
        <a href="dashboard.php">🏠 Dashboard</a>
        <a href="appointments.php" class="active">📅 Appointments</a>
        <a href="patients.php">👥 Patients</a>
        <a href="prescription.php">💊 Prescriptions</a>
        <a href="profile.php">👤 Profile</a>
        <a href="../../logout.php">🚪 Logout</a>
    </div>

</div>

<!-- MAIN -->
<div class="main">

<div class="header">
<h2>Appointment Management</h2>
</div>

<div class="table">

<table>

<tr>
<th>Patient</th>
<th>Patient ID</th>
<th>Date</th>
<th>Time</th>
<th>Status</th>
<th>Action</th>
</tr>

<?php if($appointments->num_rows > 0): ?>
<?php while($row = $appointments->fetch_assoc()): ?>

<tr>
<td><?php echo $row['patient_name'] ?? 'Unknown'; ?></td>
<td><?php echo $row['patient_id']; ?></td>
<td><?php echo $row['appointment_date']; ?></td>
<td><?php echo $row['appointment_time']; ?></td>

<td>
<span class="badge <?php echo strtolower($row['status']); ?>">
<?php echo ucfirst($row['status']); ?>
</span>
</td>

<td>
<button class="btn btn-green" onclick="updateStatus(<?php echo $row['appointment_id']; ?>,'confirmed')">✔</button>

<button class="btn btn-blue" onclick="updateStatus(<?php echo $row['appointment_id']; ?>,'completed')">Done</button>

<button class="btn btn-red" onclick="updateStatus(<?php echo $row['appointment_id']; ?>,'cancelled')">✖</button>
</td>
</tr>

<?php endwhile; ?>

<?php else: ?>

<tr>
<td colspan="6" class="empty">No appointments found</td>
</tr>

<?php endif; ?>

</table>

</div>

</div>
</div>

<script>
function updateStatus(id,status){
    fetch("../../backend/doctor/updateAppointment.php",{
        method:"POST",
        headers:{
            "Content-Type":"application/json"
        },
        body:JSON.stringify({
            id:id,
            status:status
        })
    })
    .then(res=>res.json())
    .then(data=>{
        if(data.status==="success"){
            location.reload();
        }else{
            alert(data.message);
        }
    });
}
</script>

</body>
</html>