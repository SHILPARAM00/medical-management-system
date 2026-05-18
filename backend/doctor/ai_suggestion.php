<?php
session_start();
require_once "../../config/db_connect.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'doctor') {
    echo "Unauthorized";
    exit;
}

$doctor_id = $_SESSION['user_id'];
$today = date("Y-m-d");

/* ================= DATA ================= */

// Appointments today
$todayCount = $conn->query("
    SELECT COUNT(*) as total 
    FROM appointment 
    WHERE doctor_id = $doctor_id AND appointment_date = '$today'
")->fetch_assoc()['total'];

// Pending
$pending = $conn->query("
    SELECT COUNT(*) as total 
    FROM appointment 
    WHERE doctor_id = $doctor_id AND status='pending'
")->fetch_assoc()['total'];

// Most common disease (from prescriptions)
$disease = $conn->query("
    SELECT medicine_name, COUNT(*) as total 
    FROM prescription 
    GROUP BY medicine_name 
    ORDER BY total DESC 
    LIMIT 1
")->fetch_assoc();

/* ================= AI LOGIC ================= */

$suggestions = [];

// Load-based suggestion
if($todayCount > 8){
    $suggestions[] = "🔥 High workload today. Consider rescheduling low priority cases.";
}

// Pending
if($pending > 3){
    $suggestions[] = "⚠ You have pending appointments. Clear backlog.";
}

// Disease trend
if($disease){
    $suggestions[] = "📊 Most prescribed: <b>".$disease['medicine_name']."</b>. Keep stock ready.";
}

// Smart tip
$suggestions[] = "💡 Tip: Follow up with recent patients to improve care quality.";

/* ================= OUTPUT ================= */

foreach($suggestions as $s){
    echo "<p style='margin-bottom:8px;'>$s</p>";
}
?>