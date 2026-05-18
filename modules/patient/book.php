<?php
session_start();
require_once "../../config/db_connect.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
    header("Location: ../../login.php");
    exit;
}

$patient_id = $_SESSION['user_id'];

/* ================= BOOK APPOINTMENT ================= */
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $doctor_id = $_POST['doctor_id'];
    $department = $_POST['department'];
    $appointment_date = $_POST['appointment_date'];
    $appointment_time = $_POST['appointment_time'];
    $problem = $_POST['problem'];

    /* ================= PAST DATE CHECK ================= */
    $today = date("Y-m-d");

    if (strtotime($appointment_date) < strtotime($today)) {
        $_SESSION['error'] = "You cannot book past dates.";
        header("Location: book.php");
        exit;
    }

    /* ================= VALIDATE DOCTOR ================= */
    $check = $conn->prepare("SELECT doctor_id FROM doctors WHERE doctor_id=?");
    $check->bind_param("i", $doctor_id);
    $check->execute();

    if ($check->get_result()->num_rows == 0) {
        $_SESSION['error'] = "Invalid doctor selected";
        header("Location: book.php");
        exit;
    }

    /* ================= SLOT CHECK ================= */
    $slotCheck = $conn->prepare("
        SELECT appointment_id 
        FROM appointment 
        WHERE doctor_id=? 
        AND appointment_date=? 
        AND appointment_time=?
    ");
    $slotCheck->bind_param("iss", $doctor_id, $appointment_date, $appointment_time);
    $slotCheck->execute();

    if ($slotCheck->get_result()->num_rows > 0) {
        $_SESSION['error'] = "Slot already booked. Choose another time.";
        header("Location: book.php");
        exit;
    }

    /* ================= TOKEN GENERATION ================= */
    $tokenStmt = $conn->prepare("
        SELECT COALESCE(MAX(token_no),0)+1 AS next_token
        FROM appointment
        WHERE doctor_id=? AND appointment_date=?
    ");
    $tokenStmt->bind_param("is", $doctor_id, $appointment_date);
    $tokenStmt->execute();
    $token_no = $tokenStmt->get_result()->fetch_assoc()['next_token'];

    /* ================= INSERT (CLEAN VERSION) ================= */
    $insert = $conn->prepare("
        INSERT INTO appointment
        (patient_id, doctor_id, appointment_date, appointment_time, problem, status, token_no)
        VALUES (?, ?, ?, ?, ?, 'pending', ?)
    ");

    $insert->bind_param(
        "iisssi",
        $patient_id,
        $doctor_id,
        $appointment_date,
        $appointment_time,
        $problem,
        $token_no
    );

    if ($insert->execute()) {
        $_SESSION['success'] = "Appointment booked successfully. Token No: $token_no";
        header("Location: appointments.php");
        exit;
    } else {
        $_SESSION['error'] = "Booking failed";
        header("Location: book.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Book Appointment</title>

<style>
body{
    margin:0;
    font-family:Arial;
    background:#f4f7fb;
}

.container{ display:flex; }

/* SIDEBAR */
.sidebar{
    width:230px;
    background:white;
    height:100vh;
    padding:20px;
    box-shadow:2px 0 10px rgba(0,0,0,0.05);
}

.sidebar img{ width:45px; }

.menu a{
    display:block;
    padding:12px;
    text-decoration:none;
    color:#444;
    border-radius:8px;
    margin-bottom:5px;
}

.menu a:hover{ background:#e6f0fa; }

/* MAIN */
.main{ flex:1; padding:20px; }

.card{
    background:white;
    padding:25px;
    border-radius:12px;
    width:550px;
    box-shadow:0 5px 20px rgba(0,0,0,0.05);
}

input, select, textarea{
    width:100%;
    padding:10px;
    margin:10px 0;
    border:1px solid #ddd;
    border-radius:8px;
}

button{
    width:100%;
    padding:12px;
    background:#2b6cb0;
    color:white;
    border:none;
    border-radius:8px;
    cursor:pointer;
}

button:hover{ background:#1e4e85; }

.error{ color:red; }
.success{ color:green; }
</style>
</head>

<body>

<div class="container">

<!-- SIDEBAR -->
<div class="sidebar">
    <img src="../../frontend/assets/images/logo.png.jpeg">
    <h3>MedSync</h3>

    <div class="menu">
        <a href="dashboard.php">🏠 Dashboard</a>
        <a href="appointments.php">📅 Appointments</a>
        <a href="prescriptions.php">💊 Prescriptions</a>
        <a href="bills.php">💰 Bills</a>
        <a href="profile.php">👤 Profile</a>
        <a href="../../logout.php">🚪 Logout</a>
    </div>
</div>

<!-- MAIN -->
<div class="main">

<div class="card">

<h2>Book Appointment</h2>

<?php
if(isset($_SESSION['error'])){
    echo "<p class='error'>".$_SESSION['error']."</p>";
    unset($_SESSION['error']);
}
if(isset($_SESSION['success'])){
    echo "<p class='success'>".$_SESSION['success']."</p>";
    unset($_SESSION['success']);
}
?>

<form method="POST">

<label>Department</label>
<select name="department" id="department" required>
    <option value="">Select Department</option>
    <option value="Cardiology">Cardiology</option>
    <option value="ENT">ENT</option>
    <option value="Neurology">Neurology</option>
    <option value="Orthopedics">Orthopedics</option>
    <option value="Dermatology">Dermatology</option>
    <option value="General">General</option>
</select>

<label>Doctor</label>
<select name="doctor_id" id="doctor" required>
    <option value="">Select Doctor</option>
</select>

<label>Date</label>
<input type="date" name="appointment_date" id="date" required min="<?= date('Y-m-d') ?>">

<label>Available Slots</label>
<select name="appointment_time" id="slots" required>
    <option value="">Select Slot</option>
</select>

<label>Problem</label>
<textarea name="problem"></textarea>

<button type="submit">Book Appointment</button>

</form>

</div>

</div>
</div>

<script>

/* LOAD DOCTORS */
document.getElementById("department").addEventListener("change", function(){

    let dept = this.value;

    fetch("get_doctors.php?department=" + dept)
    .then(res => res.json())
    .then(data => {

        let doctorBox = document.getElementById("doctor");
        doctorBox.innerHTML = "<option value=''>Select Doctor</option>";

        data.forEach(doc => {
            doctorBox.innerHTML += `
                <option value="${doc.doctor_id}">
                    Dr. ${doc.name} (${doc.specialization})
                </option>`;
        });

    });

});


/* LOAD SLOTS */
function loadSlots(){
    let doctor = document.getElementById("doctor").value;
    let date = document.getElementById("date").value;

    if(doctor && date){

        fetch("get_slots.php?doctor_id="+doctor+"&date="+date)
        .then(res => res.json())
        .then(data => {

            let slotBox = document.getElementById("slots");
            slotBox.innerHTML = "<option value=''>Select Slot</option>";

            data.forEach(slot => {
                let value = typeof slot === "string" ? slot : slot.slot_time;
                slotBox.innerHTML += `<option value="${value}">${value}</option>`;
            });

        });

    }
}

document.getElementById("doctor").addEventListener("change", loadSlots);
document.getElementById("date").addEventListener("change", loadSlots);

</script>

</body>
</html>