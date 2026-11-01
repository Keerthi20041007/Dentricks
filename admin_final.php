<?php
session_start();

// Database Configuration
$dsn = 'mysql:host=localhost;dbname=dental;charset=utf8';
$username = 'root';
$password = '';
date_default_timezone_set('Asia/Kolkata'); // Set to Indian Standard Time

// Initialize variables for dashboard stats
$total_patients = 0;
$total_appointments = 0;
$total_doctors = 0;

try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch total patients
    $total_patients = $pdo->query("SELECT COUNT(*) FROM patients")->fetchColumn();

    // Fetch today's appointments (only total count)
    $today = date('Y-m-d');
    $stmt = $pdo->prepare("SELECT COUNT(*) as total_appointments FROM appointments WHERE appointment_date = ?");
    $stmt->execute([$today]);
    $total_appointments = $stmt->fetchColumn();

    // Fetch total doctors
    $total_doctors = $pdo->query("SELECT COUNT(*) FROM doctors")->fetchColumn();

    $last_updated = date("F j, Y h:i A");
} catch (PDOException $e) {
    $last_updated = "Error updating: " . $e->getMessage();
}

// Handle API request for dashboard stats
if (isset($_GET['action']) && $_GET['action'] == 'get_stats') {
    header('Content-Type: application/json');
    echo json_encode([
        'total_patients' => $total_patients,
        'total_appointments' => $total_appointments,
        'total_doctors' => $total_doctors,
        'debug' => ['date' => $today, 'db_status' => 'connected']
    ]);
    exit;
}

// Handle section display
$section = isset($_GET['section']) ? $_GET['section'] : 'dashboard';

// Fetch admin details for settings section
$admin_id = $_SESSION['admin_id'] ?? null;
$admin_username = '';
if ($admin_id) {
    $stmt = $pdo->prepare("SELECT username FROM admins WHERE id = ?");
    $stmt->execute([$admin_id]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    $admin_username = $admin['username'] ?? '';
}

// Handle Service Addition
if (isset($_POST['add_service'])) {
    $service_name = $_POST['service_name'];
    $description = $_POST['description'];
    $photo = $_FILES['photo']['name'];
    $target = "uploads/" . basename($photo);

    // Validate Service Details
    if (!preg_match("/^[A-Za-z\s]+$/", $service_name)) {
        $error_message = "Service Name must contain only alphabetic characters and spaces.";
    } elseif (!preg_match("/^[A-Za-z\s]+$/", $description)) {
        $error_message = "Description must contain only alphabetic characters and spaces.";
    } else {
        if (!is_dir('uploads')) {
            mkdir('uploads', 0755, true);
        }

        if (move_uploaded_file($_FILES['photo']['tmp_name'], $target)) {
            $stmt = $pdo->prepare("INSERT INTO services (service_name, description, photo) VALUES (?, ?, ?)");
            $stmt->execute([$service_name, $description, $photo]);
            $success_message = "Service added successfully!";
        } else {
            $error_message = "Failed to upload photo.";
        }
    }
}

// Handle Service Deletion
if (isset($_GET['delete_service_id'])) {
    $id = (int)$_GET['delete_service_id'];
    $pdo->exec("DELETE FROM services WHERE id = $id");
    $success_message = "Service deleted successfully!";
}

// Handle Doctor Addition
if (isset($_POST['add_doctor'])) {
    $name = $_POST['name'];
    $specialization = $_POST['specialization'];
    $contact = $_POST['contact'];
    $email = $_POST['email'];

    // Validate Doctor Details
    if (!preg_match("/^[A-Za-z\s]+$/", $name)) {
        $error_message = "Doctor Name must contain only alphabetic characters and spaces.";
    } elseif (!preg_match("/^[0-9]{10}$/", $contact)) {
        $error_message = "Contact number must be exactly 10 digits and contain only numbers.";
    } elseif (!preg_match("/^[A-Za-z\s]+$/", $specialization)) {
        $error_message = "Specialization must contain only alphabetic characters and spaces.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO doctors (name, specialization, contact, email, bio) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $name,
            $specialization,
            $contact,
            $email,
            $_POST['bio']
        ]);
        $success_message = "Doctor added successfully!";
    }
}

// Handle Doctor Deletion
if (isset($_GET['delete_doctor_id'])) {
    $id = (int)$_GET['delete_doctor_id'];
    $pdo->exec("DELETE FROM doctors WHERE id = $id");
    $success_message = "Doctor deleted successfully!";
}

// Handle Settings Update (Username Update)
if (isset($_POST['update_settings'])) {
    $new_username = $_POST['username'];

    // Validate Username
    if (!preg_match("/^[A-Za-z\s]+$/", $new_username)) {
        $error_message = "Username must contain only alphabetic characters and spaces.";
    } else {
        $stmt = $pdo->prepare("UPDATE admins SET username = ? WHERE id = ?");
        $stmt->execute([$new_username, $admin_id]);
        $_SESSION['admin_name'] = $new_username; // Update session username
        echo "<script>alert('Settings updated successfully!'); window.location.href='?section=settings';</script>";
    }
}

// Handle Password Change
if (isset($_POST['change_password'])) {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate Password
    if (strlen($new_password) < 6) {
        $error_message = "New password must be at least 6 characters long.";
    } elseif ($new_password !== $confirm_password) {
        $error_message = "New password and confirm password do not match.";
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE admins SET password = ? WHERE id = ?");
        $stmt->execute([$hashed_password, $admin_id]);
        echo "<script>alert('Password changed successfully!'); window.location.href='?section=settings';</script>";
    }
}

// Handle Appointment Edit
if (isset($_POST['edit_appointment'])) {
    $patient_name = $_POST['patient_name'];
    $doctor = $_POST['doctor'];
    $appointment_date = $_POST['appointment_date'];
    $today = date('Y-m-d');

    // Validate Patient Name and Doctor Name
    if (!preg_match("/^[A-Za-z\s]+$/", $patient_name)) {
        $error_message = "Patient Name must contain only alphabetic characters and spaces.";
    } elseif (!preg_match("/^[A-Za-z\s]+$/", $doctor)) {
        $error_message = "Doctor Name must contain only alphabetic characters and spaces.";
    } elseif ($appointment_date < $today) {
        $error_message = "Appointment Date cannot be in the past.";
    } else {
        $stmt = $pdo->prepare("UPDATE appointments SET patient_name = ?, doctor = ?, appointment_date = ?, appointment_time = ?, status = ? WHERE id = ?");
        $stmt->execute([
            $patient_name,
            $doctor,
            $appointment_date,
            $_POST['appointment_time'],
            $_POST['status'],
            (int)$_POST['id']
        ]);
        $success_message = "Appointment updated successfully!";
    }
}

// Handle Appointment Deletion
if (isset($_GET['delete_appointment_id'])) {
    $id = (int)$_GET['delete_appointment_id'];
    $pdo->exec("DELETE FROM appointments WHERE id = $id");
    $success_message = "Appointment deleted successfully!";
}

// Handle Patient Edit
if (isset($_POST['edit_patient'])) {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $date_of_birth = $_POST['date_of_birth'];

    // Validate Full Name
    if (!preg_match("/^[A-Za-z\s]+$/", $full_name)) {
        $error_message = "Full Name must contain only alphabetic characters and spaces.";
    }
    // Validate Email
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format.";
    }
    // Validate Phone Number
    elseif (!preg_match("/^[0-9]{10}$/", $phone)) {
        $error_message = "Phone number must be exactly 10 digits and contain only numbers.";
    }
    // Validate Date of Birth (18 or older)
    else {
        $dob = new DateTime($date_of_birth);
        $today = new DateTime();
        $age = $today->diff($dob)->y;
        if ($age < 18) {
            $error_message = "Patient must be 18 years or older.";
        } else {
            $stmt = $pdo->prepare("UPDATE patients SET full_name = ?, email = ?, phone = ?, address = ?, gender = ?, date_of_birth = ? WHERE id = ?");
            $stmt->execute([
                $full_name,
                $email,
                $phone,
                $_POST['address'],
                $_POST['gender'],
                $date_of_birth,
                (int)$_POST['id']
            ]);
            $success_message = "Patient updated successfully!";
        }
    }
}

// Handle Patient Deletion
if (isset($_GET['delete_patient_id'])) {
    $id = (int)$_GET['delete_patient_id'];
    $pdo->exec("DELETE FROM patients WHERE id = $id");
    $success_message = "Patient deleted successfully!";
}
// Handle Feedback Deletion
if (isset($_GET['delete_feedback_id'])) {
    $id = (int)$_GET['delete_feedback_id'];
    $stmt = $pdo->prepare("DELETE FROM feedback WHERE id = ?");
    $stmt->execute([$id]);
    $success_message = "Feedback deleted successfully!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dentricks Admin Dashboard</title>
    <style>
        :root {
            --primary-color: #2A5C82;
            --secondary-color: #5C9DAD;
            --background-color: #f5f7fa;
            --sidebar-width: 250px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            display: flex;
            background-color: var(--background-color);
            min-height: 100vh;
        }

        .sidebar {
            width: var(--sidebar-width);
            background: var(--primary-color);
            color: white;
            height: 100vh;
            padding: 20px;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .sidebar .branding {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
            width: 100%;
            justify-content: center;
        }

        .sidebar .branding img {
            width: 60px;
            height: 50px;
            margin-right: 10px;
        }

        .sidebar .branding h2 {
            font-size: 24px;
            color: white;
            font-weight: bold;
            margin: 0;
        }

        .sidebar button {
            display: block;
            width: 100%;
            padding: 15px;
            margin-bottom: 15px;
            background-color: var(--primary-color);
            border: none;
            color: white;
            text-align: left;
            font-size: 18px;
            cursor: pointer;
            border-radius: 8px;
            transition: background-color 0.3s;
        }

        .sidebar button:hover {
            background-color: #1565c0;
        }

        .content {
            margin-left: var(--sidebar-width);
            padding: 30px;
            flex: 1;
        }

        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            text-align: center;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.08);
            text-align: center;
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card.updating {
            opacity: 0.7;
        }

        .stat-card h3 {
            color: #666;
            font-size: 16px;
            margin-bottom: 10px;
        }

        .stat-card .number {
            font-size: 32px;
            font-weight: bold;
            color: var(--secondary-color);
        }

        .total-patients { color: #3498db; }
        .appointments { color: #2ecc71; }
        .doctors { color: #9b59b6; }

        .card, .form-container {
            background: white;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            border-radius: 5px;
        }

        h1, h2 {
            margin-bottom: 20px;
            color: var(--primary-color);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        tr:hover {
            background-color: #f5f5f5;
        }

        .form-container input, .form-container select, .form-container textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .form-container button {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
        }

        .form-container button:hover {
            background-color: #1565c0;
        }

        .success { color: green; }
        .error { color: red; }

        .edit-form input, .edit-form select {
            width: 90%;
            padding: 5px;
        }

        .edit-form button {
            padding: 5px 10px;
            margin: 5px 0;
        }

        @media (max-width: 768px) {
            :root {
                --sidebar-width: 200px;
            }
            .content {
                margin-left: var(--sidebar-width);
                padding: 15px;
            }
            .stats-grid {
                grid-template-columns: 1fr;
            }
            .sidebar .branding h2 {
                font-size: 20px;
            }
            .sidebar .branding img {
                width: 30px;
                height: 40px;
                margin-right: 5px;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="branding">
            <img src="logo.png" alt="Dentricks Logo">
            <h2>Dentricks</h2>
        </div>
        <button onclick="loadSection('dashboard')">Dashboard</button>
        <button onclick="loadSection('appointments')">Appointments</button>
        <button onclick="loadSection('patients')">Patients</button>
        <button onclick="loadSection('doctors')">Doctors</button>
        <button onclick="loadSection('services')">Services</button>
        <button onclick="loadSection('schedule')">Schedule</button>
        <button onclick="loadSection('feedback')">Feedback</button>
        <button onclick="loadSection('settings')">Settings</button>
        <button onclick="logout()">Logout</button>
    </div>

    <!-- Content Area -->
    <div class="content" id="main-content">
        <?php if (isset($success_message)) echo "<p class='success'>$success_message</p>"; ?>
        <?php if (isset($error_message)) echo "<p class='error'>$error_message</p>"; ?>

        <?php
        switch ($section) {
            case 'dashboard':
                ?>
                <div class="dashboard-container">
                    <div class="header">
                        <h1>Dentricks Admin Dashboard</h1>
                        <p>Date: <span id="current-date"><?php echo date('F j, Y'); ?></span></p>
                        <p>Last Updated: <span id="last-updated"><?php echo $last_updated; ?></span></p>
                    </div>

                    <div class="stats-grid">
                        <div class="stat-card">
                            <h3>Total Patients</h3>
                            <div class="number total-patients" id="total-patients"><?php echo $total_patients; ?></div>
                        </div>
                        <div class="stat-card">
                            <h3>Today's Appointments</h3>
                            <div class="number appointments" id="total-appointments"><?php echo $total_appointments; ?></div>
                        </div>
                        <div class="stat-card">
                            <h3>Total Doctors</h3>
                            <div class="number doctors" id="total-doctors"><?php echo $total_doctors; ?></div>
                        </div>
                    </div>
                </div>
                <?php
                break;

            case 'appointments':
                $stmt = $pdo->query("SELECT * FROM appointments ORDER BY appointment_date, appointment_time");
                ?>
                <h2>Appointments</h2>
                <table>
                    <tr>
                        <th>ID</th>
                        <th>Patient Name</th>
                        <th>Doctor Name</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                    <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                        <tr>
                            <td><?= htmlspecialchars($row['id']) ?></td>
                            <td>
                                <form method="POST" action="" class="edit-form" onsubmit="return validateAppointmentForm(this)">
                                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                    <input type="text" name="patient_name" value="<?= htmlspecialchars($row['patient_name']) ?>" required>
                            </td>
                            <td><input type="text" name="doctor" value="<?= htmlspecialchars($row['doctor']) ?>" required></td>
                            <td><input type="date" name="appointment_date" value="<?= htmlspecialchars($row['appointment_date']) ?>" required min="<?php echo date('Y-m-d'); ?>"></td>
                            <td><input type="time" name="appointment_time" value="<?= htmlspecialchars($row['appointment_time']) ?>" required></td>
                            <td>
                                <select name="status" required>
                                    <option value="Pending" <?= $row['status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="Confirmed" <?= $row['status'] == 'Confirmed' ? 'selected' : '' ?>>Confirmed</option>
                                    <option value="Completed" <?= $row['status'] == 'Completed' ? 'selected' : '' ?>>Completed</option>
                                    <option value="Canceled" <?= $row['status'] == 'Canceled' ? 'selected' : '' ?>>Canceled</option>
                                </select>
                            </td>
                            <td>
                                <button type="submit" name="edit_appointment">Save</button>
                                </form>
                                <a href="?section=appointments&delete_appointment_id=<?= $row['id'] ?>" onclick="return confirm('Are you sure?');">Delete</a>
                            </td>
                        </tr>
                    <?php } ?>
                </table>
                <?php
                break;

            case 'patients':
                $stmt = $pdo->query("SELECT * FROM patients ORDER BY registration_date DESC");
                ?>
                <h2>Patients</h2>
                <table>
                    <tr>
                        <th>ID</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Address</th>
                        <th>Gender</th>
                        <th>Date of Birth</th>
                        <th>Registration Date</th>
                        <th>Action</th>
                    </tr>
                    <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                        <tr>
                            <td><?= htmlspecialchars($row['id']) ?></td>
                            <td>
                                <form method="POST" action="" class="edit-form" onsubmit="return validatePatientForm(this)">
                                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                    <input type="text" name="full_name" value="<?= htmlspecialchars($row['full_name']) ?>" required>
                            </td>
                            <td><input type="email" name="email" value="<?= htmlspecialchars($row['email']) ?>" required></td>
                            <td><input type="text" name="phone" value="<?= htmlspecialchars($row['phone']) ?>" required></td>
                            <td><input type="text" name="address" value="<?= htmlspecialchars($row['address']) ?>" required></td>
                            <td>
                                <select name="gender" required>
                                    <option value="Male" <?= $row['gender'] == 'Male' ? 'selected' : '' ?>>Male</option>
                                    <option value="Female" <?= $row['gender'] == 'Female' ? 'selected' : '' ?>>Female</option>
                                    <option value="Other" <?= $row['gender'] == 'Other' ? 'selected' : '' ?>>Other</option>
                                </select>
                            </td>
                            <td><input type="date" name="date_of_birth" value="<?= htmlspecialchars($row['date_of_birth']) ?>" required></td>
                            <td><?= htmlspecialchars($row['registration_date']) ?></td>
                            <td>
                                <button type="submit" name="edit_patient">Save</button>
                                </form>
                                <a href="?section=patients&delete_patient_id=<?= $row['id'] ?>" onclick="return confirm('Are you sure?');">Delete</a>
                            </td>
                        </tr>
                    <?php } ?>
                </table>
                <?php
                break;

            case 'doctors':
                $stmt = $pdo->query("SELECT * FROM doctors");
                ?>
                <h2>Doctors</h2>
                <div class="form-container">
                    <h3>Add Doctor</h3>
                    <form method="POST" action="" onsubmit="return validateDoctorForm(this)">
                        <input type="text" name="name" placeholder="Name" required>
                        <input type="text" name="specialization" placeholder="Specialization" required>
                        <input type="text" name="contact" placeholder="Contact" required>
                        <input type="email" name="email" placeholder="Email" required>
                        <textarea name="bio" placeholder="Bio" required></textarea>
                        <button type="submit" name="add_doctor">Add Doctor</button>
                    </form>
                </div>
                <table>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Specialization</th>
                        <th>Contact</th>
                        <th>Email</th>
                        <th>Action</th>
                    </tr>
                    <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                        <tr>
                            <td><?= htmlspecialchars($row['id']) ?></td>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td><?= htmlspecialchars($row['specialization']) ?></td>
                            <td><?= htmlspecialchars($row['contact']) ?></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td>
                                <a href="?section=doctors&delete_doctor_id=<?= $row['id'] ?>" onclick="return confirm('Are you sure?')">Delete</a>
                            </td>
                        </tr>
                    <?php } ?>
                </table>
                <?php
                break;

            case 'services':
                $stmt = $pdo->query("SELECT * FROM services");
                ?>
                <h2>Services</h2>
                <div class="form-container">
                    <h3>Add Service</h3>
                    <form method="POST" action="" enctype="multipart/form-data" onsubmit="return validateServiceForm(this)">
                        <input type="text" name="service_name" placeholder="Service Name" required>
                        <textarea name="description" placeholder="Description" required></textarea>
                        <input type="file" name="photo" required>
                        <button type="submit" name="add_service">Add Service</button>
                    </form>
                </div>
                <table>
                    <tr>
                        <th>ID</th>
                        <th>Service Name</th>
                        <th>Description</th>
                        <th>Photo</th>
                        <th>Action</th>
                    </tr>
                    <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                        <tr>
                            <td><?= htmlspecialchars($row['id']) ?></td>
                            <td><?= htmlspecialchars($row['service_name']) ?></td>
                            <td><?= htmlspecialchars($row['description']) ?></td>
                            <td><img src="uploads/<?= htmlspecialchars($row['photo']) ?>" width="100" alt="Service Photo"></td>
                            <td>
                                <a href="?section=services&delete_service_id=<?= $row['id'] ?>" onclick="return confirm('Are you sure?')">Delete</a>
                            </td>
                        </tr>
                    <?php } ?>
                </table>
                <?php
                break;

            case 'schedule':
                $search_date = isset($_GET['search_date']) ? $_GET['search_date'] : '';
                $appointments = [];
                if (!empty($search_date)) {
                    $stmt = $pdo->prepare("SELECT * FROM appointments WHERE appointment_date = ? ORDER BY appointment_time");
                    $stmt->execute([$search_date]);
                    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
                }
                ?>
                <h2>Search Appointments by Date</h2>
                <div class="form-container">
                    <form method="GET" action="">
                        <input type="hidden" name="section" value="schedule">
                        <input type="date" name="search_date" value="<?= htmlspecialchars($search_date) ?>" required min="<?php echo date('Y-m-d'); ?>">
                        <button type="submit">Search</button>
                    </form>
                </div>
                <?php if (!empty($search_date)) { ?>
                    <h3>Appointments on <?= htmlspecialchars($search_date) ?></h3>
                    <?php if (empty($appointments)) { ?>
                        <p>No appointments found for this date.</p>
                    <?php } else { ?>
                        <table>
                            <tr>
                                <th>ID</th>
                                <th>Patient Name</th>
                                <th>Doctor Name</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Status</th>
                            </tr>
                            <?php foreach ($appointments as $appointment) { ?>
                                <tr>
                                    <td><?= htmlspecialchars($appointment['id']) ?></td>
                                    <td><?= htmlspecialchars($appointment['patient_name']) ?></td>
                                    <td><?= htmlspecialchars($appointment['doctor']) ?></td>
                                    <td><?= htmlspecialchars($appointment['appointment_date']) ?></td>
                                    <td><?= htmlspecialchars($appointment['appointment_time']) ?></td>
                                    <td><?= htmlspecialchars($appointment['status']) ?></td>
                                </tr>
                            <?php } ?>
                        </table>
                    <?php } ?>
                <?php } ?>
                <?php
                break;

            case 'feedback':
                $stmt = $pdo->query("SELECT * FROM feedback");
                ?>
                <h2>Feedback Management</h2>
                <table>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Feedback</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                    <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                        <tr>
                            <td><?= htmlspecialchars($row['id']) ?></td>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td><?= htmlspecialchars($row['feedback']) ?></td>
                            <td><?= htmlspecialchars($row['created_at']) ?></td>
                            <td>
                                <a href="?section=feedback&delete_feedback_id=<?= $row['id'] ?>" onclick="return confirm('Are you sure?')">Delete</a>
                            </td>
                        </tr>
                    <?php } ?>
                </table>
                <?php
                break;

            case 'settings':
                ?>
                <h2>Settings Management</h2>
                <div class="form-container">
                    <h3>Update Admin Username</h3>
                    <form method="POST" action="" onsubmit="return validateSettingsUsernameForm(this)">
                        <label for="username">Username:</label>
                        <input type="text" id="username" name="username" value="<?= htmlspecialchars($admin_username) ?>" required>
                        <button type="submit" name="update_settings">Update Username</button>
                    </form>
                </div>
                <div class="form-container">
                    <h3>Change Admin Password</h3>
                    <form method="POST" action="" onsubmit="return validateSettingsPasswordForm(this)">
                        <label for="new_password">New Password:</label>
                        <input type="password" id="new_password" name="new_password" required>
                        <label for="confirm_password">Confirm Password:</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                        <button type="submit" name="change_password">Change Password</button>
                    </form>
                </div>
                <?php
                break;

            default:
                echo "<h1>Section not found</h1>";
                break;
        }
        ?>
    </div>

    <script>
        function loadSection(section) {
            window.location.href = "?section=" + section;
        }

        function logout() {
            // Show confirmation dialog
            if (confirm('Are you sure you want to logout?')) {
                // Show success message and redirect
                alert('You have logged out successfully!');
                window.location.href = "admin_logout.php";
            }
        }

        function updateDashboard() {
            if (document.getElementById('total-patients')) {
                document.querySelectorAll('.stat-card').forEach(card => card.classList.add('updating'));

                fetch('?action=get_stats')
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('total-patients').textContent = data.total_patients || 0;
                        document.getElementById('total-appointments').textContent = data.total_appointments || 0;
                        document.getElementById('total-doctors').textContent = data.total_doctors || 0;

                        const now = new Date();
                        document.getElementById('last-updated').textContent = now.toLocaleTimeString('en-IN', { timeZone: 'Asia/Kolkata' });
                        document.querySelectorAll('.stat-card').forEach(card => card.classList.remove('updating'));
                    })
                    .catch(error => {
                        console.error('Fetch Error:', error);
                        document.getElementById('last-updated').textContent = 'Fetch Error';
                    });
            }
        }

        if (window.location.search.includes('section=dashboard') || window.location.search === '') {
            updateDashboard();
            setInterval(updateDashboard, 30000);
        }

        setInterval(() => {
            const dateElement = document.getElementById('current-date');
            if (dateElement) {
                dateElement.textContent = new Date().toLocaleDateString('en-IN', {
                    month: 'long',
                    day: 'numeric',
                    year: 'numeric',
                    timeZone: 'Asia/Kolkata'
                });
            }
        }, 60000);

        function validateAppointmentForm(form) {
            const patientName = form.querySelector('input[name="patient_name"]').value.trim();
            const doctorName = form.querySelector('input[name="doctor"]').value.trim();
            const appointmentDate = form.querySelector('input[name="appointment_date"]').value;
            const today = new Date().toISOString().split('T')[0]; // Current date in YYYY-MM-DD format

            // Regular expressions for validation
            const nameRegex = /^[A-Za-z\s]+$/; // Alphabets and spaces only

            // Validate Patient Name
            if (!nameRegex.test(patientName)) {
                alert('Patient Name must contain only alphabetic characters and spaces.');
                return false;
            }

            // Validate Doctor Name
            if (!nameRegex.test(doctorName)) {
                alert('Doctor Name must contain only alphabetic characters and spaces.');
                return false;
            }

            // Validate Appointment Date (no past dates)
            if (appointmentDate < today) {
                alert('Appointment Date cannot be in the past. Please select today or a future date.');
                return false;
            }

            // If all validations pass, allow form submission
            return true;
        }

        function validatePatientForm(form) {
            const fullName = form.querySelector('input[name="full_name"]').value.trim();
            const email = form.querySelector('input[name="email"]').value.trim();
            const phone = form.querySelector('input[name="phone"]').value.trim();
            const dateOfBirth = form.querySelector('input[name="date_of_birth"]').value;

            // Regular expressions for validation
            const nameRegex = /^[A-Za-z\s]+$/; // Alphabets and spaces only
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/; // Basic email format
            const phoneRegex = /^[0-9]{10}$/; // Exactly 9 digits

            // Validate Full Name
            if (!nameRegex.test(fullName)) {
                alert('Full Name must contain only alphabetic characters and spaces.');
                return false;
            }

            // Validate Email
            if (!emailRegex.test(email)) {
                alert('Please enter a valid email address.');
                return false;
            }

            // Validate Phone Number
            if (!phoneRegex.test(phone)) {
                alert('Phone number must be exactly 10 digits and contain only numbers.');
                return false;
            }

            // Validate Date of Birth (must be 18 or older)
            const today = new Date();
            const dob = new Date(dateOfBirth);
            let age = today.getFullYear() - dob.getFullYear();
            const monthDiff = today.getMonth() - dob.getMonth();
            const dayDiff = today.getDate() - dob.getDate();

            if (monthDiff < 0 || (monthDiff === 0 && dayDiff < 0)) {
                age--;
            }

            if (age < 18) {
                alert('Patient must be 18 years or older.');
                return false;
            }

            // If all validations pass, allow form submission
            return true;
        }

        function validateDoctorForm(form) {
            const name = form.querySelector('input[name="name"]').value.trim();
            const specialization = form.querySelector('input[name="specialization"]').value.trim();
            const contact = form.querySelector('input[name="contact"]').value.trim();
            const email = form.querySelector('input[name="email"]').value.trim();

            // Regular expressions for validation
            const nameRegex = /^[A-Za-z\s]+$/; // Alphabets and spaces only
            const phoneRegex = /^[0-9]{10}$/; // Exactly 9 digits
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/; // Basic email format

            // Validate Name
            if (!nameRegex.test(name)) {
                alert('Doctor Name must contain only alphabetic characters and spaces.');
                return false;
            }

            // Validate Specialization
            if (!nameRegex.test(specialization)) {
                alert('Specialization must contain only alphabetic characters and spaces.');
                return false;
            }

            // Validate Contact
            if (!phoneRegex.test(contact)) {
                alert('Contact number must be exactly 10 digits and contain only numbers.');
                return false;
            }

            // Validate Email
            if (!emailRegex.test(email)) {
                alert('Please enter a valid email address.');
                return false;
            }

            // If all validations pass, allow form submission
            return true;
        }

        function validateServiceForm(form) {
            const serviceName = form.querySelector('input[name="service_name"]').value.trim();
            const description = form.querySelector('textarea[name="description"]').value.trim();

            // Regular expressions for validation
            const nameRegex = /^[A-Za-z\s]+$/; // Alphabets and spaces only

            // Validate Service Name
            if (!nameRegex.test(serviceName)) {
                alert('Service Name must contain only alphabetic characters and spaces.');
                return false;
            }

            // Validate Description
            if (!nameRegex.test(description)) {
                alert('Description must contain only alphabetic characters and spaces.');
                return false;
            }

            // If all validations pass, allow form submission
            return true;
        }

        function validateSettingsUsernameForm(form) {
            const username = form.querySelector('input[name="username"]').value.trim();

            // Regular expression for validation
            const nameRegex = /^[A-Za-z\s]+$/; // Alphabets and spaces only

            // Validate Username
            if (!nameRegex.test(username)) {
                alert('Username must contain only alphabetic characters and spaces.');
                return false;
            }

            // If validation passes, allow form submission
            return true;
        }

        function validateSettingsPasswordForm(form) {
            const newPassword = form.querySelector('input[name="new_password"]').value;
            const confirmPassword = form.querySelector('input[name="confirm_password"]').value;

            // Validate Password Length
            if (newPassword.length < 6) {
                alert('New password must be at least 6 characters long.');
                return false;
            }

            // Validate Password Match
            if (newPassword !== confirmPassword) {
                alert('New password and confirm password do not match.');
                return false;
            }

            // If all validations pass, allow form submission
            return true;
        }
    </script>
</body>
</html>
<?php
$pdo = null; // Close PDO connection
?>