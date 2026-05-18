<?php
require "../../config/db_connect.php";

$data = json_decode(file_get_contents("php://input"), true);

$id = $data['id'];

$conn->query("DELETE FROM medicines WHERE medicine_id=$id");

echo json_encode(["status"=>"deleted"]);
?>