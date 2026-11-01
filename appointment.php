<?php
ini_set('display_errors',1); error_reporting(E_ALL);
$conn = new mysqli('localhost', 'root', '', 'dental');
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);
$doctors_result = $conn->query("SELECT name FROM doctors");
$doctors = [];
while ($row = $doctors_result->fetch_assoc()) $doctors[] = $row['name'];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['doctor'])) {
    $doctor = $conn->real_escape_string($_POST['doctor']);
    $date   = $conn->real_escape_string($_POST['date']);
    $sql    = "SELECT appointment_time FROM appointments WHERE doctor = ? AND appointment_date = ?";
    $stmt   = $conn->prepare($sql); $stmt->bind_param("ss",$doctor,$date); $stmt->execute();
    $result = $stmt->get_result(); $booked_times = [];
    while ($row = $result->fetch_assoc()) $booked_times[] = date('H:i', strtotime($row['appointment_time']));
    $stmt->close(); header('Content-Type: application/json'); echo json_encode($booked_times); $conn->close(); exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Book Appointment – DENTRICKS</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="shared.css">
  <style>
    .appt-wrapper { display:grid; grid-template-columns:1fr 1.2fr; min-height:100vh; padding-top:var(--nav-h); }
    .appt-left { background:linear-gradient(160deg,var(--teal-dark),var(--teal-light)); padding:64px 52px; display:flex; flex-direction:column; justify-content:center; position:relative; overflow:hidden; }
    .appt-left::before { content:''; position:absolute; top:-80px; right:-80px; width:280px; height:280px; border-radius:50%; background:rgba(255,255,255,.05); }
    .appt-left h2 { font-family:var(--font-display); font-size:clamp(28px,3vw,42px); color:var(--white); margin-bottom:18px; position:relative; }
    .appt-left h2 em { font-style:normal; color:var(--gold); }
    .appt-left p  { color:rgba(255,255,255,.7); font-size:15px; font-weight:300; line-height:1.8; margin-bottom:40px; position:relative; }
    .info-box { background:rgba(255,255,255,.1); border:1px solid rgba(255,255,255,.18); border-radius:var(--radius-md); padding:24px 28px; backdrop-filter:blur(12px); position:relative; }
    .info-box h4 { color:var(--white); font-size:15px; font-weight:600; margin-bottom:14px; }
    .info-row { display:flex; align-items:flex-start; gap:10px; margin-bottom:12px; }
    .info-row span { font-size:17px; }
    .info-row p { font-size:13.5px; color:rgba(255,255,255,.75); line-height:1.4; }
    .appt-right { background:var(--cream); display:flex; align-items:center; justify-content:center; padding:60px 52px; }
    .appt-card { background:var(--white); border-radius:var(--radius-lg); padding:44px 44px; border:1px solid var(--border); box-shadow:var(--shadow-lg); width:100%; max-width:520px; }
    .appt-card h2 { font-family:var(--font-display); font-size:26px; margin-bottom:6px; }
    .appt-card .sub { font-size:14px; color:var(--muted); margin-bottom:32px; font-weight:300; }
    .form-row-2 { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
    .time-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:10px; margin-top:8px; }
    .time-slot { padding:10px 4px; border:1.5px solid var(--border); border-radius:var(--radius-sm); text-align:center; font-size:13px; font-weight:500; cursor:pointer; transition:all .18s; color:var(--charcoal); background:var(--white); }
    .time-slot:hover:not(.booked):not(.unavail) { border-color:var(--teal); color:var(--teal); background:var(--teal-glow); }
    .time-slot.selected { background:var(--teal); color:var(--white); border-color:var(--teal); }
    .time-slot.booked  { background:#f3f3f3; color:#bbb; cursor:not-allowed; }
    .time-slot.unavail { background:#f3f3f3; color:#bbb; cursor:not-allowed; }
    .time-loading { font-size:13.5px; color:var(--muted); text-align:center; padding:20px 0; }
    @media(max-width:860px){ .appt-wrapper{grid-template-columns:1fr;} .appt-left{padding:48px 28px;min-height:280px;} .appt-right{padding:36px 20px;} .form-row-2{grid-template-columns:1fr;} }
    .appt-card { animation:slideUp .45s cubic-bezier(.22,1,.36,1) both; }
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
    <a href="patient_profile.php" class="btn btn-outline">My Profile</a>
    <a href="patient_logout.php" class="btn btn-primary">Logout</a>
  </div>
</nav>
<div class="appt-wrapper">
  <div class="appt-left">
    <div class="section-label" style="background:rgba(255,255,255,.12);color:rgba(255,255,255,.9);margin-bottom:24px;">📅 Schedule Visit</div>
    <h2>Book Your<br><em>Appointment</em></h2>
    <p>Choose your preferred doctor and time slot. We will confirm your booking and send a reminder before your visit.</p>
    <div class="info-box">
      <h4>Clinic Hours</h4>
      <div class="info-row"><span>🕘</span><p>Monday – Saturday: 9:00 AM – 6:00 PM</p></div>
      <div class="info-row"><span>🚨</span><p>Sunday: Emergency cases only</p></div>
      <div class="info-row"><span>📞</span><p>+91 6235369136</p></div>
      <div class="info-row"><span>📍</span><p>St Mary's Shopping Complex, Chullikara, Kasaragod</p></div>
    </div>
  </div>
  <div class="appt-right">
    <div class="appt-card">
      <h2>Book Appointment</h2>
      <p class="sub">Fill in your details and pick a time that works for you.</p>
      <form action="submit_appointment.php" method="post" onsubmit="return validateForm()">
        <div class="form-row-2">
          <div class="form-group">
            <label class="form-label">Your Name</label>
            <input type="text" id="username" name="username" class="form-input" placeholder="Full name" required>
          </div>
          <div class="form-group">
            <label class="form-label">Email</label>
            <input type="email" id="email" name="email" class="form-input" placeholder="your@email.com" required>
          </div>
        </div>
        <div class="form-row-2">
          <div class="form-group">
            <label class="form-label">Phone Number</label>
            <input type="tel" id="phone" name="phone" class="form-input" placeholder="10-digit number" required>
          </div>
          <div class="form-group">
            <label class="form-label">Preferred Doctor</label>
            <select id="doctor" name="doctor" class="form-input" required onchange="loadSlots()">
              <option value="">Select a doctor</option>
              <?php foreach ($doctors as $doc): ?>
                <option value="<?php echo htmlspecialchars($doc); ?>"><?php echo htmlspecialchars($doc); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Preferred Date</label>
          <input type="date" id="date" name="date" class="form-input" required onchange="loadSlots()" min="<?php echo date('Y-m-d'); ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Select Time Slot</label>
          <div id="time-slots-container"><p class="time-loading">Please select a doctor and date first.</p></div>
          <input type="hidden" id="hidden-time" name="time" required>
        </div>
        <button type="submit" class="btn btn-primary btn-full" style="padding:14px;margin-top:8px;">Confirm Booking</button>
      </form>
    </div>
  </div>
</div>
<script>
function loadSlots() {
  const doctor = document.getElementById('doctor').value;
  const date   = document.getElementById('date').value;
  const container = document.getElementById('time-slots-container');
  if (!doctor || !date) { container.innerHTML = '<p class="time-loading">Please select a doctor and date first.</p>'; return; }
  container.innerHTML = '<p class="time-loading">Loading available slots...</p>';
  const fd = new FormData(); fd.append('doctor', doctor); fd.append('date', date);
  fetch('appointment.php', { method:'POST', body:fd })
    .then(r => r.json())
    .then(bookedTimes => {
      const now = new Date(), todayStr = now.toISOString().split('T')[0];
      const isToday = date === todayStr, curMin = now.getHours()*60 + now.getMinutes();
      container.innerHTML = '<div class="time-grid" id="time-grid"></div>';
      const grid = document.getElementById('time-grid');
      for (let h = 9; h <= 17; h++) {
        for (let m of [0, 30]) {
          if (h === 17 && m === 30) continue;
          const hS = String(h).padStart(2,'0'), mS = String(m).padStart(2,'0'), val = hS+':'+mS;
          const label = new Date('2000-01-01T'+val).toLocaleTimeString([],{hour:'2-digit',minute:'2-digit',hour12:true});
          const booked = bookedTimes.includes(val), past = isToday && h*60+m < curMin;
          const div = document.createElement('div');
          div.className = 'time-slot' + (booked?' booked':'') + (past&&!booked?' unavail':'');
          div.textContent = label;
          if (booked) div.textContent += ' (Booked)';
          div.dataset.val = val;
          if (!booked && !past) div.addEventListener('click', () => {
            document.querySelectorAll('.time-slot').forEach(s => s.classList.remove('selected'));
            div.classList.add('selected');
            document.getElementById('hidden-time').value = val;
          });
          grid.appendChild(div);
        }
      }
    }).catch(() => { container.innerHTML = '<p class="time-loading">Error loading slots. Please try again.</p>'; });
}
function validateForm() {
  const u = document.getElementById('username').value.trim();
  const e = document.getElementById('email').value.trim();
  const p = document.getElementById('phone').value.trim();
  const d = document.getElementById('doctor').value;
  const dt = document.getElementById('date').value;
  const t  = document.getElementById('hidden-time').value;
  if (!/^[A-Za-z\s]+$/.test(u)) { alert('Name must contain only letters.'); return false; }
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(e)) { alert('Please enter a valid email.'); return false; }
  if (!/^[0-9]{10}$/.test(p)) { alert('Phone must be 10 digits.'); return false; }
  if (!d) { alert('Please select a doctor.'); return false; }
  if (!dt) { alert('Please select a date.'); return false; }
  if (!t) { alert('Please select a time slot.'); return false; }
  return true;
}
</script>
</body>
</html>
