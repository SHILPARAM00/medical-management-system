<?php
session_start();
require_once "../../config/db_connect.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'pharmacist') {
    header("Location: ../../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$name = $_SESSION['name'];
$currentPage = basename($_SERVER['PHP_SELF']);

// FETCH USER DATA
$user = $conn->query("SELECT * FROM users WHERE user_id = $user_id")->fetch_assoc();

// FETCH PHARMACIST EXTRA DATA
$pharmacist = $conn->query("SELECT * FROM pharmacists WHERE user_id = $user_id")->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
<title>Profile</title>

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
    width:50px;
}
.menu a{
    display:block;
    padding:12px;
    margin:5px 0;
    text-decoration:none;
    color:#444;
    border-radius:8px;
}
.menu a:hover{
    background:#e6f0fa;
}
.active{
    background:#e6f0fa;
}

/* MAIN */
.main{
    flex:1;
    padding:20px;
}

/* PROFILE CARD */
.card{
    background:white;
    padding:25px;
    border-radius:12px;
    max-width:600px;
    margin-top:20px;
    box-shadow:0 5px 15px rgba(0,0,0,0.05);
}

.profile-top{
    text-align:center;
    margin-bottom:20px;
}

.avatar{
    width:90px;
    height:90px;
    border-radius:50%;
    background:#2b6cb0;
    color:white;
    font-size:35px;
    display:flex;
    align-items:center;
    justify-content:center;
    margin:auto;
}

.profile-top h2{
    margin:10px 0 5px;
}

.profile-top p{
    color:#666;
}

/* FORM */
label{
    font-weight:bold;
    display:block;
    margin-top:12px;
}

input{
    width:100%;
    padding:10px;
    margin-top:6px;
    border:1px solid #ccc;
    border-radius:8px;
    box-sizing:border-box;
}

button{
    margin-top:20px;
    padding:12px 20px;
    background:#2b6cb0;
    color:white;
    border:none;
    border-radius:8px;
    cursor:pointer;
    font-size:14px;
}

button:hover{
    background:#1f4f82;
}
</style>
</head>

<body>

<div class="container">

<!-- SIDEBAR -->
<div class="sidebar">
<img src="../../frontend/assets/images/logo.png.jpeg">

<h3>Pharmacist Panel</h3>

<div class="menu">
<a href="dashboard.php" >🏠 Dashboard</a>
<a href="medicines.php">💊 Medications</a>
<a href="prescriptions.php" >📄 Prescriptions</a>
<a href="inventory.php" >📦 Inventory</a>
<a href="purchase_orders.php">🛒 Purchase Orders</a>
<a href="suppliers.php">🚚 Suppliers</a>
<a href="reports.php">📊 Reports</a>
<a href="profile.php"  class="active">👤 Profile</a>
<a href="../../logout.php">🚪 Logout</a>
</div>
</div>

<!-- MAIN -->
<div class="main">

<h2>👤 My Profile</h2>

<div class="card">

<div class="profile-top">
<div class="avatar">
<?php echo strtoupper(substr($user['name'], 0, 1)); ?>
</div>
<h2><?php echo $user['name']; ?></h2>
<p>Pharmacist</p>
</div>

<form method="POST" action="../../backend/pharmacist/update_profile.php">

<label>Name</label>
<input type="text" name="name" value="<?php echo $user['name']; ?>" required>

<label>Email</label>
<input type="email" name="email" value="<?php echo $user['email']; ?>" required>

<label>Phone</label>
<input type="text" name="phone" value="<?php echo $pharmacist['phone'] ?? ''; ?>">

<label>New Password</label>
<input type="password" name="password" placeholder="Leave blank if no change">

<button type="submit">Update Profile</button>

</form>

</div>

</div>
</div>

</body>
</html>