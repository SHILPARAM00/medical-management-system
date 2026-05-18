<?php
require_once "../../config/db_connect.php";

$data = json_decode(file_get_contents("php://input"), true);

$id = $data['bill_id'] ?? 0;
$method = $data['method'] ?? 'UPI';
$txn = "TXN" . rand(10000,99999);

if($id){

    $stmt = $conn->prepare("
        UPDATE bills 
        SET payment_status='paid',
            payment_method=?,
            transaction_id=?
        WHERE bill_id=?
    ");

    $stmt->bind_param("ssi", $method, $txn, $id);

    if($stmt->execute()){
        echo json_encode([
            "status"=>"success",
            "message"=>"Payment successful",
            "txn"=>$txn
        ]);
    }else{
        echo json_encode(["status"=>"error","message"=>"Failed"]);
    }
}
?>