<?php
require "../../config/db_connect.php";

$data = json_decode(file_get_contents("php://input"), true);

$name = $data['name'];
$price = $data['price'];
$stock = $data['stock'];

$sql = "INSERT INTO medicines (name, price, stock) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sdi", $name, $price, $stock);

if($stmt->execute()){
    echo json_encode(["status"=>"success"]);
}
?>