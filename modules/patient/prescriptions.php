<?php
session_start();
require_once "../../config/db_connect.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit;
}

$patient_id = $_SESSION['user_id'];

/* ==================================================
   FIXED PRESCRIPTIONS + DOCTOR NAME
   Handles doctor_id stored as:
   1) doctors.doctor_id
   2) doctors.user_id
================================================== */

$prescriptions = $conn->query("
    SELECT 
        p.*,

        COALESCE(u1.name, u2.name, 'N/A') AS doctor_name

    FROM prescription p

    /* CASE 1: prescription.doctor_id = doctors.doctor_id */
    LEFT JOIN doctors d1 
        ON p.doctor_id = d1.doctor_id

    LEFT JOIN users u1 
        ON d1.user_id = u1.user_id

    /* CASE 2: prescription.doctor_id = users.user_id directly */
    LEFT JOIN users u2
        ON p.doctor_id = u2.user_id

    WHERE p.patient_id = '$patient_id'

    ORDER BY p.created_at DESC
");
?>

<!DOCTYPE html>
<html>
<head>
<title>My Prescriptions</title>

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
    gap:15px;
    flex-wrap:wrap;
}

.header h2{
    color:#222;
}

/* SEARCH */
.search-box{
    position:relative;
}

.search-box i{
    position:absolute;
    left:12px;
    top:12px;
    color:#888;
}

.search{
    padding:10px 10px 10px 35px;
    width:260px;
    border:1px solid #ccc;
    border-radius:8px;
    outline:none;
}

.search:focus{
    border-color:#2b6cb0;
}

/* GRID */
.grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(300px,1fr));
    gap:20px;
    margin-top:22px;
}

/* CARD */
.card{
    background:#fff;
    padding:20px;
    border-radius:14px;
    box-shadow:0 5px 20px rgba(0,0,0,0.05);
    transition:0.3s;
    border-left:5px solid #2b6cb0;
}

.card:hover{
    transform:translateY(-5px);
}

.title{
    font-size:18px;
    font-weight:bold;
    color:#2b6cb0;
    margin-bottom:12px;
}

.small{
    color:#555;
    font-size:14px;
    margin:6px 0;
}

/* STATUS */
.status{
    display:inline-block;
    margin-top:10px;
    padding:6px 12px;
    border-radius:20px;
    font-size:12px;
    font-weight:bold;
}

.active{
    background:#d4edda;
    color:#155724;
}

.completed{
    background:#d1ecf1;
    color:#0c5460;
}

.stopped{
    background:#f8d7da;
    color:#721c24;
}

.pending{
    background:#fff3cd;
    color:#856404;
}

/* BUTTONS */
.btn-group{
    margin-top:15px;
}

.btn{
    padding:8px 14px;
    border:none;
    border-radius:6px;
    cursor:pointer;
    font-size:13px;
    margin-right:8px;
}

.view{
    background:#2b6cb0;
    color:#fff;
}

.download{
    background:#16a34a;
    color:#fff;
}

/* EMPTY */
.empty{
    margin-top:40px;
    text-align:center;
    color:#777;
    font-size:16px;
}

/* MODAL */
.modal{
    display:none;
    position:fixed;
    top:0;
    left:0;
    width:100%;
    height:100%;
    background:rgba(0,0,0,0.6);
    justify-content:center;
    align-items:center;
    z-index:1000;
}

.modal-content{
    background:#fff;
    width:380px;
    padding:25px;
    border-radius:14px;
    position:relative;
}

.close{
    position:absolute;
    right:15px;
    top:12px;
    cursor:pointer;
    font-size:18px;
    color:#666;
}

.modal-content h3{
    color:#2b6cb0;
    margin-bottom:15px;
}

.modal-content p{
    margin:10px 0;
    color:#444;
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
        <a href="appointments.php" >📅 Appointments</a>
        <a href="prescriptions.php" class="active">💊 Prescriptions</a>
        <a href="bills.php">💰 Bills</a>
        <a href="profile.php">👤 Profile</a>
        <a href="../../logout.php">🚪 Logout</a>
    </div>

</div>

<!-- MAIN -->
<div class="main">

<div class="header">
<h2>My Prescriptions</h2>

<div class="search-box">
<i class="fa fa-search"></i>
<input type="text" class="search" placeholder="Search medicine..." onkeyup="filterCards(this.value)">
</div>

</div>

<div class="grid" id="grid">

<?php if($prescriptions->num_rows > 0): ?>
<?php while($p = $prescriptions->fetch_assoc()): 
$status = strtolower(trim($p['status']));
?>

<div class="card">

<div class="title">💊 <?php echo $p['medicine_name']; ?></div>

<p class="small"><b>Doctor:</b> <?php echo $p['doctor_name']; ?></p>
<p class="small"><b>Dosage:</b> <?php echo $p['dosage']; ?></p>
<p class="small"><b>Duration:</b> <?php echo $p['duration']; ?></p>
<p class="small"><b>Date:</b> <?php echo $p['created_at']; ?></p>

<span class="status <?php echo $status; ?>">
<?php echo ucfirst($status); ?>
</span>

<div class="btn-group">

<button class="btn view" onclick="openModal(
'<?php echo addslashes($p['medicine_name']); ?>',
'<?php echo addslashes($p['dosage']); ?>',
'<?php echo addslashes($p['duration']); ?>',
'<?php echo addslashes($p['doctor_name']); ?>',
'<?php echo ucfirst($status); ?>'
)">
View
</button>

<button class="btn download" onclick="window.print()">
Download
</button>

</div>

</div>

<?php endwhile; ?>
<?php else: ?>
<div class="empty">No prescriptions found</div>
<?php endif; ?>

</div>

</div>

</div>

<!-- MODAL -->
<div class="modal" id="modal">

<div class="modal-content">

<span class="close" onclick="closeModal()">✖</span>

<h3 id="m_name"></h3>

<p><b>Doctor:</b> <span id="m_doc"></span></p>
<p><b>Dosage:</b> <span id="m_dose"></span></p>
<p><b>Duration:</b> <span id="m_dur"></span></p>
<p><b>Status:</b> <span id="m_status"></span></p>

</div>

</div>

<script>

function openModal(name,dose,dur,doc,status){
    document.getElementById("modal").style.display="flex";
    document.getElementById("m_name").innerText = name;
    document.getElementById("m_doc").innerText = doc;
    document.getElementById("m_dose").innerText = dose;
    document.getElementById("m_dur").innerText = dur;
    document.getElementById("m_status").innerText = status;
}

function closeModal(){
    document.getElementById("modal").style.display="none";
}

function filterCards(val){
    val = val.toLowerCase();

    let cards = document.querySelectorAll(".card");

    cards.forEach(card => {
        let text = card.innerText.toLowerCase();
        card.style.display = text.includes(val) ? "block" : "none";
    });
}

window.onclick = function(e){
    if(e.target.id == "modal"){
        closeModal();
    }
}

</script>

</body>
</html>