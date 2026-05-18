<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>MedSync Medical Clinic</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

<style>

html{ scroll-behavior:smooth; }

body{
    margin:0;
    font-family:Arial, sans-serif;
    background:#f5f9fc;
}

/* NAVBAR */
.navbar{
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:15px 60px;
    background:white;
    box-shadow:0 2px 10px rgba(0,0,0,0.05);
    position:sticky;
    top:0;
    z-index:1000;
}

.logo{
    display:flex;
    align-items:center;
    gap:10px;
    font-size:22px;
    font-weight:bold;
    color:#2b6cb0;
}

.logo img{
    width:40px;
}

.menu a{
    margin:0 12px;
    text-decoration:none;
    color:#444;
    font-weight:500;
}

.menu a:hover{
    color:#2b6cb0;
}

.nav-buttons{
    display:flex;
    gap:10px;
}

.btn{
    padding:10px 18px;
    border:none;
    border-radius:6px;
    cursor:pointer;
    font-weight:bold;
}

.login-btn{
    background:white;
    border:1px solid #2b6cb0;
    color:#2b6cb0;
}

.signup-btn{
    background:#38a169;
    color:white;
}

.book-btn{
    background:#2b6cb0;
    color:white;
}

/* HERO */
.hero{
    height:100vh;
    display:flex;
    align-items:center;
    justify-content:flex-start;
    padding-left:120px;
    background:url('frontend/assets/images/hero1.jpeg') no-repeat center top/cover;
    position:relative;
}

.hero-text{
    max-width:520px;
    color:white;
    animation:fadeUp 1s ease;
}

.hero-text h1{
    color:#2b6cb0;
    font-size:48px;
    margin-bottom:15px;
}

.hero-text p{
    font-size:18px;
    color:#666;
    margin-bottom:20px;
}

.hero-btns{
    display:flex;
    gap:15px;
}

.hero-text button{
    padding:12px 22px;
    border:none;
    border-radius:6px;
    cursor:pointer;
    font-size:15px;
}

/* FEATURES */
.features{
    margin-top:-80px;
    padding:40px;
}

.cards{
    display:flex;
    justify-content:center;
    gap:25px;
    flex-wrap:wrap;
}

.card{
    background:white;
    width:280px;
    padding:25px;
    border-radius:12px;
    box-shadow:0 5px 20px rgba(0,0,0,0.08);
    transition:0.3s;
}

.card:hover{
    transform:translateY(-8px);
}

.card i{
    font-size:28px;
    color:#2b6cb0;
    margin-bottom:10px;
}

.card h3{
    color:#2b6cb0;
}

.card p{
    color:#666;
}

/* SECTIONS */
.section{
    padding:80px 60px;
    text-align:center;
}

.section:nth-child(even){
    background:#f0f6fb;
}

.section h2{
    color:#2b6cb0;
}

.section p{
    color:#555;
    max-width:700px;
    margin:auto;
}

/* FOOTER */
footer{
    text-align:center;
    padding:20px;
    background:#e6eef5;
}

/* ANIMATION */
@keyframes fadeUp{
    from{ opacity:0; transform:translateY(40px); }
    to{ opacity:1; transform:translateY(0); }
}

</style>
</head>

<body>

<div class="navbar">

    <div class="logo">
        <img src="frontend/assets/images/logo.png.jpeg">
        Medical Management System
    </div>

    <div class="menu">
        <a href="#home"><i class="fa fa-home"></i> Home</a>
        <a href="#about"><i class="fa fa-info-circle"></i> About</a>
        <a href="#services"><i class="fa fa-stethoscope"></i> Services</a>
        <a href="#patient"><i class="fa fa-user"></i> Patient</a>
        <a href="#contact"><i class="fa fa-phone"></i> Contact</a>
    </div>

    <div class="nav-buttons">
        <button class="btn login-btn" onclick="goLogin()">Login</button>
        <button class="btn signup-btn" onclick="goSignup()">Sign Up</button>
        <button class="btn book-btn" onclick="goBook()">Book</button>
    </div>

</div>

<div class="hero" id="home">

    <div class="hero-text">
        <h1>Your Health,<br>Our Commitment</h1>
        <p>Modern healthcare system with smart management for patients, doctors, and pharmacy.</p>

        <div class="hero-btns">
            <button class="book-btn" onclick="goBook()">Request Appointment</button>
            <button class="signup-btn" onclick="goSignup()">Register Now</button>
        </div>
    </div>

</div>

<div class="features">

<div class="cards">

    <div class="card">
        <i class="fa fa-user-md"></i>
        <h3>Find a Doctor</h3>
        <p>Search for experienced doctors near you.</p>
    </div>

    <div class="card">
        <i class="fa fa-hospital"></i>
        <h3>Our Services</h3>
        <p>Explore all hospital services easily.</p>
    </div>

    <div class="card">
        <i class="fa fa-laptop-medical"></i>
        <h3>Patient Portal</h3>
        <p>Manage records and appointments online.</p>
    </div>

</div>

</div>

<div class="section" id="about">
    <h2>About Us</h2>
    <p>MedSync is a smart hospital management system designed to simplify healthcare operations.</p>
</div>

<div class="section" id="services">
    <h2>Our Services</h2>
    <p>Appointments, prescriptions, pharmacy, billing, and real-time tracking.</p>
</div>

<div class="section" id="patient">
    <h2>Patient Information</h2>
    <p>Access records, prescriptions, and appointments easily.</p>
</div>

<div class="section" id="contact">
    <h2>Contact</h2>
    <p>📧 support@medsync.com <br> 📞 +91 9876543210</p>
</div>

<footer>
© 2026 MedSync Medical System
</footer>

<script>
function goLogin(){
    window.location.href="login.php";
}

function goSignup(){
    window.location.href="register.php";
}

function goBook(){
    window.location.href="login.php";
}
</script>

</body>
</html>