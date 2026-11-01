<?php
ini_set('display_errors', 1); error_reporting(E_ALL);
try {
    $conn = new PDO("mysql:host=localhost;dbname=dental", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $conn->query("SELECT * FROM services");
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $conn = null;
} catch (PDOException $e) { $services = []; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Services – DENTRICKS</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="shared.css">
  <style>
    .services-hero {
      background: linear-gradient(160deg, var(--teal-dark), var(--teal-light));
      padding: 100px 48px 80px;
      text-align: center; position: relative; overflow: hidden;
    }
    .services-hero::before {
      content:''; position:absolute; top:-80px; right:-80px;
      width:320px; height:320px; border-radius:50%; background:rgba(255,255,255,.05);
    }
    .services-hero h1 { font-family:var(--font-display); font-size:clamp(32px,5vw,52px); color:var(--white); margin-bottom:14px; position:relative; }
    .services-hero p  { color:rgba(255,255,255,.7); font-size:16.5px; font-weight:300; position:relative; }

    .services-body { max-width:1100px; margin:0 auto; padding:72px 48px 100px; }

    /* Static service showcases */
    .static-services { display:grid; grid-template-columns:repeat(3,1fr); gap:28px; margin-bottom:72px; }
    .srv-card {
      background:var(--white); border:1px solid var(--border); border-radius:var(--radius-lg);
      padding:36px 28px; transition:transform .25s,box-shadow .25s;
      box-shadow:var(--shadow-sm);
    }
    .srv-card:hover { transform:translateY(-5px); box-shadow:var(--shadow-md); }
    .srv-card .sicon { font-size:42px; margin-bottom:18px; }
    .srv-card h3 { font-family:var(--font-display); font-size:20px; color:var(--charcoal); margin-bottom:10px; }
    .srv-card p  { font-size:14px; color:var(--muted); line-height:1.7; }

    /* DB services */
    .db-section-title {
      font-family:var(--font-display); font-size:28px; color:var(--charcoal);
      margin-bottom:32px; padding-bottom:16px; border-bottom:2px solid var(--border);
    }
    .db-services-list { display:grid; gap:20px; }
    .db-service-card {
      background:var(--white); border:1px solid var(--border); border-radius:var(--radius-md);
      padding:28px 32px; display:flex; align-items:center; gap:28px;
      box-shadow:var(--shadow-sm); transition:transform .2s,box-shadow .2s;
    }
    .db-service-card:hover { transform:translateY(-3px); box-shadow:var(--shadow-md); }
    .db-service-card img { width:120px; height:120px; object-fit:cover; border-radius:var(--radius-sm); flex-shrink:0; }
    .db-service-card h2 { font-family:var(--font-display); font-size:20px; color:var(--teal-dark); margin-bottom:8px; }
    .db-service-card p  { font-size:14.5px; color:var(--muted); line-height:1.6; }
    .db-img-placeholder {
      width:120px; height:120px; border-radius:var(--radius-sm); flex-shrink:0;
      background:var(--teal-glow); display:flex; align-items:center; justify-content:center;
      font-size:40px;
    }
    @media(max-width:860px){
      .static-services { grid-template-columns:1fr 1fr; }
      .services-hero, .services-body { padding-left:24px; padding-right:24px; }
    }
    @media(max-width:560px){
      .static-services { grid-template-columns:1fr; }
      .db-service-card { flex-direction:column; }
    }
  </style>
</head>
<body>
<nav class="dnt-nav">
  <a href="code.php" class="dnt-brand"><div class="dnt-brand-icon">🦷</div>DENTRICKS</a>
  <ul class="dnt-nav-links">
    <li><a href="code.php">Home</a></li>
    <li><a href="services.php" class="active">Services</a></li>
    <li><a href="Doctors.php">Doctors</a></li>
    <li><a href="code.php#contact">Contact</a></li>
  </ul>
  <div class="dnt-nav-actions">
    <a href="prolog.html" class="btn btn-outline">Login</a>
    <a href="registar.php" class="btn btn-primary">Register</a>
  </div>
</nav>

<div class="page-body">
  <div class="services-hero">
    <div class="section-label" style="margin:0 auto 20px;display:inline-flex;">✦ What We Offer</div>
    <h1>Our Services</h1>
    <p>Comprehensive dental care tailored to every age and every need.</p>
  </div>

  <div class="services-body">
    <div class="static-services">
      <div class="srv-card"><div class="sicon">🦷</div><h3>General Dentistry</h3><p>Preventive care, diagnosis, and treatment of common dental issues to maintain your overall oral health.</p></div>
      <div class="srv-card"><div class="sicon">✨</div><h3>Cosmetic Dentistry</h3><p>Whitening, veneers, bonding and more — to give you the smile you've always dreamed of.</p></div>
      <div class="srv-card"><div class="sicon">🔬</div><h3>Surgical Dentistry</h3><p>Expert handling of complex cases — extractions, implants, and jaw corrections done safely.</p></div>
      <div class="srv-card"><div class="sicon">🪥</div><h3>Teeth Whitening</h3><p>Professional whitening treatments that deliver noticeably brighter results in one session.</p></div>
      <div class="srv-card"><div class="sicon">😁</div><h3>Braces &amp; Retainers</h3><p>Traditional braces or clear aligners to straighten teeth, with retainers to hold your results.</p></div>
      <div class="srv-card"><div class="sicon">💉</div><h3>Root Canal Therapy</h3><p>Save your natural tooth with modern, comfortable root canal treatment — no more fear.</p></div>
    </div>

    <?php if (!empty($services)): ?>
    <h2 class="db-section-title">All Available Services</h2>
    <div class="db-services-list">
      <?php foreach ($services as $s): ?>
        <div class="db-service-card">
          <?php if (!empty($s['photo'])): ?>
            <img src="<?= htmlspecialchars($s['photo']) ?>" alt="<?= htmlspecialchars($s['service_name']) ?>">
          <?php else: ?>
            <div class="db-img-placeholder">🦷</div>
          <?php endif; ?>
          <div>
            <h2><?= htmlspecialchars($s['service_name']) ?></h2>
            <p><?= htmlspecialchars($s['description']) ?></p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</div>

<footer class="dnt-footer">
  <div class="dnt-footer-grid">
    <div>
      <div class="footer-brand">🦷 DENTRICKS</div>
      <p>Compassionate dental care for every smile.</p>
      <p style="margin-top:12px">📍 St Mary's Shopping Complex, Chullikara, Kasaragod, Kerala</p>
      <p>📞 +91 6235369136 &nbsp;|&nbsp; ✉️ dentricks@gmail.com</p>
    </div>
    <div><h4>Quick Links</h4><ul>
      <li><a href="code.php">Home</a></li><li><a href="services.php">Services</a></li>
      <li><a href="Doctors.php">Doctors</a></li><li><a href="registar.php">Register</a></li>
      <li><a href="prolog.html">Login</a></li>
    </ul></div>
    <div><h4>Services</h4><ul>
      <li><a href="#">General Dentistry</a></li><li><a href="#">Cosmetic Dentistry</a></li>
      <li><a href="#">Surgical Dentistry</a></li><li><a href="#">Whitening</a></li>
      <li><a href="#">Root Canal</a></li>
    </ul></div>
    <div><h4>Hours</h4>
      <p>Mon – Sat: 9:00 AM – 6:00 PM</p>
      <p>Sunday: Emergency Only</p>
    </div>
  </div>
  <div class="footer-copy"><span>© 2025 Dentricks. All rights reserved.</span><span>Kasaragod, Kerala</span></div>
</footer>
</body>
</html>
