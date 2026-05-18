<?php
session_start();
require_once "../../config/db_connect.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'pharmacist') {
    header("Location: ../../login.php");
    exit;
}

$name = $_SESSION['name'];
$currentPage = basename($_SERVER['PHP_SELF']);

$id = $_GET['id'];

// FETCH DATA
$data = $conn->query("SELECT * FROM medicines WHERE medicine_id=$id")->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
<title>Edit Medicine</title>

<style>
body{margin:0;font-family:Arial;background:#f4f7fb;}
.container{display:flex}

/* SIDEBAR */
.sidebar{
    width:230px;background:white;height:100vh;padding:20px;
    box-shadow:2px 0 10px rgba(0,0,0,0.05);
}
.sidebar img{width:50px}
.menu a{
    display:block;padding:12px;margin:5px 0;
    text-decoration:none;color:#444;border-radius:8px;
}
.menu a:hover{background:#e6f0fa}

/* MAIN */
.main{flex:1;padding:20px}

/* FORM */
.card{
    background:white;padding:20px;border-radius:12px;
    margin-top:20px;max-width:500px;
}

input{
    width:100%;padding:10px;margin:10px 0;
}

button{
    padding:10px 20px;background:#2b6cb0;color:white;border:none;border-radius:6px;
}
</style>
</head>

<body>

<div class="container">

<div class="sidebar">
<img src="../../frontend/assets/images/logo.png.jpeg">
<h3>Pharmacist Panel</h3>

<div class="menu">
<a href="dashboard.php">🏠 Dashboard</a>
<a href="medicines.php">💊 Medicines</a>
</div>
</div>

<div class="main">

<h2>Edit Medicine</h2>
<h3><?php echo $name; ?></h3>

<div class="card">

<form method="POST" action="../../backend/medicines/update_medicine.php">

<input type="hidden" name="medicine_id" value="<?php echo $data['medicine_id']; ?>">

<input type="text" name="name" value="<?php echo $data['name']; ?>" required>

<input type="number" name="price" value="<?php echo $data['price']; ?>" required>

<input type="number" name="stock" value="<?php echo $data['stock']; ?>" required>

<input type="date" name="expiry_date" value="<?php echo $data['expiry_date']; ?>" required>

<button type="submit">Update Medicine</button>

</form>

</div>

</div>
</div>

</body>
</html>