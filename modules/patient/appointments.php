<?php
session_start();
require_once "../../config/db_connect.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit;
}

$patient_id = $_SESSION['user_id'];

/* FETCH APPOINTMENTS */
$stmt = $conn->prepare("
    SELECT 
        a.*,
        COALESCE(doc.name, a.doctor_name) AS doctor_name,
        COALESCE(d.specialization, a.specialization) AS specialization

    FROM appointment a
    LEFT JOIN doctors d ON a.doctor_id = d.doctor_id
    LEFT JOIN users doc ON d.user_id = doc.user_id

    WHERE a.patient_id = ?
    ORDER BY a.appointment_date DESC, a.appointment_time DESC
");

$stmt->bind_param("i", $patient_id);
$stmt->execute();
$appointments = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
<title>My Appointments</title>

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

/* TABLE */
.table{
    width:100%;
    background:white;
    margin-top:20px;
    border-radius:12px;
    overflow:hidden;
    box-shadow:0 5px 20px rgba(0,0,0,0.05);
}

table{
    width:100%;
    border-collapse:collapse;
}

th, td{
    padding:14px;
    text-align:left;
}

th{
    background:#2b6cb0;
    color:white;
}

tr:nth-child(even){
    background:#f9fbff;
}

/* STATUS BADGE */
.status{
    padding:6px 12px;
    border-radius:20px;
    font-size:12px;
    font-weight:bold;
    display:inline-block;
}

/* Better Visible Colors */
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

/* BUTTON */
.btn{
    padding:6px 12px;
    border:none;
    border-radius:5px;
    cursor:pointer;
    font-size:13px;
    text-decoration:none;
    display:inline-block;
}

.view{
    background:#2b6cb0;
    color:white;
}

.cancel{
    background:red;
    color:white;
}

/* TOP BUTTON */
.top-btn{
    background:#2b6cb0;
    color:white;
    padding:10px 15px;
    border:none;
    border-radius:6px;
    cursor:pointer;
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
        <a href="#" >🏠 Dashboard</a>
        <a href="appointments.php" class="active">📅 Appointments</a>
        <a href="prescriptions.php">💊 Prescriptions</a>
        <a href="bills.php">💰 Bills</a>
        <a href="profile.php">👤 Profile</a>
        <a href="../../logout.php">🚪 Logout</a>
    </div>

</div>

<!-- MAIN -->
<div class="main">

<!-- HEADER -->
<div class="header">
<h2>My Appointments</h2>

<button class="top-btn" onclick="go('book.php')">
<i class="fa fa-plus"></i> Book Appointment
</button>
</div>

<!-- TABLE -->
<div class="table">

<table>

<tr>
<th>ID</th>
<th>Doctor</th>
<th>Specialization</th>
<th>Date</th>
<th>Time</th>
<th>Status</th>
<th>Action</th>
</tr>

<?php if($appointments->num_rows > 0): ?>

<?php while($a = $appointments->fetch_assoc()): 
$status = strtolower(trim($a['status']));
?>

<tr>
<td><?php echo $a['appointment_id']; ?></td>
<td><?php echo $a['doctor_name']; ?></td>
<td><?php echo $a['specialization']; ?></td>
<td><?php echo $a['appointment_date']; ?></td>
<td><?php echo $a['appointment_time']; ?></td>

<td>
<span class="status <?php echo $status; ?>">
<?php echo ucfirst($status); ?>
</span>
</td>

<td>

<a href="view_appointment.php?id=<?php echo $a['appointment_id']; ?>" class="btn view">
View
</a>

<?php if($status != "cancelled" && $status != "completed"): ?>
<button class="btn cancel" onclick="cancelAppointment(<?php echo $a['appointment_id']; ?>)">
Cancel
</button>
<?php endif; ?>

</td>

</tr>

<?php endwhile; ?>

<?php else: ?>

<tr>
<td colspan="7" style="text-align:center;">No appointments found</td>
</tr>

<?php endif; ?>

</table>

</div>

</div>

</div>

<script>

function go(page){
    window.location.href = page;
}

function cancelAppointment(id){

    if(confirm("Cancel this appointment?")){

        fetch("../../backend/patient/cancelappointment.php",{
            method:"POST",
            headers:{
                "Content-Type":"application/json"
            },
            body:JSON.stringify({
                appointment_id:id
            })
        })
        .then(res => res.json())
        .then(data => {
            alert(data.message);
            location.reload();
        });

    }
}

</script>

</body>
</html>