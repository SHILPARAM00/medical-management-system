<?php
require "../config/database.php";

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

$id = $data['id'];

$stmt = $conn->prepare("
    UPDATE prescriptions 
    SET status='dispensed'
    WHERE prescription_id=?
");

$stmt->bind_param("i", $id);

if($stmt->execute()) {
    echo json_encode(["status"=>"success"]);
} else {
    echo json_encode(["status"=>"error"]);
}
?>