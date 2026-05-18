<?php
require_once "../../config/db_connect.php";
header('Content-Type: application/json');

if (!isset($_GET['department']) || $_GET['department'] == "") {
    echo json_encode([]);
    exit;
}

$department = $_GET['department'];

/* FLEXIBLE MATCH (IMPORTANT FIX) */
$stmt = $conn->prepare("
    SELECT d.doctor_id, u.name, d.specialization
    FROM doctors d
    JOIN users u ON d.user_id = u.user_id
    WHERE LOWER(d.specialization) LIKE LOWER(?)
");

$dept = "%".$department."%";

$stmt->bind_param("s", $dept);
$stmt->execute();

$result = $stmt->get_result();

$data = [];

while($row = $result->fetch_assoc()){
    $data[] = $row;
}

echo json_encode($data);
?>