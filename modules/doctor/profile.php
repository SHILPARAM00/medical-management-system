<?php
session_start();
require_once "../../config/db_connect.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'doctor') {
    header("Location: ../../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

/* =========================
   FETCH USER + DOCTOR DATA
========================= */

$data = $conn->query("
    SELECT u.name, u.email, d.specialization, d.experience, d.fee, d.phone
    FROM users u
    LEFT JOIN doctors d ON u.user_id = d.user_id
    WHERE u.user_id = $user_id
")->fetch_assoc();

/* =========================
   UPDATE PROFILE
========================= */

if (isset($_POST['update'])) {

    $name           = $_POST['name'];
    $email          = $_POST['email'];
    $specialization = $_POST['specialization'];
    $experience     = $_POST['experience'];
    $fee            = $_POST['fee'];
    $phone          = $_POST['phone'];

    $conn->query("
        UPDATE users SET
        name = '$name',
        email = '$email'
        WHERE user_id = $user_id
    ");

    $conn->query("
        UPDATE doctors SET
        specialization = '$specialization',
        experience = '$experience',
        fee = '$fee',
        phone = '$phone'
        WHERE user_id = $user_id
    ");

    echo "<script>
        alert('Profile Updated Successfully');
        window.location='profile.php';
    </script>";
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Doctor Profile</title>

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
    padding:30px;
}

/* CARD */
.card{
    background:white;
    padding:30px;
    border-radius:12px;
    box-shadow:0 5px 20px rgba(0,0,0,0.05);
    max-width:700px;
}

h2{
    color:#2b6cb0;
    margin-bottom:20px;
}

.profile-icon{
    width:80px;
    height:80px;
    border-radius:50%;
    background:#2b6cb0;
    color:white;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:30px;
    margin-bottom:20px;
}

/* FORM */
label{
    display:block;
    margin-top:14px;
    margin-bottom:5px;
    font-weight:bold;
}

input{
    width:100%;
    padding:10px;
    border:1px solid #ccc;
    border-radius:8px;
    font-size:14px;
}

/* BUTTON */
.btn{
    margin-top:20px;
    padding:12px 18px;
    border:none;
    background:#2b6cb0;
    color:white;
    border-radius:8px;
    cursor:pointer;
    font-size:15px;
}

.btn:hover{
    background:#1f4f85;
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
        <a href="prescription.php" >💊 Prescriptions</a>
        <a href="profile.php"class="active">👤 Profile</a>
        <a href="../../logout.php">🚪 Logout</a>
    </div>

</div>


<!-- MAIN -->
<div class="main">

<div class="card">

<div class="profile-icon">
<i class="fa fa-user-md"></i>
</div>

<h2>Doctor Profile</h2>

<form method="POST">

<label>Full Name</label>
<input type="text" name="name"
value="<?php echo $data['name']; ?>" required>

<label>Email</label>
<input type="email" name="email"
value="<?php echo $data['email']; ?>" required>

<label>Specialization</label>
<input type="text" name="specialization"
value="<?php echo $data['specialization']; ?>">

<label>Experience (Years)</label>
<input type="number" name="experience"
value="<?php echo $data['experience']; ?>">

<label>Consultation Fee</label>
<input type="number" name="fee"
value="<?php echo $data['fee']; ?>">

<label>Phone</label>
<input type="text" name="phone"
value="<?php echo $data['phone']; ?>">

<button type="submit" name="update" class="btn">
Update Profile
</button>

</form>

</div>

</div>

</div>

</body>
</html>