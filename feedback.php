<?php
include 'fed_db.php'; // Database connection


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $feedback = $_POST['feedback'];

    // Validate name (alphabets and spaces only)
    if (!preg_match("/^[A-Za-z\s]+$/", $name)) {
        die("Invalid name. Only alphabetic characters and spaces are allowed.");
    }

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Invalid email format.");
    }

    // Prevent SQL Injection
    $name = mysqli_real_escape_string($conn, $name);
    $email = mysqli_real_escape_string($conn, $email);
    $feedback = mysqli_real_escape_string($conn, $feedback);

    // Insert feedback (created_at is automatically handled by MySQL)
    $sql = "INSERT INTO feedback (name, email, feedback) VALUES ('$name', '$email', '$feedback')";

    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Feedback submitted successfully!'); window.location.href='code.php';</script>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }

    mysqli_close($conn);
}
?>