<?php
session_start();
$host = "localhost"; $username = "root"; $password = ""; $database = "dental";
$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);
$sql = "SELECT name FROM doctors";
$result = $conn->query($sql);
$doctors = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) $doctors[] = $row['name'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>DENTRICKS – Your Trusted Dental Home</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="shared.css">
  <style>
    /* ── HERO ── */
    .hero {
      position: relative; overflow: hidden;
      min-height: 92vh; display: flex; align-items: center;
      background: linear-gradient(160deg, var(--teal-dark) 0%, var(--teal) 55%, var(--teal-light) 100%);
    }
    .hero-bg-circle {
      position: absolute; border-radius: 50%; pointer-events: none;
    }
    .hero-bg-circle:nth-child(1) { width:500px; height:500px; top:-150px; right:-100px; background:rgba(255,255,255,.04); }
    .hero-bg-circle:nth-child(2) { width:300px; height:300px; bottom:-80px; left:5%; background:rgba(255,255,255,.04); }
    .hero-content {
      position: relative; z-index: 2;
      max-width: 1200px; margin: 0 auto; padding: 80px 48px;
      display: grid; grid-template-columns: 1fr 1fr; gap: 80px; align-items: center;
    }
    .hero-badge {
      display: inline-flex; align-items: center; gap: 8px;
      background: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.2);
      border-radius: 100px; padding: 6px 18px; font-size: 13px;
      color: rgba(255,255,255,.9); font-weight: 500;
      margin-bottom: 28px; backdrop-filter: blur(10px);
    }
    .hero h1 {
      font-family: var(--font-display); font-size: clamp(38px, 4.5vw, 58px);
      font-weight: 700; color: var(--white); line-height: 1.15; margin-bottom: 22px;
    }
    .hero h1 em { font-style: normal; color: var(--gold); }
    .hero p {
      font-size: 16.5px; color: rgba(255,255,255,.75);
      font-weight: 300; line-height: 1.8; margin-bottom: 40px; max-width: 440px;
    }
    .hero-actions { display: flex; gap: 14px; flex-wrap: wrap; }
    .hero-stats {
      display: flex; gap: 40px; margin-top: 56px;
      padding-top: 40px; border-top: 1px solid rgba(255,255,255,.15);
    }
    .stat-num { font-family: var(--font-display); font-size: 32px; font-weight: 700; color: var(--white); }
    .stat-lbl { font-size: 12px; color: rgba(255,255,255,.55); letter-spacing: .8px; text-transform: uppercase; margin-top: 2px; }
    .hero-visual {
      display: flex; flex-direction: column; gap: 16px;
    }
    .hero-card {
      background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.18);
      border-radius: var(--radius-lg); padding: 24px 28px;
      backdrop-filter: blur(16px); color: var(--white);
    }
    .hero-card-icon { font-size: 32px; margin-bottom: 10px; }
    .hero-card h3 { font-size: 17px; font-weight: 600; margin-bottom: 6px; }
    .hero-card p  { font-size: 13.5px; color: rgba(255,255,255,.65); line-height: 1.6; font-weight: 300; }
    .hero-card-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }

    /* ── ABOUT ── */
    .about-section { padding: 100px 48px; max-width: 1200px; margin: 0 auto; }
    .about-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 72px; align-items: center; }
    .about-img-wrap {
      position: relative; border-radius: var(--radius-lg); overflow: hidden;
    }
    .about-img-wrap img { width: 100%; height: 460px; object-fit: cover; display: block; border-radius: var(--radius-lg); }
    .about-img-badge {
      position: absolute; bottom: 24px; left: 24px;
      background: var(--white); border-radius: var(--radius-md); padding: 16px 22px;
      box-shadow: var(--shadow-lg); display: flex; align-items: center; gap: 14px;
    }
    .about-img-badge .big { font-family: var(--font-display); font-size: 32px; font-weight: 700; color: var(--teal); }
    .about-img-badge small { font-size: 12.5px; color: var(--muted); display: block; line-height: 1.4; }
    .about-text .section-sub { margin-bottom: 28px; }
    .about-checks { display: flex; flex-direction: column; gap: 14px; margin-bottom: 36px; }
    .check-item { display: flex; gap: 12px; align-items: flex-start; }
    .check-icon { width:22px; height:22px; border-radius:50%; background:var(--teal-glow); color:var(--teal); display:flex; align-items:center; justify-content:center; font-size:12px; flex-shrink:0; margin-top:2px; }
    .check-item p { font-size: 14.5px; color: var(--muted); line-height: 1.5; }

    /* ── SERVICES ── */
    .services-section {
      background: var(--charcoal); padding: 100px 48px;
    }
    .services-inner { max-width: 1200px; margin: 0 auto; }
    .services-inner .section-title { color: var(--white); }
    .services-inner .section-sub { color: rgba(255,255,255,.55); }
    .services-inner .section-label { background: rgba(255,255,255,.08); color: rgba(255,255,255,.7); }
    .services-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; margin-top: 56px; }
    .service-card-pro {
      background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.08);
      border-radius: var(--radius-lg); padding: 36px 28px;
      transition: background .3s, transform .25s, border-color .3s; cursor: pointer;
    }
    .service-card-pro:hover { background: rgba(255,255,255,.09); transform: translateY(-4px); border-color: rgba(10,122,130,.5); }
    .service-icon { font-size: 38px; margin-bottom: 20px; }
    .service-card-pro h3 { font-family: var(--font-display); font-size: 20px; color: var(--white); margin-bottom: 12px; }
    .service-card-pro p { font-size: 14px; color: rgba(255,255,255,.5); line-height: 1.7; font-weight: 300; }
    .service-card-pro .card-link { display: inline-flex; align-items: center; gap: 6px; color: var(--teal-light); font-size: 13.5px; font-weight: 500; margin-top: 22px; transition: gap .2s; }
    .service-card-pro:hover .card-link { gap: 10px; }

    /* ── WHY US ── */
    .whyus-section { padding: 100px 48px; max-width: 1200px; margin: 0 auto; }
    .whyus-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; margin-top: 56px; }
    .whyus-card {
      background: var(--white); border-radius: var(--radius-lg);
      border: 1px solid var(--border); padding: 32px 24px; text-align: center;
      box-shadow: var(--shadow-sm); transition: transform .25s, box-shadow .25s;
    }
    .whyus-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-md); }
    .whyus-card .icon { font-size: 40px; margin-bottom: 16px; }
    .whyus-card h4 { font-size: 16px; font-weight: 600; margin-bottom: 8px; }
    .whyus-card p { font-size: 13.5px; color: var(--muted); line-height: 1.6; }

    /* ── APPOINTMENT CTA ── */
    .cta-band {
      background: linear-gradient(135deg, var(--teal-dark), var(--teal-light));
      padding: 72px 48px; text-align: center;
    }
    .cta-band h2 { font-family: var(--font-display); font-size: 36px; color: var(--white); margin-bottom: 14px; }
    .cta-band p  { color: rgba(255,255,255,.75); font-size: 16px; margin-bottom: 36px; }
    .cta-band .phone { font-size: 26px; font-weight: 700; color: var(--gold); margin-bottom: 32px; }

    /* ── FEEDBACK ── */
    .feedback-section { padding: 100px 48px; max-width: 1200px; margin: 0 auto; }
    .feedback-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 72px; align-items: center; margin-top: 56px; }
    .feedback-form-wrap { }
    .feedback-quote {
      background: linear-gradient(160deg, var(--teal-dark), var(--teal));
      border-radius: var(--radius-lg); padding: 48px 44px; color: var(--white);
    }
    .feedback-quote blockquote { font-family: var(--font-display); font-size: 22px; line-height: 1.5; color: rgba(255,255,255,.9); margin-bottom: 28px; }
    .feedback-quote cite { font-size: 13px; color: rgba(255,255,255,.55); }

    @media(max-width:900px){
      .hero-content, .about-grid, .services-grid, .whyus-grid, .feedback-grid { grid-template-columns: 1fr; }
      .hero-card-row { grid-template-columns: 1fr; }
      .whyus-grid { grid-template-columns: 1fr 1fr; }
      .hero-content { padding: 60px 24px; }
      .about-section, .services-section, .whyus-section, .feedback-section { padding: 64px 24px; }
    }
  </style>
</head>
<body>

<!-- NAV -->
<nav class="dnt-nav">
  <a href="code.php" class="dnt-brand"><div class="dnt-brand-icon">🦷</div>DENTRICKS</a>
  <ul class="dnt-nav-links">
    <li><a href="code.php" class="active">Home</a></li>
    <li><a href="services.php">Services</a></li>
    <li><a href="Doctors.php">Doctors</a></li>
    <li><a href="#contact">Contact</a></li>
  </ul>
  <div class="dnt-nav-actions">
    <?php if (isset($_SESSION['patient_id'])): ?>
      <a href="patient_profile.php" class="btn btn-outline">My Profile</a>
      <a href="patient_logout.php" class="btn btn-primary" onclick="if(!confirm('Log out?'))return false;">Logout</a>
    <?php else: ?>
      <a href="prolog.html" class="btn btn-outline">Login</a>
      <a href="registar.php" class="btn btn-primary">Register</a>
    <?php endif; ?>
  </div>
</nav>

<!-- HERO -->
<section class="hero" id="home">
  <div class="hero-bg-circle"></div>
  <div class="hero-bg-circle"></div>
  <div class="hero-content">
    <div>
      <div class="hero-badge">✦ Trusted Dental Care Since 2018</div>
      <h1>Welcome to Your<br><em>Trusted Dental</em><br>Home</h1>
      <p>We provide compassionate, expert care for your smile — from routine check-ups to advanced restorative dentistry. Adults and kids welcome from age 2.</p>
      <div class="hero-actions">
        <a href="prolog.html" class="btn btn-primary btn-lg" id="book1">Book Appointment</a>
        <a href="services.php" class="btn btn-ghost btn-lg">Our Services</a>
      </div>
      <div class="hero-stats">
        <div><div class="stat-num">5K+</div><div class="stat-lbl">Happy Patients</div></div>
        <div><div class="stat-num">12+</div><div class="stat-lbl">Specialists</div></div>
        <div><div class="stat-num">98%</div><div class="stat-lbl">Satisfaction</div></div>
        <div><div class="stat-num">15+</div><div class="stat-lbl">Years Combined</div></div>
      </div>
    </div>
    <div class="hero-visual">
      <div class="hero-card">
        <div class="hero-card-icon">🏆</div>
        <h3>Award-Winning Care</h3>
        <p>Recognized by the Kerala Dental Association for excellence in patient care and clinical outcomes.</p>
      </div>
      <div class="hero-card-row">
        <div class="hero-card">
          <div class="hero-card-icon">📅</div>
          <h3>Easy Booking</h3>
          <p>Book appointments online in under 2 minutes.</p>
        </div>
        <div class="hero-card">
          <div class="hero-card-icon">🛡️</div>
          <h3>Safe &amp; Sterile</h3>
          <p>ISO-certified sterilization protocols.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ABOUT -->
<section id="about">
  <div class="about-section">
    <div class="about-grid">
      <div class="about-img-wrap">
        <img src="image 6.png" alt="Our dental clinic">
        <div class="about-img-badge">
          <div class="big">15+</div>
          <div><strong>Years</strong><small>of trusted dental care<br>in Kasaragod</small></div>
        </div>
      </div>
      <div class="about-text">
        <div class="section-label">✦ About Us</div>
        <h2 class="section-title">Caring for Every <em>Smile</em> We Meet</h2>
        <p class="section-sub">Adults and kids are welcome — we treat patients from age 2 to seniors. At Dentricks we offer a full range of services, from routine check-ups and cleanings to restorative and cosmetic dentistry.</p>
        <div class="about-checks">
          <div class="check-item"><div class="check-icon">✓</div><p>Complimentary custom whitening kit with every new patient visit</p></div>
          <div class="check-item"><div class="check-icon">✓</div><p>Digital X-rays for precise diagnostics with minimal radiation</p></div>
          <div class="check-item"><div class="check-icon">✓</div><p>Comfortable, child-friendly waiting areas</p></div>
          <div class="check-item"><div class="check-icon">✓</div><p>Book a cleaning every 6 months and we'll refill your whitening gel</p></div>
        </div>
        <a href="Doctors.php" class="btn btn-primary">Meet Our Team →</a>
      </div>
    </div>
  </div>
</section>

<!-- SERVICES -->
<section class="services-section" id="services">
  <div class="services-inner">
    <div class="section-label">✦ What We Offer</div>
    <h2 class="section-title" style="color:var(--white)">Discover the Services<br><em style="color:var(--teal-light)">Dentricks</em> Provides</h2>
    <p class="section-sub" style="color:rgba(255,255,255,.55)">From preventive care to advanced procedures — all under one roof.</p>
    <div class="services-grid">
      <div class="service-card-pro">
        <div class="service-icon">🦷</div>
        <h3>General Dentistry</h3>
        <p>Preventive care, diagnosis, and treatment of common dental issues to keep your oral health in peak condition.</p>
        <a href="services.php" class="card-link">Learn more →</a>
      </div>
      <div class="service-card-pro">
        <div class="service-icon">✨</div>
        <h3>Cosmetic Dentistry</h3>
        <p>Enhance the appearance of your teeth, gums, and smile through whitening, veneers, and aesthetic procedures.</p>
        <a href="services.php" class="card-link">Learn more →</a>
      </div>
      <div class="service-card-pro">
        <div class="service-icon">🔬</div>
        <h3>Surgical Dentistry</h3>
        <p>Complex procedures including tooth extractions, dental implants, and jaw corrections handled with expertise.</p>
        <a href="services.php" class="card-link">Learn more →</a>
      </div>
      <div class="service-card-pro">
        <div class="service-icon">🪥</div>
        <h3>Teeth Whitening</h3>
        <p>Professional-grade whitening that removes years of stains, giving you a brighter, more confident smile.</p>
        <a href="services.php" class="card-link">Learn more →</a>
      </div>
      <div class="service-card-pro">
        <div class="service-icon">😁</div>
        <h3>Braces &amp; Retainers</h3>
        <p>Traditional or invisible aligners for straightening teeth, plus retainers to maintain your perfect alignment.</p>
        <a href="services.php" class="card-link">Learn more →</a>
      </div>
      <div class="service-card-pro">
        <div class="service-icon">💉</div>
        <h3>Root Canal Therapy</h3>
        <p>We treat infected tooth pulp by cleaning, shaping, and sealing — saving your natural tooth painlessly.</p>
        <a href="services.php" class="card-link">Learn more →</a>
      </div>
    </div>
  </div>
</section>

<!-- WHY US -->
<section>
  <div class="whyus-section">
    <div class="section-label">✦ Why Choose Us</div>
    <h2 class="section-title">We're Committed to<br>Your <em>Perfect Smile</em></h2>
    <div class="whyus-grid">
      <div class="whyus-card"><div class="icon">🏥</div><h4>Modern Equipment</h4><p>State-of-the-art digital X-rays, laser dentistry, and CAD/CAM technology.</p></div>
      <div class="whyus-card"><div class="icon">👨‍⚕️</div><h4>Expert Team</h4><p>12+ experienced specialists across all dental disciplines.</p></div>
      <div class="whyus-card"><div class="icon">💊</div><h4>Pain-Free Care</h4><p>Advanced anaesthesia techniques ensure a comfortable, stress-free visit.</p></div>
      <div class="whyus-card"><div class="icon">💳</div><h4>Flexible Payments</h4><p>Insurance-friendly and EMI options available to suit every budget.</p></div>
    </div>
  </div>
</section>

<!-- CTA BAND -->
<section class="cta-band" id="contact">
  <h2>Ready to Transform Your Smile?</h2>
  <p>Begin the change today — our team is ready to welcome you.</p>
  <div class="phone">📞 +91 6235369136</div>
  <a href="prolog.html" class="btn btn-primary btn-lg" id="book" style="margin-right:14px">Book Now</a>
  <a href="Doctors.php" class="btn btn-ghost btn-lg">View Doctors</a>
</section>

<!-- FEEDBACK -->
<section>
  <div class="feedback-section">
    <div class="section-label">✦ Your Voice Matters</div>
    <h2 class="section-title">Share Your <em>Experience</em></h2>
    <div class="feedback-grid">
      <div class="feedback-quote">
        <blockquote>"Your smile is our priority — every visit, every patient, every time."</blockquote>
        <p style="color:rgba(255,255,255,.6);font-size:15px;margin-bottom:24px">We read every piece of feedback and use it to make Dentricks even better for you.</p>
        <cite>— The Dentricks Team</cite>
      </div>
      <div class="feedback-form-wrap">
        <form action="feedback.php" method="POST" class="card" onsubmit="return validateFeedbackForm()">
          <h3 style="font-family:var(--font-display);font-size:22px;margin-bottom:24px">Leave Feedback</h3>
          <div class="form-group">
            <label class="form-label">Full Name</label>
            <input type="text" name="name" id="fullName" class="form-input" placeholder="Your name" required>
          </div>
          <div class="form-group">
            <label class="form-label">Email</label>
            <input type="email" name="email" id="email" class="form-input" placeholder="your@email.com" required>
          </div>
          <div class="form-group">
            <label class="form-label">Your Feedback</label>
            <textarea name="feedback" id="feedback" class="form-input" rows="4" placeholder="Tell us about your experience…" required style="resize:vertical"></textarea>
          </div>
          <button type="submit" class="btn btn-primary btn-full">Submit Feedback</button>
        </form>
      </div>
    </div>
  </div>
</section>

<!-- FOOTER -->
<footer class="dnt-footer">
  <div class="dnt-footer-grid">
    <div>
      <div class="footer-brand">🦷 DENTRICKS</div>
      <p>Compassionate dental care for every smile, from kids to seniors.</p>
      <p style="margin-top:14px">📍 St Mary's Shopping Complex,<br>&nbsp;&nbsp;&nbsp;Chullikara, Kasaragod, Kerala</p>
      <p>📞 +91 6235369136</p>
      <p>✉️ dentricks@gmail.com</p>
    </div>
    <div><h4>Quick Links</h4><ul>
      <li><a href="code.php">Home</a></li>
      <li><a href="services.php">Services</a></li>
      <li><a href="Doctors.php">Doctors</a></li>
      <li><a href="registar.php">Register</a></li>
      <li><a href="prolog.html">Login</a></li>
    </ul></div>
    <div><h4>Services</h4><ul>
      <li><a href="services.php">General Dentistry</a></li>
      <li><a href="services.php">Cosmetic Dentistry</a></li>
      <li><a href="services.php">Surgical Dentistry</a></li>
      <li><a href="services.php">Teeth Whitening</a></li>
      <li><a href="services.php">Root Canal</a></li>
    </ul></div>
    <div><h4>Our Doctors</h4><ul>
      <?php foreach($doctors as $d): ?>
        <li><a href="Doctors.php"><?= htmlspecialchars($d) ?></a></li>
      <?php endforeach; if(empty($doctors)): ?>
        <li>Coming soon</li>
      <?php endif; ?>
    </ul></div>
  </div>
  <div class="footer-copy">
    <span>© 2025 Dentricks. All rights reserved.</span>
    <span>Kasaragod, Kerala, India</span>
  </div>
</footer>

<script>
let isLoggedIn = <?= isset($_SESSION['patient_id']) ? 'true' : 'false' ?>;
function handleBooking(e) {
  e.preventDefault();
  if (isLoggedIn) { window.location.href = "appointment.php"; }
  else { alert("Please login to book an appointment."); setTimeout(()=>{ window.location.href="prolog.html"; }, 800); }
}
document.getElementById("book1").addEventListener("click", handleBooking);
document.getElementById("book").addEventListener("click", handleBooking);
function validateFeedbackForm() {
  const name = document.getElementById("fullName").value.trim();
  const email = document.getElementById("email").value.trim();
  const fb = document.getElementById("feedback").value.trim();
  if (!/^[A-Za-z\s]+$/.test(name)) { alert("Name must contain only letters and spaces."); return false; }
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { alert("Please enter a valid email."); return false; }
  if (!fb) { alert("Feedback cannot be empty."); return false; }
  return true;
}
</script>
</body>
</html>
