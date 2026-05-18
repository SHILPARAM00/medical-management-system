<?php
session_start();
require_once "../../config/db_connect.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'pharmacist') {
    header("Location: ../../login.php");
    exit;
}

/* SAVE ORDER */
if (isset($_POST['submit'])) {
    $supplier_id = $_POST['supplier_id'];
    $medicine_name = $_POST['medicine_name'];
    $quantity = $_POST['quantity'];
    $status = $_POST['status'];

    $conn->query("
        INSERT INTO purchase_orders (supplier_id, medicine_name, quantity, order_date, status)
        VALUES ('$supplier_id', '$medicine_name', '$quantity', CURDATE(), '$status')
    ");

    header("Location: purchase_orders.php");
    exit;
}

/* FETCH SUPPLIERS */
$suppliers = $conn->query("SELECT * FROM suppliers");
?>

<!DOCTYPE html>
<html>
<head>
<title>Add Purchase Order</title>

<style>
body{
    margin:0;
    font-family:Arial;
    background:#f4f7fb;
}

.container{
    max-width:600px;
    margin:40px auto;
    background:white;
    padding:30px;
    border-radius:12px;
    box-shadow:0 5px 15px rgba(0,0,0,0.08);
}

h2{
    margin-bottom:20px;
    color:#2b6cb0;
}

label{
    display:block;
    margin-top:15px;
    font-weight:bold;
}

input, select{
    width:100%;
    padding:10px;
    margin-top:8px;
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
}

button:hover{
    background:#1e4f8a;
}
</style>
</head>
<body>

<div class="container">
    <h2>Add Purchase Order</h2>

    <form method="POST">

        <label>Supplier</label>
        <select name="supplier_id" required>
            <option value="">Select Supplier</option>
            <?php while($s = $suppliers->fetch_assoc()){ ?>
                <option value="<?php echo $s['supplier_id']; ?>">
                    <?php echo $s['name']; ?>
                </option>
            <?php } ?>
        </select>

        <label>Medicine Name</label>
        <input type="text" name="medicine_name" required>

        <label>Quantity</label>
        <input type="number" name="quantity" required>

        <label>Status</label>
        <select name="status" required>
            <option value="Pending">Pending</option>
            <option value="Delivered">Delivered</option>
        </select>

        <button type="submit" name="submit">Save Order</button>
    </form>
</div>

</body>
</html>