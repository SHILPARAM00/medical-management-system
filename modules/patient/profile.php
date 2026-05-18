<?php
session_start();
require_once "../../config/db_connect.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$name = $_SESSION['name'];

/* =========================
   FETCH USER + PATIENT DATA
========================= */

$user = $conn->query("
    SELECT u.name, u.email, p.phone, p.gender, p.age, p.address, p.emergency_phone
    FROM users u
    LEFT JOIN patients p ON u.user_id = p.user_id
    WHERE u.user_id = $user_id
")->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
<title>Patient Profile</title>

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

<style>

body{
    margin:0;
    font-family:Arial;
    background:#f4f7fb;
}

/* LAYOUT */
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
    padding:20px;
}

/* HEADER */
.header{
    display:flex;
    justify-content:space-between;
    align-items:center;
}

/* PROFILE CARD */
.profile-card{
    background:white;
    padding:25px;
    border-radius:12px;
    box-shadow:0 5px 20px rgba(0,0,0,0.05);
    margin-top:20px;
    max-width:700px;
}

/* FORM */
.form-group{
    margin-bottom:15px;
}

.form-group label{
    display:block;
    font-weight:bold;
    margin-bottom:5px;
}

.form-group input,
.form-group select{
    width:100%;
    padding:10px;
    border-radius:6px;
    border:1px solid #ddd;
}

/* BUTTON */
.btn{
    background:#2b6cb0;
    color:white;
    padding:10px 20px;
    border:none;
    border-radius:6px;
    cursor:pointer;
}

.btn:hover{
    background:#1e4e8c;
}

/* SUCCESS */
.success{
    color:green;
    margin-top:10px;
}

.error{
    color:red;
    margin-top:10px;
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
        <a href="prescriptions.php">💊 Prescriptions</a>
        <a href="bills.php">💰 Bills</a>
        <a href="profile.php" class="active">👤 Profile</a>
        <a href="../../logout.php">🚪 Logout</a>
    </div>

</div>

<!-- MAIN -->
<div class="main">

<div class="header">
<h2>👤 My Profile</h2>
<!-- ADD THIS HERE -->
<?php if(isset($_GET['update']) && $_GET['update'] == "success"): ?>
    <p style="color:green; font-weight:bold;">Profile updated successfully!</p>
<?php elseif(isset($_GET['update']) && $_GET['update'] == "error"): ?>
    <p style="color:red; font-weight:bold;">Update failed!</p>
<?php endif; ?>
</div>

<div class="profile-card">

<form id="profileForm">

<div class="form-group">
<label>Full Name</label>
<input type="text" id="name" value="<?php echo $user['name']; ?>">
</div>

<div class="form-group">
<label>Email</label>
<input type="email" value="<?php echo $user['email']; ?>" disabled>
</div>

<div class="form-group">
<label>Phone</label>
<input type="text" id="phone" value="<?php echo $user['phone']; ?>">
</div>

<div class="form-group">
<label>Emergency Phone</label>
<input type="text" id="emergency_phone" value="<?php echo $user['emergency_phone']; ?>">
</div>

<div class="form-group">
<label>Gender</label>
<select id="gender">
    <option value="male" <?php if($user['gender']=="male") echo "selected"; ?>>Male</option>
    <option value="female" <?php if($user['gender']=="female") echo "selected"; ?>>Female</option>
    <option value="other" <?php if($user['gender']=="other") echo "selected"; ?>>Other</option>
</select>
</div>

<div class="form-group">
<label>Age</label>
<input type="number" id="age" value="<?php echo $user['age']; ?>">
</div>

<div class="form-group">
<label>Address</label>
<input type="text" id="address" value="<?php echo $user['address']; ?>">
</div>

<button type="button" class="btn" onclick="updateProfile()">Update Profile</button>

<div id="msg"></div>

</form>

</div>

</div>

</div>

<script>

function updateProfile(){

    let data = {
        name: document.getElementById("name").value,
        phone: document.getElementById("phone").value,
        emergency_phone: document.getElementById("emergency_phone").value,
        gender: document.getElementById("gender").value,
        age: document.getElementById("age").value,
        address: document.getElementById("address").value
    };

    fetch("../../backend/patient/updateprofile.php",{
        method:"POST",
        headers:{"Content-Type":"application/json"},
        body:JSON.stringify(data)
    })
    .then(res=>res.json())
    .then(res=>{
        if(res.status==="success"){
            document.getElementById("msg").innerHTML = "<p class='success'>Profile updated successfully</p>";
        } else {
            document.getElementById("msg").innerHTML = "<p class='error'>"+res.message+"</p>";
        }
    });
}

</script>

</body>
</html>