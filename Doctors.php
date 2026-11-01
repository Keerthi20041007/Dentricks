<?php
$conn = new mysqli('localhost', 'root', '', 'dental');
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);
$result = $conn->query("SELECT * FROM doctors");
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Our Doctors – DENTRICKS</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="shared.css">
  <style>
    .doctors-hero {
      background: linear-gradient(160deg, var(--teal-dark), var(--teal-light));
      padding: 100px 48px 80px; text-align: center; position: relative; overflow: hidden;
    }
    .doctors-hero::before { content:''; position:absolute; bottom:-60px; left:-60px; width:260px; height:260px; border-radius:50%; background:rgba(255,255,255,.05); }
    .doctors-hero h1 { font-family:var(--font-display); font-size:clamp(32px,5vw,52px); color:var(--white); margin-bottom:14px; position:relative; }
    .doctors-hero p  { color:rgba(255,255,255,.7); font-size:16px; font-weight:300; position:relative; }

    .doctors-body { max-width:1100px; margin:0 auto; padding:72px 48px 100px; }
    .doctors-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(320px,1fr)); gap:28px; }

    .doctor-card {
      background:var(--white); border:1px solid var(--border); border-radius:var(--radius-lg);
      overflow:hidden; box-shadow:var(--shadow-sm); transition:transform .25s, box-shadow .25s;
    }
    .doctor-card:hover { transform:translateY(-5px); box-shadow:var(--shadow-md); }
    .doctor-card-header {
      background: linear-gradient(135deg, var(--teal-dark), var(--teal-light));
      padding: 32px 28px; display:flex; align-items:center; gap:18px;
    }
    .doctor-avatar {
      width:72px; height:72px; border-radius:50%;
      background:rgba(255,255,255,.15); border:3px solid rgba(255,255,255,.3);
      display:flex; align-items:center; justify-content:center; font-size:34px; flex-shrink:0;
    }
    .doctor-card-header h2 { font-family:var(--font-display); font-size:20px; color:var(--white); margin-bottom:4px; }
    .doctor-card-header span { font-size:13px; color:rgba(255,255,255,.7); font-weight:300; }
    .doctor-card-body { padding:24px 28px; }
    .doctor-info-row { display:flex; align-items:flex-start; gap:12px; margin-bottom:14px; }
    .doctor-info-row .lbl { font-size:11.5px; font-weight:600; letter-spacing:.5px; text-transform:uppercase; color:var(--muted); min-width:55px; padding-top:1px; }
    .doctor-info-row .val { font-size:14px; color:var(--charcoal); line-height:1.5; }
    .doctor-info-row .val a { color:var(--teal); }
    .no-doctors {
      grid-column: 1/-1; text-align:center; padding:80px 0;
      font-size:18px; color:var(--muted);
    }

    @media(max-width:860px){ .doctors-hero,.doctors-body { padding-left:24px; padding-right:24px; } }
  </style>
</head>
<body>
<nav class="dnt-nav">
  <a href="code.php" class="dnt-brand"><div class="dnt-brand-icon">🦷</div>DENTRICKS</a>
  <ul class="dnt-nav-links">
    <li><a href="code.php">Home</a></li>
    <li><a href="services.php">Services</a></li>
    <li><a href="Doctors.php" class="active">Doctors</a></li>
    <li><a href="code.php#contact">Contact</a></li>
  </ul>
  <div class="dnt-nav-actions">
    <a href="prolog.html" class="btn btn-outline">Login</a>
    <a href="registar.php" class="btn btn-primary">Register</a>
  </div>
</nav>

<div class="page-body">
  <div class="doctors-hero">
    <div class="section-label" style="margin:0 auto 20px;display:inline-flex;">✦ Meet the Team</div>
    <h1>Our Doctors</h1>
    <p>Qualified specialists dedicated to your dental health and well-being.</p>
  </div>

  <div class="doctors-body">
    <div class="doctors-grid">
      <?php if ($result && $result->num_rows > 0):
        while ($row = $result->fetch_assoc()): ?>
        <div class="doctor-card">
          <div class="doctor-card-header">
            <div class="doctor-avatar">👨‍⚕️</div>
            <div>
              <h2><?= htmlspecialchars($row['name']) ?></h2>
              <span><?= htmlspecialchars($row['specialization']) ?></span>
            </div>
          </div>
          <div class="doctor-card-body">
            <div class="doctor-info-row">
              <span class="lbl">Phone</span>
              <span class="val"><a href="tel:<?= htmlspecialchars($row['contact']) ?>"><?= htmlspecialchars($row['contact']) ?></a></span>
            </div>
            <div class="doctor-info-row">
              <span class="lbl">Email</span>
              <span class="val"><a href="mailto:<?= htmlspecialchars($row['email']) ?>"><?= htmlspecialchars($row['email']) ?></a></span>
            </div>
            <?php if (!empty($row['bio'])): ?>
            <div class="doctor-info-row">
              <span class="lbl">Bio</span>
              <span class="val"><?= htmlspecialchars($row['bio']) ?></span>
            </div>
            <?php endif; ?>
            <a href="appointment.php" class="btn btn-primary" style="margin-top:20px;width:100%">Book with Dr. <?= explode(' ', htmlspecialchars($row['name']))[count(explode(' ', $row['name'])) - 1] ?></a>
          </div>
        </div>
      <?php endwhile;
      else: ?>
        <div class="no-doctors">👨‍⚕️ No doctors listed yet. Check back soon!</div>
      <?php endif; ?>
    </div>
  </div>
</div>

<footer class="dnt-footer">
  <div class="dnt-footer-grid">
    <div>
      <div class="footer-brand">🦷 DENTRICKS</div>
      <p>Compassionate dental care for every smile.</p>
      <p style="margin-top:12px">📍 St Mary's Shopping Complex, Chullikara, Kasaragod</p>
      <p>📞 +91 6235369136 &nbsp;|&nbsp; ✉️ dentricks@gmail.com</p>
    </div>
    <div><h4>Quick Links</h4><ul>
      <li><a href="code.php">Home</a></li><li><a href="services.php">Services</a></li>
      <li><a href="Doctors.php">Doctors</a></li><li><a href="registar.php">Register</a></li>
    </ul></div>
    <div><h4>Services</h4><ul>
      <li><a href="services.php">General Dentistry</a></li><li><a href="services.php">Cosmetic</a></li>
      <li><a href="services.php">Surgical</a></li><li><a href="services.php">Whitening</a></li>
    </ul></div>
    <div><h4>Hours</h4><p>Mon – Sat: 9 AM – 6 PM</p><p>Sunday: Emergency Only</p></div>
  </div>
  <div class="footer-copy"><span>© 2025 Dentricks. All rights reserved.</span><span>Kasaragod, Kerala</span></div>
</footer>
</body>
</html>
