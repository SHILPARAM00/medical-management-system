<?php
session_start();
require_once "config/db_connect.php";

/* =========================
   BACKEND LOGIN
========================= */
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    header("Content-Type: application/json");

    $data = json_decode(file_get_contents("php://input"), true);

    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';
    $role = $data['role'] ?? '';

    if (!$email || !$password || !$role) {
        echo json_encode(["status"=>"error","message"=>"All fields required"]);
        exit;
    }

    $stmt = $conn->prepare("SELECT user_id, name, email, password, role FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {

        $user = $result->fetch_assoc();

        if ($user['role'] !== $role) {
            echo json_encode(["status"=>"error","message"=>"Role mismatch"]);
            exit;
        }

        if (password_verify($password, $user['password'])) {

            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['name'];

            echo json_encode(["status"=>"success","user"=>$user]);

        } else {
            echo json_encode(["status"=>"error","message"=>"Wrong password"]);
        }

    } else {
        echo json_encode(["status"=>"error","message"=>"User not found"]);
    }

    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Login - MedSync</title>

<style>

/* BODY WITH BACKGROUND */
body{
    margin:0;
    font-family:Arial;
    height:100vh;

    background:url('frontend/assets/images/hero.png.jpeg') no-repeat center center/cover;
    display:flex;
    background-position:top center;
    background-size:cover;
    justify-content:flex-end;
    align-items:center;
}

/* OVERLAY FIX (BALANCED CLEAR LOOK) */
body::before{
    content:"";
    position:absolute;
    width:100%;
    height:100%;
    background:linear-gradient(
        to right,
        rgba(255,255,255,0.1),
        rgba(255,255,255,0.75)
    );
    z-index:0;
}

/* LOGIN CONTAINER */
.container{
    position:relative;
    z-index:2;
    width:420px;
    margin-right:80px;
    animation:fadeUp 1s ease;
}

/* LOGO CENTER */
.logo{
    text-align:center;
    margin-bottom:15px;
}

.logo img{
    width:60px;
}

/* TITLE */
.title{
    text-align:center;
    margin-bottom:20px;
}

.title h2{
    color:#2b6cb0;
    margin:5px 0;
}

.title p{
    color:#555;
}

/* INPUTS */
input, select{
    width:100%;
    height:45px;
    margin:10px 0;
    padding:10px;
    border:1px solid #ccc;
    border-radius:8px;
    font-size:14px;
}

/* PASSWORD FIELD */
.pass-box{
    position:relative;
}

.eye{
    position:absolute;
    right:12px;
    top:50%;
    transform:translateY(-50%);
    cursor:pointer;
}

/* OPTIONS ROW */
.options{
    display:flex;
    justify-content:space-between;
    align-items:center;
    font-size:14px;
    margin:10px 0;
}

/* CHECKBOX */
.options label{
    display:flex;
    align-items:center;
    gap:5px;
    color:#444;
}

/* FORGOT PASSWORD */
.options a{
    text-decoration:none;
    color:#2b6cb0;
    font-weight:500;
}

.options a:hover{
    text-decoration:underline;
}

/* BUTTON */
button{
    width:100%;
    padding:12px;
    background:#2b6cb0;
    color:white;
    border:none;
    border-radius:8px;
    font-weight:bold;
    cursor:pointer;
    margin-top:10px;
}

button:hover{
    background:#1e4f8a;
}

/* REGISTER */
.register{
    text-align:center;
    margin-top:15px;
}

.register a{
    color:#2b6cb0;
    text-decoration:none;
    font-weight:500;
}

/* ERROR */
#error{
    text-align:center;
    color:red;
    margin-top:10px;
}

/* ANIMATION */
@keyframes fadeUp{
    from{
        opacity:0;
        transform:translateY(30px);
    }
    to{
        opacity:1;
        transform:translateY(0);
    }
}

</style>
</head>

<body>

<div class="container">

    <!-- LOGO -->
    <div class="logo">
        <img src="frontend/assets/images/logo.png.jpeg">
    </div>

    <!-- TITLE -->
    <div class="title">
        <h2>Medical Management System</h2>
        <p>Smart Management, Better Healthcare</p>
    </div>

    <!-- FORM -->
    <input type="email" id="email" placeholder="Email">

    <div class="pass-box">
        <input type="password" id="password" placeholder="Password">
        <span class="eye" onclick="togglePass()">👁</span>
    </div>

    <select id="role">
        <option value="">Select Role</option>
        <option value="admin">Admin</option>
        <option value="doctor">Doctor</option>
        <option value="pharmacist">Pharmacist</option>
        <option value="patient">Patient</option>
    </select>

    <!-- OPTIONS -->
    <div class="options">
        <label>
            <input type="checkbox"> Remember me
        </label>
        <a href="forgot_password.php">Forgot Password?</a>
    </div>

    <!-- LOGIN BUTTON -->
    <button onclick="login()">Login</button>

    <div id="error"></div>

    <!-- REGISTER -->
    <div class="register">
        Don't have an account? 
        <a href="register.php">Sign Up</a>
    </div>

</div>

<script>

function togglePass(){
    let p = document.getElementById("password");
    p.type = (p.type==="password") ? "text" : "password";
}

function login(){

    let email=document.getElementById("email").value;
    let password=document.getElementById("password").value;
    let role=document.getElementById("role").value;

    fetch("login.php",{
        method:"POST",
        headers:{"Content-Type":"application/json"},
        body:JSON.stringify({email,password,role})
    })
    .then(res=>res.json())
    .then(data=>{

        if(data.status==="success"){

            let r=data.user.role;

            if(r==="admin") window.location.href="modules/admin/dashboard.php";
            else if(r==="doctor") window.location.href="modules/doctor/dashboard.php";
            else if(r==="pharmacist") window.location.href="modules/pharmacist/dashboard.php";
            else if(r==="patient") window.location.href="modules/patient/dashboard.php";

        } else {
            document.getElementById("error").innerText=data.message;
        }

    });
}

</script>

</body>
</html>