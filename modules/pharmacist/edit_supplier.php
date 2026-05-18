<?php
session_start();
require_once "../../config/db_connect.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'pharmacist') {
    header("Location: ../../login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: suppliers.php");
    exit;
}

$id = $_GET['id'];

/* FETCH SUPPLIER */
$supplier = $conn->query("SELECT * FROM suppliers WHERE supplier_id = $id")->fetch_assoc();

if (!$supplier) {
    die("Supplier not found");
}

/* UPDATE */
if (isset($_POST['update'])) {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];

    $conn->query("
        UPDATE suppliers 
        SET name='$name', phone='$phone', address='$address'
        WHERE supplier_id=$id
    ");

    header("Location: suppliers.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Edit Supplier</title>

<style>
body{
    font-family:Arial;
    background:#f4f7fb;
    padding:30px;
}

.card{
    background:white;
    max-width:500px;
    margin:auto;
    padding:25px;
    border-radius:12px;
    box-shadow:0 5px 15px rgba(0,0,0,0.05);
}

input, textarea{
    width:100%;
    padding:10px;
    margin:10px 0;
    border:1px solid #ccc;
    border-radius:8px;
    box-sizing:border-box;
}

button{
    background:#2b6cb0;
    color:white;
    border:none;
    padding:10px 20px;
    border-radius:8px;
    cursor:pointer;
}
</style>
</head>

<body>

<div class="card">
<h2>Edit Supplier</h2>

<form method="POST">

<label>Supplier Name</label>
<input type="text" name="name" value="<?php echo $supplier['name']; ?>" required>

<label>Phone</label>
<input type="text" name="phone" value="<?php echo $supplier['phone']; ?>" required>

<label>Address</label>
<textarea name="address" required><?php echo $supplier['address']; ?></textarea>

<button type="submit" name="update">Update Supplier</button>

</form>
</div>

</body>
</html>