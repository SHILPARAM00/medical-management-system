<?php
require "../config/database.php";

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

$prescription_id = $data['prescription_id'];
$medicine_id = $data['medicine_id'];
$dosage = $data['dosage'];
$quantity = $data['quantity'];

/* ADD MEDICINE ITEM */
$stmt = $conn->prepare("
    INSERT INTO prescription_items 
    (prescription_id, medicine_id, dosage, quantity)
    VALUES (?, ?, ?, ?)
");

$stmt->bind_param("iisi", $prescription_id, $medicine_id, $dosage, $quantity);

if($stmt->execute()) {
    echo json_encode(["status"=>"success"]);
} else {
    echo json_encode(["status"=>"error"]);
}
?>