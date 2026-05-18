<?php
session_start();
require_once "../../config/db_connect.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

$id = $_GET['id'] ?? 0;

/* FETCH PHARMACIST DETAILS */
$data = $conn->query("
    SELECT 
        p.pharmacist_id,
        p.phone,
        u.name,
        u.email,
        u.user_id
    FROM pharmacists p
    JOIN users u ON p.user_id = u.user_id
    WHERE p.pharmacist_id = $id
")->fetch_assoc();

if (!$data) {
    echo "Pharmacist not found";
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Pharmacist Details</title>

<style>
body{
    font-family:Arial;
    background:#f4f7fb;
    padding:30px;
}

.card{
    background:white;
    padding:20px;
    border-radius:12px;
    box-shadow:0 5px 15px rgba(0,0,0,0.1);
    max-width:500px;
}

h2{
    color:#2b6cb0;
}

.label{
    font-weight:bold;
    color:#444;
}

.back{
    display:inline-block;
    margin-top:15px;
    padding:8px 12px;
    background:#2b6cb0;
    color:white;
    text-decoration:none;
    border-radius:6px;
}
</style>
</head>

<body>

<div class="card">

<h2>💊 Pharmacist Details</h2>

<p><span class="label">Name:</span> <?php echo $data['name']; ?></p>
<p><span class="label">Email:</span> <?php echo $data['email']; ?></p>
<p><span class="label">Phone:</span> <?php echo $data['phone']; ?></p>
<p><span class="label">User ID:</span> <?php echo $data['user_id']; ?></p>

<a class="back" href="pharmacists.php">⬅ Back</a>

</div>

</body>
</html>