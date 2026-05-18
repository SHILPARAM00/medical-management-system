<?php
require "../../config/db_connect.php";

$patient_id = $_GET['patient_id'];

$sql = "SELECT * FROM bills WHERE patient_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $patient_id);
$stmt->execute();

$result = $stmt->get_result();

$data = [];

while($row = $result->fetch_assoc()){
    $data[] = $row;
}

echo json_encode($data);
?>