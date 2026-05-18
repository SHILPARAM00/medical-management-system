<?php
session_start();
require_once "../../config/db_connect.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'doctor') {
    echo json_encode(["count"=>0,"data"=>[]]);
    exit;
}

$doctor_id = $_SESSION['user_id'];
$today = date("Y-m-d");

/* ================= AUTO GENERATE NOTIFICATIONS ================= */

// New appointments today
$conn->query("
INSERT INTO notifications (doctor_id, message, type)
SELECT $doctor_id, CONCAT('New appointment on ', appointment_time), 'appointment'
FROM appointment
WHERE doctor_id = $doctor_id 
AND appointment_date = '$today'
AND appointment_id NOT IN (
    SELECT reference_id FROM (
        SELECT id as reference_id FROM notifications
    ) as temp
)
");

/* ================= FETCH ================= */

// Unread count
$count = $conn->query("
    SELECT COUNT(*) as total 
    FROM notifications 
    WHERE doctor_id = $doctor_id AND is_read = 0
")->fetch_assoc()['total'];

// Latest notifications
$data = $conn->query("
    SELECT * FROM notifications
    WHERE doctor_id = $doctor_id
    ORDER BY created_at DESC
    LIMIT 5
");

$list = [];
while($row = $data->fetch_assoc()){
    $list[] = $row;
}

echo json_encode([
    "count"=>$count,
    "data"=>$list
]);