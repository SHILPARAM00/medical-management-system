<?php
require_once "../../config/db_connect.php";

$id = $_GET['id'];

// GET ALL ITEMS
$items = $conn->query("
SELECT pi.*, m.stock, m.medicine_id
FROM prescription_items pi
JOIN medicines m ON pi.medicine_id = m.medicine_id
WHERE pi.prescription_id = $id
");

while($i = $items->fetch_assoc()){

    // CHECK STOCK
    if($i['stock'] < $i['quantity']){
        echo "Not enough stock for medicine ID: ".$i['medicine_id'];
        exit;
    }

    // REDUCE STOCK
    $newStock = $i['stock'] - $i['quantity'];

    $conn->query("
    UPDATE medicines 
    SET stock = $newStock 
    WHERE medicine_id = ".$i['medicine_id']);
}

// UPDATE PRESCRIPTION STATUS
$conn->query("UPDATE prescription SET status='dispensed' WHERE prescription_id=$id");

// REDIRECT
header("Location: ../../modules/pharmacist/view_bill.php?id=".$id);
?>