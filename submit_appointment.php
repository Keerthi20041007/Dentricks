<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection
$conn = new mysqli('localhost', 'root', '', 'dental');
if ($conn->connect_error) {
    die("<script>alert('Connection failed: " . addslashes($conn->connect_error) . "'); window.history.back();</script>");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $doctor = $_POST['doctor'];
    $email = $_POST['email'];
    $date = $_POST['date'];
    $phone = $_POST['phone'];
    $time = $_POST['time'];

    // Validation
    if (!preg_match("/^[A-Za-z\s]+$/", $username)) {
        die("<script>alert('Invalid username. Only alphabetic characters and spaces are allowed.'); window.history.back();</script>");
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("<script>alert('Invalid email format.'); window.history.back();</script>");
    }
    if (!preg_match("/^[0-9]{10}$/", $phone)) {
        die("<script>alert('Phone number must contain exactly 10 digits and only numbers.'); window.history.back();</script>");
    }

    // Validate that the selected date is not in the past
    $selected_date = $_POST['date'];
    if (strtotime($selected_date) < strtotime(date('Y-m-d'))) {
        die("<script>alert('Error: Cannot book appointments for past dates'); window.history.back();</script>");
    }

    // Insert appointment into the database
    $sql = "INSERT INTO appointments (patient_name, doctor, appointment_date, appointment_time, status, Email, Phone_number) 
            VALUES (?, ?, ?, ?, 'Pending', ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssss", $username, $doctor, $date, $time, $email, $phone);

    if ($stmt->execute()) {
        // Show alert and redirect to patient_profile.php without parameter
        echo "<script>alert('Appointment booked successfully!'); window.location.href = 'patient_profile.php';</script>";
        exit();
    } else {
        echo "<script>alert('Error: " . addslashes($stmt->error) . "'); window.history.back();</script>";
    }

    $stmt->close();
}

$conn->close();
?>