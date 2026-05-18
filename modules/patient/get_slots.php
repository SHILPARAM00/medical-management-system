<?php
require_once "../../config/db_connect.php";

header('Content-Type: application/json');

if (!isset($_GET['doctor_id']) || !isset($_GET['date'])) {
    echo json_encode([]);
    exit;
}

$doctor_id = $_GET['doctor_id'];
$date = $_GET['date'];

/* ================= FIXED TIME SLOTS ================= */
/* You can change these as per hospital timing */
$all_slots = [
    "09:00 AM",
    "09:15 AM",
    "09:30 AM",
    "09:45 AM",
    "10:00 AM",
    "10:15 AM",
    "10:30 AM",
    "10:45 AM",
    "11:00 AM",
    "11:15 AM",
    "11:30 AM",
    "11:45 AM",
    "02:00 PM",
    "02:15 PM",
    "02:30 PM",
    "02:45 PM",
    "03:00 PM",
    "03:15 PM",
    "03:30 PM",
    "03:45 PM",
    "04:00 PM"
];

/* ================= FETCH BOOKED SLOTS ================= */
$stmt = $conn->prepare("
    SELECT appointment_time 
    FROM appointment 
    WHERE doctor_id=? AND appointment_date=?
");
$stmt->bind_param("is", $doctor_id, $date);
$stmt->execute();

$result = $stmt->get_result();

$booked = [];

while ($row = $result->fetch_assoc()) {
    $booked[] = $row['appointment_time'];
}

/* ================= FILTER AVAILABLE SLOTS ================= */
$available_slots = [];

foreach ($all_slots as $slot) {
    if (!in_array($slot, $booked)) {
        $available_slots[] = $slot;
    }
}

/* ================= RETURN JSON ================= */
echo json_encode($available_slots);
?>