<?php
include "db_connect.php";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = htmlspecialchars($_POST['username']);
    $email = htmlspecialchars($_POST['email']);
    $phone = htmlspecialchars($_POST['phone']);
    $address = htmlspecialchars($_POST['address']);
    $gender = htmlspecialchars($_POST['gender']);
    $date_of_birth = htmlspecialchars($_POST['date_of_birth']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    if (!preg_match("/^[A-Za-z\s]+$/", $full_name)) { echo "<script>alert('Username must contain only alphabetic characters and spaces.'); window.history.back();</script>"; exit(); }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { echo "<script>alert('Invalid email format.'); window.history.back();</script>"; exit(); }
    if (!preg_match("/^[0-9]{10}$/", $phone)) { echo "<script>alert('Phone number must be exactly 10 digits.'); window.history.back();</script>"; exit(); }
    $dob = new DateTime($date_of_birth); $today = new DateTime(); $age = $today->diff($dob)->y;
    if ($age < 18) { echo "<script>alert('You must be 18 or older to register.'); window.history.back();</script>"; exit(); }
    if (strlen($password) < 6 || $password !== $confirm_password) { echo "<script>alert('Password must be at least 6 characters and match the confirmation.'); window.history.back();</script>"; exit(); }
    $password_hashed = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO patients (full_name, email, phone, address, gender, date_of_birth, password) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $full_name, $email, $phone, $address, $gender, $date_of_birth, $password_hashed);
    if ($stmt->execute()) { echo "<script>alert('Registration successful!'); window.location.href = 'code.php';</script>"; }
    else { echo "<p style='color:red'>Error: " . $stmt->error . "</p>"; }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register – DENTRICKS</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="shared.css">
  <style>
    body { background: var(--cream); }
    .reg-wrapper {
      display: grid; grid-template-columns: 1fr 1fr;
      min-height: 100vh; padding-top: var(--nav-h);
    }
    /* Left panel */
    .reg-left {
      background: linear-gradient(160deg, var(--teal-dark) 0%, var(--teal) 60%, var(--teal-light) 100%);
      display: flex; flex-direction: column; justify-content: center;
      padding: 64px 56px; position: relative; overflow: hidden;
    }
    .reg-left::before { content:''; position:absolute; top:-100px; right:-100px; width:350px; height:350px; border-radius:50%; background:rgba(255,255,255,.05); }
    .reg-left::after  { content:''; position:absolute; bottom:-60px; left:-60px; width:240px; height:240px; border-radius:50%; background:rgba(255,255,255,.04); }
    .reg-left h2 { font-family:var(--font-display); font-size:clamp(28px,3vw,40px); color:var(--white); margin-bottom:18px; position:relative; }
    .reg-left h2 em { font-style:normal; color:var(--gold); }
    .reg-left p  { color:rgba(255,255,255,.7); font-size:15px; font-weight:300; line-height:1.8; margin-bottom:40px; position:relative; }
    .benefit-list { list-style:none; display:flex; flex-direction:column; gap:16px; position:relative; }
    .benefit-list li { display:flex; align-items:center; gap:14px; color:rgba(255,255,255,.85); font-size:14.5px; }
    .benefit-icon { width:36px; height:36px; border-radius:50%; background:rgba(255,255,255,.12); border:1px solid rgba(255,255,255,.2); display:flex; align-items:center; justify-content:center; font-size:17px; flex-shrink:0; }
    /* Right panel */
    .reg-right { display:flex; align-items:center; justify-content:center; padding:48px 48px; }
    .reg-card { background:var(--white); border-radius:var(--radius-lg); padding:44px 44px; border:1px solid var(--border); box-shadow:var(--shadow-lg); width:100%; max-width:480px; }
    .reg-card h2 { font-family:var(--font-display); font-size:26px; margin-bottom:6px; }
    .reg-card .sub { font-size:14px; color:var(--muted); margin-bottom:32px; font-weight:300; }
    .form-row { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
    .reg-login { text-align:center; margin-top:20px; font-size:14px; color:var(--muted); }
    .reg-login a { color:var(--teal); font-weight:600; }
    @media(max-width:860px){
      .reg-wrapper { grid-template-columns:1fr; }
      .reg-left { padding:48px 32px; min-height:300px; }
      .reg-right { padding:36px 24px; }
      .form-row { grid-template-columns:1fr; }
    }
    .reg-card { animation: slideUp .45s cubic-bezier(.22,1,.36,1) both; }
    @keyframes slideUp { from{opacity:0;transform:translateY(18px)} to{opacity:1;transform:translateY(0)} }
  </style>
</head>
<body>
<nav class="dnt-nav">
  <a href="code.php" class="dnt-brand"><div class="dnt-brand-icon">🦷</div>DENTRICKS</a>
  <ul class="dnt-nav-links">
    <li><a href="code.php">Home</a></li>
    <li><a href="services.php">Services</a></li>
    <li><a href="Doctors.php">Doctors</a></li>
  </ul>
  <div class="dnt-nav-actions">
    <a href="prolog.html" class="btn btn-outline">Login</a>
  </div>
</nav>

<div class="reg-wrapper">
  <div class="reg-left">
    <div class="section-label" style="background:rgba(255,255,255,.12);color:rgba(255,255,255,.9);margin-bottom:24px;">✦ Create Your Account</div>
    <h2>Join <em>Dentricks</em><br>Patient Portal</h2>
    <p>Get exclusive access to online appointment booking, treatment history, doctor messaging, and more.</p>
    <ul class="benefit-list">
      <li><div class="benefit-icon">📅</div>Book appointments anytime, 24/7</li>
      <li><div class="benefit-icon">🗂️</div>View your full treatment history</li>
      <li><div class="benefit-icon">👨‍⚕️</div>Connect directly with your doctor</li>
      <li><div class="benefit-icon">🔔</div>Receive appointment reminders</li>
      <li><div class="benefit-icon">🛡️</div>Your data is private &amp; secure</li>
    </ul>
  </div>

  <div class="reg-right">
    <div class="reg-card">
      <h2>Create Account</h2>
      <p class="sub">Fill in your details to get started.</p>
      <form action="" method="post" onsubmit="return validateForm()">
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Full Name</label>
            <input type="text" id="username" name="username" class="form-input" placeholder="Your full name" required>
          </div>
          <div class="form-group">
            <label class="form-label">Email Address</label>
            <input type="email" id="email" name="email" class="form-input" placeholder="your@email.com" required>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Phone Number</label>
            <input type="tel" id="phone" name="phone" class="form-input" placeholder="10-digit number" required>
          </div>
          <div class="form-group">
            <label class="form-label">Gender</label>
            <select id="gender" name="gender" class="form-input" required>
              <option value="Male">Male</option>
              <option value="Female">Female</option>
              <option value="Other">Other</option>
            </select>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Address</label>
          <input type="text" id="address" name="address" class="form-input" placeholder="Your address" required>
        </div>
        <div class="form-group">
          <label class="form-label">Date of Birth</label>
          <input type="date" id="date_of_birth" name="date_of_birth" class="form-input" required>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Password</label>
            <input type="password" id="password" name="password" class="form-input" placeholder="Min. 6 characters" required>
          </div>
          <div class="form-group">
            <label class="form-label">Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password" class="form-input" placeholder="Repeat password" required>
          </div>
        </div>
        <button type="submit" class="btn btn-primary btn-full" style="margin-top:8px;padding:14px;">Create My Account</button>
      </form>
      <p class="reg-login">Already have an account? <a href="prolog.html">Sign in →</a></p>
    </div>
  </div>
</div>

<script>
function validateForm() {
  const username = document.getElementById('username').value.trim();
  const email    = document.getElementById('email').value.trim();
  const phone    = document.getElementById('phone').value.trim();
  const dob      = document.getElementById('date_of_birth').value;
  const pass     = document.getElementById('password').value;
  const confirm  = document.getElementById('confirm_password').value;
  if (!/^[A-Za-z\s]+$/.test(username)) { alert('Name must contain only letters and spaces.'); return false; }
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { alert('Please enter a valid email address.'); return false; }
  if (!/^[0-9]{10}$/.test(phone)) { alert('Phone number must be exactly 10 digits.'); return false; }
  const today = new Date(), dobDate = new Date(dob);
  let age = today.getFullYear() - dobDate.getFullYear();
  if (today.getMonth() < dobDate.getMonth() || (today.getMonth() === dobDate.getMonth() && today.getDate() < dobDate.getDate())) age--;
  if (age < 18) { alert('You must be 18 or older to register.'); return false; }
  if (pass.length < 6) { alert('Password must be at least 6 characters.'); return false; }
  if (pass !== confirm) { alert('Passwords do not match.'); return false; }
  return true;
}
</script>
</body>
</html>

$conn->close(); // ✅ close HERE after all work is done