<?php
require "../../config/db_connect.php";

$data = json_decode(file_get_contents("php://input"), true);

$id = $data['id'];
$name = $data['name'];
$price = $data['price'];
$stock = $data['stock'];

$sql = "UPDATE medicines 
        SET name=?, price=?, stock=? 
        WHERE medicine_id=?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sdii", $name, $price, $stock, $id);

if($stmt->execute()){
    echo json_encode(["status"=>"updated"]);
} else {
    echo json_encode(["status"=>"error"]);
}
?>