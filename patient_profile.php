<?php
session_start();
$servername="localhost"; $username="root"; $password=""; $dbname="dental";
$conn = new mysqli($servername,$username,$password,$dbname);
if ($conn->connect_error) die("Connection failed: ".$conn->connect_error);
if (!isset($_SESSION['patient_id'])) { header("Location: login.php"); exit(); }
$patient_id = $_SESSION['patient_id'];
$sql="SELECT full_name,email,phone,address,date_of_birth AS dob,gender FROM patients WHERE id=?";
$stmt=$conn->prepare($sql); $stmt->bind_param("i",$patient_id); $stmt->execute();
$patient=$stmt->get_result()->fetch_assoc(); $stmt->close();

if ($_SERVER['REQUEST_METHOD']==='POST' && !isset($_POST['cancel_appointment'])) {
    $fn=$conn->real_escape_string($_POST['fullname']); $em=$conn->real_escape_string($_POST['email']);
    $ph=$conn->real_escape_string($_POST['phone']);    $ad=$conn->real_escape_string($_POST['address']);
    $db=$conn->real_escape_string($_POST['dob']);      $gn=$conn->real_escape_string($_POST['gender']);
    if (!preg_match("/^[A-Za-z\s]+$/",$fn)) { echo "<script>alert('Full Name must contain only alphabetic characters.');window.history.back();</script>"; exit(); }
    if (!filter_var($em,FILTER_VALIDATE_EMAIL))      { echo "<script>alert('Invalid email format.');window.history.back();</script>"; exit(); }
    if (!preg_match("/^[0-9]{10}$/",$ph))            { echo "<script>alert('Phone must be 10 digits.');window.history.back();</script>"; exit(); }
    $dob_date=new DateTime($db); $today=new DateTime(); $age=$today->diff($dob_date)->y;
    if ($age<18) { echo "<script>alert('Patient must be 18+.');window.history.back();</script>"; exit(); }
    $sql="UPDATE patients SET full_name=?,email=?,phone=?,address=?,date_of_birth=?,gender=? WHERE id=?";
    $stmt=$conn->prepare($sql); $stmt->bind_param("ssssssi",$fn,$em,$ph,$ad,$db,$gn,$patient_id);
    if ($stmt->execute()) { header("Location: patient_profile.php?success=1"); exit(); }
    $stmt->close();
}
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['cancel_appointment'])) {
    $ad=$conn->real_escape_string($_POST['appointment_date']); $at=$conn->real_escape_string($_POST['appointment_time']);
    $dc=$conn->real_escape_string($_POST['doctor']); $pn=$conn->real_escape_string($_POST['patient_name']);
    $at.=':00';
    $sql="DELETE FROM appointments WHERE patient_name=? AND doctor=? AND appointment_date=? AND appointment_time=?";
    $stmt=$conn->prepare($sql); $stmt->bind_param("ssss",$pn,$dc,$ad,$at); $stmt->execute();
    header('Content-Type: application/json');
    echo json_encode($stmt->affected_rows>0 ? ['success'=>true,'message'=>'Appointment cancelled.'] : ['success'=>false,'message'=>'Appointment not found.']);
    $stmt->close(); $conn->close(); exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Profile – DENTRICKS</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="shared.css">
  <style>
    body { background:var(--cream); }
    .profile-wrapper { display:grid; grid-template-columns:260px 1fr; min-height:100vh; padding-top:var(--nav-h); }
    /* Sidebar */
    .profile-sidebar {
      background:var(--charcoal); padding:32px 24px;
      display:flex; flex-direction:column; gap:6px;
    }
    .sidebar-brand { font-family:var(--font-display); font-size:20px; color:var(--white); margin-bottom:32px; display:flex; align-items:center; gap:10px; }
    .sidebar-avatar { width:72px; height:72px; border-radius:50%; background:var(--teal-glow); border:3px solid var(--teal); display:flex; align-items:center; justify-content:center; font-size:34px; margin:0 auto 12px; }
    .sidebar-name { text-align:center; font-weight:600; color:var(--white); font-size:15px; margin-bottom:4px; }
    .sidebar-email { text-align:center; font-size:12.5px; color:rgba(255,255,255,.45); margin-bottom:28px; }
    .sidebar-divider { height:1px; background:rgba(255,255,255,.08); margin:8px 0; }
    .sidebar-link {
      display:flex; align-items:center; gap:12px; padding:11px 16px; border-radius:var(--radius-sm);
      color:rgba(255,255,255,.65); font-size:14px; font-weight:500; cursor:pointer;
      transition:background .2s, color .2s; text-decoration:none; border:none; background:none; width:100%; text-align:left;
    }
    .sidebar-link:hover { background:rgba(255,255,255,.06); color:var(--white); }
    .sidebar-link.active { background:var(--teal); color:var(--white); }
    .sidebar-link .icon { font-size:17px; width:22px; text-align:center; }
    .sidebar-logout { margin-top:auto; }
    /* Main content */
    .profile-main { padding:48px; overflow-y:auto; }
    .profile-main h1 { font-family:var(--font-display); font-size:30px; margin-bottom:6px; }
    .profile-main .welcome-sub { color:var(--muted); font-size:15px; margin-bottom:40px; }
    /* Info card */
    .info-card { background:var(--white); border-radius:var(--radius-lg); border:1px solid var(--border); padding:36px; box-shadow:var(--shadow-sm); margin-bottom:24px; }
    .info-card h3 { font-family:var(--font-display); font-size:20px; color:var(--charcoal); margin-bottom:24px; padding-bottom:14px; border-bottom:1px solid var(--border); }
    .info-grid { display:grid; grid-template-columns:1fr 1fr; gap:20px; }
    .info-field label { font-size:12px; font-weight:600; letter-spacing:.6px; text-transform:uppercase; color:var(--muted); display:block; margin-bottom:5px; }
    .info-field p { font-size:15px; color:var(--charcoal); font-weight:500; }
    @media(max-width:860px){ .profile-wrapper{grid-template-columns:1fr;} .profile-sidebar{flex-direction:row;flex-wrap:wrap;gap:8px;padding:16px;} .profile-main{padding:24px;} .info-grid{grid-template-columns:1fr;} }
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
    <a href="appointment.php" class="btn btn-primary">Book Appointment</a>
  </div>
</nav>

<div class="profile-wrapper">
  <aside class="profile-sidebar">
    <div class="sidebar-avatar">👤</div>
    <div class="sidebar-name"><?php echo htmlspecialchars($patient['full_name']); ?></div>
    <div class="sidebar-email"><?php echo htmlspecialchars($patient['email']); ?></div>
    <div class="sidebar-divider"></div>
    <button class="sidebar-link active" id="nav-profile" onclick="switchTab('profile')"><span class="icon">👤</span>My Profile</button>
    <button class="sidebar-link" id="nav-appts"   onclick="switchTab('appointments')"><span class="icon">📅</span>Appointments</button>
    <a href="appointment.php" class="sidebar-link"><span class="icon">➕</span>Book Appointment</a>
    <a href="code.php" class="sidebar-link"><span class="icon">🏠</span>Home</a>
    <div class="sidebar-divider"></div>
    <a href="patient_logout.php" class="sidebar-link sidebar-logout" style="color:#f87171"><span class="icon">🚪</span>Logout</a>
  </aside>

  <main class="profile-main">
    <h1>Welcome, <?php echo htmlspecialchars(explode(' ',$patient['full_name'])[0]); ?>!</h1>
    <p class="welcome-sub">Manage your profile and appointments from here.</p>
    <div id="tab-profile">
      <div class="info-card">
        <h3>Personal Information</h3>
        <div class="info-grid">
          <div class="info-field"><label>Full Name</label><p><?php echo htmlspecialchars($patient['full_name']); ?></p></div>
          <div class="info-field"><label>Email</label><p><?php echo htmlspecialchars($patient['email']); ?></p></div>
          <div class="info-field"><label>Phone</label><p><?php echo htmlspecialchars($patient['phone'] ?? 'Not provided'); ?></p></div>
          <div class="info-field"><label>Gender</label><p><?php echo htmlspecialchars($patient['gender']); ?></p></div>
          <div class="info-field"><label>Date of Birth</label><p><?php echo htmlspecialchars($patient['dob']); ?></p></div>
          <div class="info-field"><label>Address</label><p><?php echo htmlspecialchars($patient['address']); ?></p></div>
        </div>
        <button class="btn btn-outline" style="margin-top:28px" onclick="showEditForm()">Edit Profile</button>
      </div>
      <div id="edit-section" style="display:none">
        <div class="info-card">
          <h3>Edit Profile</h3>
          <form action="" method="POST" onsubmit="return validateProfileForm(this)">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
              <div class="form-group"><label class="form-label">Full Name</label><input type="text" name="fullname" class="form-input" value="<?php echo htmlspecialchars($patient['full_name']); ?>" required></div>
              <div class="form-group"><label class="form-label">Email</label><input type="email" name="email" class="form-input" value="<?php echo htmlspecialchars($patient['email']); ?>" required></div>
              <div class="form-group"><label class="form-label">Phone</label><input type="text" name="phone" class="form-input" value="<?php echo htmlspecialchars($patient['phone']??''); ?>" required></div>
              <div class="form-group"><label class="form-label">Gender</label>
                <select name="gender" class="form-input" required>
                  <option value="Male" <?php echo $patient['gender']==='Male'?'selected':''; ?>>Male</option>
                  <option value="Female" <?php echo $patient['gender']==='Female'?'selected':''; ?>>Female</option>
                  <option value="Other" <?php echo $patient['gender']==='Other'?'selected':''; ?>>Other</option>
                </select>
              </div>
              <div class="form-group"><label class="form-label">Date of Birth</label><input type="date" name="dob" class="form-input" value="<?php echo htmlspecialchars($patient['dob']); ?>" required></div>
              <div class="form-group"><label class="form-label">Address</label><input type="text" name="address" class="form-input" value="<?php echo htmlspecialchars($patient['address']); ?>" required></div>
            </div>
            <div style="display:flex;gap:12px;margin-top:20px">
              <button type="submit" class="btn btn-primary">Save Changes</button>
              <button type="button" class="btn btn-outline" onclick="hideEditForm()">Cancel</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <div id="tab-appointments" style="display:none">
      <div class="info-card">
        <h3>My Appointments</h3>
        <div id="appt-content"><p style="color:var(--muted)">Loading your appointments...</p></div>
      </div>
    </div>
  </main>
</div>

<script>
<?php if (isset($_GET['success'])&&$_GET['success']=='1'): ?>
  alert('Profile updated successfully!');
  history.replaceState({},'','patient_profile.php');
<?php endif; ?>
const patientName = <?php echo json_encode($patient['full_name']); ?>;
<?php
$sql="SELECT doctor,appointment_date,appointment_time,status FROM appointments WHERE patient_name=? ORDER BY appointment_date DESC";
$stmt=$conn->prepare($sql); $stmt->bind_param("s",$patient['full_name']); $stmt->execute();
$appts=$stmt->get_result()->fetchAll(PDO::FETCH_ASSOC) ?? [];
$rows=[]; $r=$stmt->get_result(); while($row=$r->fetch_assoc()) $rows[]=$row;
$stmt->close(); $conn->close();
?>
const appointments = <?php echo json_encode($rows); ?>;
function switchTab(tab) {
  document.getElementById('tab-profile').style.display = tab==='profile'?'block':'none';
  document.getElementById('tab-appointments').style.display = tab==='appointments'?'block':'none';
  document.getElementById('nav-profile').className = 'sidebar-link'+(tab==='profile'?' active':'');
  document.getElementById('nav-appts').className   = 'sidebar-link'+(tab==='appointments'?' active':'');
  if (tab==='appointments') renderAppointments();
}
function renderAppointments() {
  const container = document.getElementById('appt-content');
  if (!appointments.length) { container.innerHTML='<p style="color:var(--muted)">No appointments found. <a href="appointment.php" style="color:var(--teal)">Book one now</a></p>'; return; }
  let html = '<div style="overflow-x:auto"><table class="dnt-table"><thead><tr><th>Doctor</th><th>Date</th><th>Time</th><th>Status</th><th>Action</th></tr></thead><tbody>';
  appointments.forEach(a => {
    const statusClass = a.status==='Pending'?'badge-gold':a.status==='Confirmed'?'badge-green':'badge-red';
    html += '<tr><td>'+a.doctor+'</td><td>'+a.appointment_date+'</td><td>'+a.appointment_time+'</td>';
    html += '<td><span class="badge '+statusClass+'">'+a.status+'</span></td>';
    html += '<td>'+(a.status==='Pending'?'<button class="btn btn-danger" style="padding:6px 14px;font-size:13px" onclick="cancelAppt(\''+a.doctor+'\',\''+a.appointment_date+'\',\''+a.appointment_time+'\')">Cancel</button>':'—')+'</td></tr>';
  });
  html += '</tbody></table></div>';
  container.innerHTML = html;
}
function cancelAppt(doc, date, time) {
  if (!confirm('Cancel this appointment?')) return;
  const fd = new FormData(); fd.append('cancel_appointment','1'); fd.append('doctor',doc); fd.append('appointment_date',date); fd.append('appointment_time',time); fd.append('patient_name',patientName);
  fetch('patient_profile.php',{method:'POST',body:fd}).then(r=>r.json()).then(d=>{ alert(d.message); if(d.success) location.reload(); }).catch(()=>alert('Error cancelling.'));
}
function showEditForm() { document.getElementById('edit-section').style.display='block'; }
function hideEditForm() { document.getElementById('edit-section').style.display='none'; }
function validateProfileForm(form) {
  const fn=form.querySelector('[name=fullname]').value.trim(), em=form.querySelector('[name=email]').value.trim(), ph=form.querySelector('[name=phone]').value.trim(), db=form.querySelector('[name=dob]').value;
  if (!/^[A-Za-z\s]+$/.test(fn)) { alert('Full Name must contain only letters.'); return false; }
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(em)) { alert('Invalid email.'); return false; }
  if (!/^[0-9]{10}$/.test(ph)) { alert('Phone must be 10 digits.'); return false; }
  const today=new Date(), dob=new Date(db); let age=today.getFullYear()-dob.getFullYear();
  if (today.getMonth()<dob.getMonth()||(today.getMonth()===dob.getMonth()&&today.getDate()<dob.getDate())) age--;
  if (age<18) { alert('Patient must be 18+.'); return false; }
  return true;
}
window.onload = () => switchTab('profile');
</script>
</body>
</html>
