<?php
session_start();
require_once "config/db_connect.php";

/* =========================
   BACKEND RESET PASSWORD
========================= */
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    header("Content-Type: application/json");

    $data = json_decode(file_get_contents("php://input"), true);

    $email = $data['email'] ?? '';
    $new_password = $data['new_password'] ?? '';

    if (!$email || !$new_password) {
        echo json_encode(["status"=>"error","message"=>"All fields required"]);
        exit;
    }

    // check user exists
    $check = $conn->prepare("SELECT user_id FROM users WHERE email=?");
    $check->bind_param("s", $email);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(["status"=>"error","message"=>"User not found"]);
        exit;
    }

    // update password
    $hashed = password_hash($new_password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("UPDATE users SET password=? WHERE email=?");
    $stmt->bind_param("ss", $hashed, $email);

    if ($stmt->execute()) {
        echo json_encode(["status"=>"success"]);
    } else {
        echo json_encode(["status"=>"error","message"=>"Something went wrong"]);
    }

    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Forgot Password - MedSync</title>

<style>

/* SAME BACKGROUND */
body{
    margin:0;
    font-family:Arial;
    height:100vh;

    background:url('frontend/assets/images/hero.png.jpeg') no-repeat center top/cover;
    display:flex;
    justify-content:flex-end;
    align-items:center;
}

/* OVERLAY */
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

/* CONTAINER */
.container{
    position:relative;
    z-index:2;
    width:420px;
    margin-right:80px;
    animation:fadeUp 1s ease;
}

/* LOGO */
.logo{
    text-align:center;
    margin-bottom:10px;
}
.logo img{
    width:60px;
}

/* TITLE */
.title{
    text-align:center;
    margin-bottom:15px;
}
.title h2{
    color:#2b6cb0;
    margin:5px 0;
}
.title p{
    color:#555;
}

/* INPUT */
input{
    width:100%;
    height:45px;
    margin:10px 0;
    padding:10px;
    border:1px solid #ccc;
    border-radius:8px;
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
}
button:hover{
    background:#1e4f8a;
}

/* MESSAGE */
.msg{
    text-align:center;
    margin-top:10px;
}

/* LINK */
.links{
    text-align:center;
    margin-top:10px;
}
.links a{
    color:#2b6cb0;
    text-decoration:none;
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
        <img src="frontend/assets/images/logo.png.jpeg" alt="logo">
    </div>

    <!-- TITLE -->
    <div class="title">
        <h2>Reset Password</h2>
        <p>Secure your account</p>
    </div>

    <!-- FORM -->
    <input type="email" id="email" placeholder="Enter Registered Email">

    <input type="password" id="new_password" placeholder="New Password">

    <button onclick="resetPassword()">Reset Password</button>

    <div class="msg" id="msg"></div>

    <!-- LINK -->
    <div class="links">
        <a href="login.php">Back to Login</a>
    </div>

</div>

<script>

function resetPassword() {

    let email = document.getElementById("email").value;
    let new_password = document.getElementById("new_password").value;

    fetch("forgot_password.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({email, new_password})
    })
    .then(res => res.json())
    .then(res => {

        let msg = document.getElementById("msg");

        if (res.status === "success") {
            msg.style.color = "green";
            msg.innerText = "Password updated successfully";
        } else {
            msg.style.color = "red";
            msg.innerText = res.message;
        }

    });

}

</script>

</body>
</html>