<?php
require_once "../../config/db_connect.php";

$data = json_decode(file_get_contents("php://input"), true);

$id = $data['id'] ?? 0;
$status = $data['status'] ?? '';

if(!$id || !$status){
    echo json_encode(["status"=>"error","message"=>"Invalid data"]);
    exit;
}

$stmt = $conn->prepare("UPDATE appointment SET status=? WHERE appointment_id=?");
$stmt->bind_param("si", $status, $id);

if($stmt->execute()){
    echo json_encode(["status"=>"success"]);
}else{
    echo json_encode(["status"=>"error","message"=>$stmt->error]);
}