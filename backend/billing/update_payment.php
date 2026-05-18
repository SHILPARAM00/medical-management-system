<?php
require "../../config/db_connect.php";

$data = json_decode(file_get_contents("php://input"), true);

$bill_id = $data['bill_id'];

$sql = "UPDATE bills SET payment_status='paid' WHERE bill_id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $bill_id);

if($stmt->execute()){
    echo json_encode(["status"=>"paid"]);
} else {
    echo json_encode(["status"=>"error"]);
}
?>