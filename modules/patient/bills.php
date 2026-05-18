<?php
session_start();
require_once "../../config/db_connect.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit;
}

$patient_id = $_SESSION['user_id'];

/* FETCH BILLS (FULL FIXED QUERY) */
$stmt = $conn->prepare("
    SELECT 
        b.*,
        a.appointment_date,
        u.name AS doctor_name
    FROM bills b
    LEFT JOIN appointment a 
        ON b.appointment_id = a.appointment_id
    LEFT JOIN doctors d 
        ON a.doctor_id = d.doctor_id
    LEFT JOIN users u 
        ON d.user_id = u.user_id
    WHERE b.patient_id = ?
    ORDER BY b.created_at DESC
");

$stmt->bind_param("i", $patient_id);
$stmt->execute();
$bills = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
<title>My Bills</title>

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

<style>

body{
    margin:0;
    font-family:Arial;
    background:#f4f7fb;
}

.container{
    display:flex;
}

/* Sidebar */
.sidebar{
    width:260px;
    background:#ffffff;
    min-height:100vh;
    padding:20px;
    box-shadow:2px 0 10px rgba(0,0,0,0.05);
}

.logo-box{
    display:flex;
    align-items:center;
    gap:10px;
    margin-bottom:25px;
    padding-bottom:15px;
    border-bottom:1px solid #eee;
}

.logo-box img{
    width:45px;
    height:45px;
    border-radius:10px;
    object-fit:cover;
}

.logo-text{
    font-size:16px;
    font-weight:bold;
    color:#2b6cb0;
    line-height:1.3;
}

.menu a{
    display:block;
    padding:12px;
    margin-bottom:8px;
    text-decoration:none;
    color:#333;
    border-radius:8px;
    transition:0.3s;
}

.menu a:hover,
.menu a.active{
    background:#e6f0fa;
    color:#2b6cb0;
}
/* MAIN */
.main{
    flex:1;
    padding:25px;
}

/* TABLE */
.table{
    width:100%;
    background:white;
    margin-top:20px;
    border-radius:12px;
    overflow:hidden;
    box-shadow:0 5px 20px rgba(0,0,0,0.05);
}

table{
    width:100%;
    border-collapse:collapse;
}

th, td{
    padding:14px;
    text-align:left;
}

th{
    background:#2b6cb0;
    color:white;
}

tr:nth-child(even){
    background:#f9fbff;
}

/* STATUS */
.status{
    padding:6px 12px;
    border-radius:20px;
    font-size:12px;
    font-weight:bold;
    display:inline-block;
}

.paid{
    background:#d4edda;
    color:#155724;
}

.pending{
    background:#fff3cd;
    color:#856404;
}

.failed{
    background:#f8d7da;
    color:#721c24;
}

/* BUTTONS */
.btn{
    padding:6px 12px;
    border:none;
    border-radius:5px;
    cursor:pointer;
    font-size:13px;
}

.view{
    background:#2b6cb0;
    color:white;
}

.pay{
    background:#16a34a;
    color:white;
}

/* MODAL */
.modal{
    display:none;
    position:fixed;
    top:0; left:0;
    width:100%; height:100%;
    background:rgba(0,0,0,0.6);
    justify-content:center;
    align-items:center;
}

.modal-content{
    background:white;
    padding:25px;
    border-radius:10px;
    width:380px;
}

.close{
    float:right;
    cursor:pointer;
    font-size:18px;
}

</style>
</head>

<body>

<div class="container">

<!-- Sidebar -->
<div class="sidebar">

    <div class="logo-box">
        <img src="../../frontend/assets/images/logo.png.jpeg">
        <div class="logo-text">
            Medical Management<br>System
        </div>
    </div>

    <div class="menu">
        <a href="#" >🏠 Dashboard</a>
        <a href="appointments.php" >📅 Appointments</a>
        <a href="prescriptions.php" class="active">💊 Prescriptions</a>
        <a href="bills.php">💰 Bills</a>
        <a href="profile.php">👤 Profile</a>
        <a href="../../logout.php">🚪 Logout</a>
    </div>

</div>

<!-- MAIN -->
<div class="main">

<h2>My Bills</h2>

<div class="table">

<table>

<tr>
<th>ID</th>
<th>Doctor</th>
<th>Date</th>
<th>Amount</th>
<th>Status</th>
<th>Method</th>
<th>Transaction</th>
<th>Action</th>
</tr>

<?php if($bills->num_rows > 0): ?>

<?php while($b = $bills->fetch_assoc()): 
$status = strtolower(trim($b['payment_status'] ?? 'pending'));
?>

<tr>

<td><?php echo $b['bill_id']; ?></td>

<td><?php echo $b['doctor_name'] ?? 'N/A'; ?></td>

<td><?php echo $b['appointment_date'] ?? '-'; ?></td>

<td>₹ <?php echo $b['total_amount']; ?></td>

<td>
<span class="status <?php echo $status; ?>">
<?php echo ucfirst($status); ?>
</span>
</td>

<td><?php echo $b['payment_method'] ?? '-'; ?></td>

<td><?php echo $b['transaction_id'] ?? '-'; ?></td>

<td>

<button class="btn view" onclick="viewBill(
'<?php echo $b['bill_id']; ?>',
'<?php echo $b['total_amount']; ?>',
'<?php echo $b['payment_status']; ?>',
'<?php echo $b['created_at']; ?>',
'<?php echo $b['payment_method']; ?>',
'<?php echo $b['transaction_id']; ?>'
)">
View
</button>

<?php 
$cleanStatus = strtolower(trim($b['payment_status'] ?? 'pending'));

if ($cleanStatus == "pending" || $cleanStatus == "unpaid" || $cleanStatus == "0"): 
?>

<button class="btn pay" onclick="pay(<?php echo $b['bill_id']; ?>)">
Pay
</button>

<?php endif; ?>

</td>

</tr>

<?php endwhile; ?>

<?php else: ?>
<tr>
<td colspan="8" style="text-align:center;">No bills found</td>
</tr>
<?php endif; ?>

</table>

</div>

</div>

</div>

<!-- MODAL -->
<div class="modal" id="modal">
<div class="modal-content">

<span class="close" onclick="closeModal()">✖</span>

<h3>Bill Details</h3>

<p><b>ID:</b> <span id="m_id"></span></p>
<p><b>Amount:</b> ₹ <span id="m_amt"></span></p>
<p><b>Status:</b> <span id="m_status"></span></p>
<p><b>Date:</b> <span id="m_date"></span></p>
<p><b>Method:</b> <span id="m_method"></span></p>
<p><b>Transaction:</b> <span id="m_txn"></span></p>

</div>
</div>

<script>

function viewBill(id,amt,status,date,method,txn){
    document.getElementById("modal").style.display="flex";

    document.getElementById("m_id").innerText=id;
    document.getElementById("m_amt").innerText=amt;
    document.getElementById("m_status").innerText=status;
    document.getElementById("m_date").innerText=date;
    document.getElementById("m_method").innerText=method || "-";
    document.getElementById("m_txn").innerText=txn || "-";
}

function closeModal(){
    document.getElementById("modal").style.display="none";
}

function pay(id){

    let method = prompt("Enter payment method (UPI/Card/Cash):","UPI");
    if(!method) return;

    fetch("../../backend/patient/paybill.php",{
        method:"POST",
        headers:{
            "Content-Type":"application/json"
        },
        body:JSON.stringify({
            bill_id:id,
            method:method
        })
    })
    .then(res=>res.json())
    .then(data=>{
        alert(data.message + "\nTransaction: " + data.txn);
        location.reload();
    });

}

</script>

</body>
</html>