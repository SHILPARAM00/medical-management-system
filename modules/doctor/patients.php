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

/* FETCH PATIENTS */
$patients = $conn->query("
SELECT DISTINCT
u.user_id,
u.name,
p.gender,
p.phone,
MAX(a.appointment_date) AS last_visit,

(
SELECT COUNT(*)
FROM prescription pr
WHERE pr.patient_id = u.user_id
AND pr.doctor_id = $doctor_id
) AS prescription_count

FROM appointment a

JOIN users u ON a.patient_id = u.user_id

LEFT JOIN patients p ON p.user_id = u.user_id

WHERE a.doctor_id = $doctor_id

GROUP BY u.user_id,u.name,p.gender,p.phone

ORDER BY last_visit DESC
");
?>

<!DOCTYPE html>
<html>
<head>
<title>Doctor Patients</title>

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
    text-decoration:none;
    color:#333;
    border-radius:8px;
    margin-bottom:8px;
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

.header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    flex-wrap:wrap;
    gap:10px;
}

.search{
    padding:10px;
    border-radius:8px;
    border:1px solid #ccc;
    width:260px;
}

/* TABLE */
.table{
    margin-top:20px;
    background:white;
    border-radius:12px;
    overflow:hidden;
    box-shadow:0 5px 20px rgba(0,0,0,0.05);
}

table{
    width:100%;
    border-collapse:collapse;
}

th,td{
    padding:15px;
    text-align:left;
    border-bottom:1px solid #eee;
}

th{
    background:#2b6cb0;
    color:white;
}

.badge{
    padding:5px 10px;
    border-radius:20px;
    font-size:12px;
    color:white;
}

.male{background:#3182ce;}
.female{background:#d53f8c;}
.other{background:#666;}

/* BUTTONS */
.btn{
    padding:7px 12px;
    border:none;
    border-radius:6px;
    cursor:pointer;
    font-size:12px;
    margin-right:5px;
    margin-bottom:4px;
}

.view{
    background:#38a169;
    color:white;
}

.pres{
    background:#2b6cb0;
    color:white;
}

.edit{
    background:#ed8936;
    color:white;
}

.empty{
    text-align:center;
    padding:20px;
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
        <a href="appointments.php">📅 Appointments</a>
        <a href="patients.php" class="active">👥 Patients</a>
        <a href="prescription.php">💊 Prescriptions</a>
        <a href="profile.php">👤 Profile</a>
        <a href="../../logout.php">🚪 Logout</a>
    </div>

</div>

<!-- MAIN -->
<div class="main">

<div class="header">
    <h2>👥 My Patients</h2>

    <input type="text"
    id="search"
    class="search"
    placeholder="Search patient..."
    onkeyup="filterPatients()">
</div>

<div class="table">

<table id="patientTable">

<tr>
<th>Name</th>
<th>Gender</th>
<th>Phone</th>
<th>Last Visit</th>
<th>Actions</th>
</tr>

<?php if($patients->num_rows > 0): ?>
<?php while($p = $patients->fetch_assoc()): ?>

<tr>

<td><?php echo $p['name']; ?></td>

<td>
<span class="badge <?php echo strtolower($p['gender']); ?>">
<?php echo ucfirst($p['gender']); ?>
</span>
</td>

<td><?php echo $p['phone'] ? $p['phone'] : 'N/A'; ?></td>

<td><?php echo $p['last_visit']; ?></td>

<td>
<button class="btn view"
onclick="viewPatient(<?php echo $p['user_id']; ?>)">
View
</button>

<?php if($p['prescription_count'] > 0): ?>
<button class="btn edit"
onclick="editPrescription(<?php echo $p['user_id']; ?>)">
Update Prescription
</button>
<?php else: ?>
<button class="btn pres"
onclick="addPrescription(<?php echo $p['user_id']; ?>)">
Add Prescription
</button>
<?php endif; ?>

</td>

</tr>

<?php endwhile; ?>
<?php else: ?>

<tr>
<td colspan="5" class="empty">No patients found</td>
</tr>

<?php endif; ?>

</table>

</div>

</div>
</div>

<script>
function filterPatients(){
    let input = document.getElementById("search").value.toLowerCase();
    let rows = document.querySelectorAll("#patientTable tr");

    rows.forEach((row,index)=>{
        if(index===0) return;
        let text = row.innerText.toLowerCase();
        row.style.display = text.includes(input) ? "" : "none";
    });
}

function viewPatient(id){
    window.location.href = "patientview.php?id=" + id;
}

function addPrescription(id){
    window.location.href = "add_prescription.php?patient_id=" + id;
}

function editPrescription(id){
    window.location.href = "edit_prescription.php?patient_id=" + id;
}
</script>

</body>
</html>