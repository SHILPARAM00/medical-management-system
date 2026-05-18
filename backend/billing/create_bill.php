<?php
require "../../config/db_connect.php";

$data = json_decode(file_get_contents("php://input"), true);

$patient_id = $data['patient_id'];
$appointment_id = $data['appointment_id'];

$total = 0;

/* GET prescription items */
$sql = "SELECT pi.quantity, m.price
        FROM prescription_items pi
        JOIN medicines m ON pi.medicine_id = m.medicine_id
        WHERE pi.prescription_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $data['prescription_id']);
$stmt->execute();
$result = $stmt->get_result();

/* CALCULATE TOTAL */
while($row = $result->fetch_assoc()){
    $total += $row['quantity'] * $row['price'];
}

/* INSERT BILL */
$insert = $conn->prepare("
    INSERT INTO bills (patient_id, appointment_id, total_amount, payment_status)
    VALUES (?, ?, ?, 'pending')
");

$insert->bind_param("iid", $patient_id, $appointment_id, $total);

if($insert->execute()){
    echo json_encode([
        "status"=>"success",
        "total"=>$total
    ]);
} else {
    echo json_encode(["status"=>"error"]);
}
?>