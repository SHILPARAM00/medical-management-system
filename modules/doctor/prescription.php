<?php
session_start();
require_once "../../config/db_connect.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit;
}

$doctor_id = $_SESSION['user_id'];
$name = $_SESSION['name'];

// FETCH DATA
$prescriptions = $conn->query("
    SELECT p.*, u.name as patient_name
    FROM prescription p
    JOIN users u ON p.patient_id = u.user_id
    WHERE p.doctor_id = $doctor_id
    ORDER BY p.created_at DESC
");
?>

<!DOCTYPE html>
<html>
<head>
<title>Prescriptions</title>

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

<style>

body{margin:0;font-family:Arial;background:#f4f7fb;}
.container{display:flex;}

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
.main{flex:1;padding:20px;}

/* HEADER */
.header{
    display:flex;justify-content:space-between;align-items:center;
}
.avatar{
    width:35px;height:35px;background:#2b6cb0;color:white;
    display:flex;align-items:center;justify-content:center;border-radius:50%;
}

/* FILTER */
.filter{
    margin-top:20px;display:flex;gap:10px;flex-wrap:wrap;
}
.filter input,.filter select{
    padding:10px;border-radius:6px;border:1px solid #ccc;
}

/* CARD */
.card{
    background:white;margin-top:20px;padding:20px;
    border-radius:10px;
}

/* TABLE */
table{width:100%;border-collapse:collapse;}
th,td{padding:10px;border-bottom:1px solid #eee;}
th{background:#2b6cb0;color:white;}

/* BUTTON */
.btn{
    padding:6px 10px;border:none;border-radius:5px;cursor:pointer;
}
.add{background:#2b6cb0;color:white;}
.edit{background:#f6ad55;color:white;}
.delete{background:#e53e3e;color:white;}

/* MODAL */
.modal{
    display:none;position:fixed;top:0;left:0;width:100%;height:100%;
    background:rgba(0,0,0,0.5);
    justify-content:center;align-items:center;
}
.modal-box{
    background:white;padding:20px;border-radius:10px;width:350px;
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
        <a href="patients.php">👥 Patients</a>
        <a href="prescription.php" class="active">💊 Prescriptions</a>
        <a href="profile.php">👤 Profile</a>
        <a href="../../logout.php">🚪 Logout</a>
    </div>

</div>

<!-- MAIN -->
<div class="main">

<div class="header">
<h2>💊 Prescriptions</h2>
<div class="avatar"><?php echo strtoupper($name[0]); ?></div>
</div>

<!-- FILTER -->
<div class="filter">
<input type="text" id="patientFilter" placeholder="Patient ID">

<select id="statusFilter">
<option value="">All</option>
<option value="active">Active</option>
<option value="completed">Completed</option>
</select>

<button class="btn add" onclick="goAdd()">+ Add</button>
</div>

<!-- TABLE -->
<div class="card">
<table id="table">

<tr>
<th>ID</th>
<th>Patient</th>
<th>Medicine</th>
<th>Dosage</th>
<th>Duration</th>
<th>Status</th>
<th>Date</th>
<th>Action</th>
</tr>

<?php while($p = $prescriptions->fetch_assoc()): ?>

<tr>
<td><?php echo $p['prescription_id']; ?></td>
<td><?php echo $p['patient_name']; ?></td>
<td><?php echo $p['medicine_name']; ?></td>
<td><?php echo $p['dosage']; ?></td>
<td><?php echo $p['duration']; ?></td>
<td><?php echo $p['status']; ?></td>
<td><?php echo $p['created_at']; ?></td>

<td>
<button class="btn edit"
onclick="openEdit(
<?php echo $p['prescription_id']; ?>,
'<?php echo $p['medicine_name']; ?>',
'<?php echo $p['dosage']; ?>',
'<?php echo $p['duration']; ?>',
'<?php echo $p['status']; ?>'
)">Edit</button>
</td>

</tr>

<?php endwhile; ?>

</table>
</div>

</div>
</div>

<!-- EDIT MODAL -->
<div class="modal" id="modal">
<div class="modal-box">

<h3>Edit Prescription</h3>

<input type="hidden" id="id">
<input type="text" id="medicine" placeholder="Medicine"><br><br>
<input type="text" id="dosage" placeholder="Dosage"><br><br>
<input type="text" id="duration" placeholder="Duration"><br><br>

<select id="status">
<option value="active">Active</option>
<option value="completed">Completed</option>
</select>

<br><br>

<button class="btn add" onclick="update()">Update</button>
<button onclick="closeModal()">Cancel</button>

</div>
</div>

<script>

/* FILTER */
document.getElementById("patientFilter").addEventListener("keyup", filter);
document.getElementById("statusFilter").addEventListener("change", filter);

function filter(){
let rows=document.querySelectorAll("#table tr");

let patient=document.getElementById("patientFilter").value.toLowerCase();
let status=document.getElementById("statusFilter").value;

rows.forEach((r,i)=>{
if(i===0)return;

let text=r.innerText.toLowerCase();

if(text.includes(patient) && (!status || text.includes(status))){
r.style.display="";
}else{
r.style.display="none";
}
});
}

/* NAV */
function goAdd(){
window.location.href="add_prescription.php";
}

/* MODAL */
function openEdit(id,med,dos,stat){
document.getElementById("modal").style.display="flex";
document.getElementById("id").value=id;
document.getElementById("medicine").value=med;
document.getElementById("dosage").value=dos;
document.getElementById("duration").value=dur;
document.getElementById("status").value=stat;
}

function closeModal(){
document.getElementById("modal").style.display="none";
}

/* UPDATE */
function update(){

let data={
id:document.getElementById("id").value,
medicine:document.getElementById("medicine").value,
dosage:document.getElementById("dosage").value,
duration:document.getElementById("duration").value,
status:document.getElementById("status").value
};

fetch("../../backend/doctor/update_prescription.php",{
method:"POST",
headers:{"Content-Type":"application/json"},
body:JSON.stringify(data)
})
.then(()=>location.reload());

}

</script>

</body>
</html>