<?php
session_start();
require_once "../../config/db_connect.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'doctor') {
    header("Location: ../../login.php");
    exit;
}

$doctor_user_id = $_SESSION['user_id'];

$prescription_id = $_GET['id'] ?? 0;

/* =========================
   FETCH PRESCRIPTION
========================= */

$data = $conn->query("
    SELECT p.*, u.name
    FROM prescription p
    JOIN users u ON p.patient_id = u.user_id
    WHERE p.prescription_id = $prescription_id
")->fetch_assoc();

if (!$data) {
    echo "Prescription not found";
    exit;
}

/* =========================
   UPDATE PRESCRIPTION
========================= */

if (isset($_POST['update'])) {

    $medicine_name = $_POST['medicine_name'];
    $dosage          = $_POST['dosage'];
    $duration        = $_POST['duration'];
    $notes           = $_POST['notes'];

    $conn->query("
        UPDATE prescription SET
        medicine_name = '$medicine_name',
        dosage = '$dosage',
        duration = '$duration',
        notes = '$notes'
        WHERE prescription_id = $prescription_id
    ");

    echo "<script>
        alert('Prescription updated successfully');
        window.location='patients.php';
    </script>";
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Edit Prescription</title>

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
    width:230px;
    background:white;
    height:100vh;
    padding:20px;
    box-shadow:2px 0 10px rgba(0,0,0,0.05);
}

.sidebar img{
    width:45px;
}

.sidebar h2{
    color:#2b6cb0;
    font-size:18px;
}

.menu{
    margin-top:20px;
}

.menu a{
    display:block;
    padding:12px;
    margin-bottom:5px;
    text-decoration:none;
    color:#444;
    border-radius:8px;
}

.menu a:hover{
    background:#e6f0fa;
}

/* MAIN */
.main{
    flex:1;
    padding:25px;
}

/* FORM */
.card{
    background:white;
    padding:25px;
    border-radius:12px;
    box-shadow:0 5px 20px rgba(0,0,0,0.05);
    max-width:700px;
}

h2{
    color:#2b6cb0;
}

label{
    display:block;
    margin-top:15px;
    margin-bottom:5px;
    font-weight:bold;
}

input, textarea{
    width:100%;
    padding:10px;
    border:1px solid #ccc;
    border-radius:8px;
    font-size:14px;
}

textarea{
    height:120px;
    resize:none;
}

.btn{
    margin-top:20px;
    background:#2b6cb0;
    color:white;
    border:none;
    padding:12px 20px;
    border-radius:8px;
    cursor:pointer;
    font-size:15px;
}

.btn:hover{
    background:#1f4f85;
}

.patient{
    color:#555;
    margin-bottom:15px;
}

</style>
</head>

<body>

<div class="container">

<!-- SIDEBAR -->
<div class="sidebar">
<img src="../../frontend/assets/images/logo.png.jpeg">
<h2>Doctor Panel</h2>

<div class="menu">
<a href="dashboard.php">🏠 Dashboard</a>
<a href="appointments.php">📅 Appointments</a>
<a href="patients.php">👥 Patients</a>
<a href="prescription.php">💊 Prescriptions</a>
<a href="../../logout.php">🚪 Logout</a>
</div>
</div>

<!-- MAIN -->
<div class="main">

<div class="card">

<h2>✏ Edit Prescription</h2>
<p class="patient">Patient: <b><?php echo $data['name']; ?></b></p>

<form method="POST">

<label>Medicine Name</label>
<input type="text" name="medicine_name"
value="<?php echo $data['medicine_name']; ?>" required>

<label>Dosage</label>
<input type="text" name="dosage"
value="<?php echo $data['dosage']; ?>" required>

<label>Duration</label>
<input type="text" name="duration"
value="<?php echo $data['duration']; ?>" required>

<label>Notes</label>
<textarea name="notes"><?php echo $data['notes']; ?></textarea>

<button type="submit" name="update" class="btn">
Update Prescription
</button>

</form>

</div>

</div>

</div>

</body>
</html>