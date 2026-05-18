<?php
require_once "../../config/db_connect.php";

if(!isset($_GET['id'])){
    echo "Invalid";
    exit;
}

$id = $_GET['id'];

// GET ITEMS
$items = $conn->query("
SELECT pi.*, m.name, m.price
FROM prescription_items pi
JOIN medicines m ON pi.medicine_id = m.medicine_id
WHERE pi.prescription_id = $id
");

$total = 0;
?>

<h2>🧾 Bill</h2>

<table border="1" cellpadding="10">
<tr>
<th>Medicine</th>
<th>Price</th>
<th>Qty</th>
<th>Subtotal</th>
</tr>

<?php while($i = $items->fetch_assoc()){ 
    $sub = $i['price'] * $i['quantity'];
    $total += $sub;
?>

<tr>
<td><?php echo $i['name']; ?></td>
<td>₹<?php echo $i['price']; ?></td>
<td><?php echo $i['quantity']; ?></td>
<td>₹<?php echo $sub; ?></td>
</tr>

<?php } ?>

</table>

<h3>Total Amount: ₹<?php echo $total; ?></h3>