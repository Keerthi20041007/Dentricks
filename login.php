<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$conn = new mysqli('localhost', 'root', '', 'dental');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];

    $sql = "SELECT id, full_name, password FROM patients WHERE full_name = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $patient = $result->fetch_assoc();
        if (password_verify($password, $patient['password'])) {
            $_SESSION['patient_id'] = $patient['id'];
            $_SESSION['patient_name'] = $patient['full_name'];
            header("Location: code.php");
            exit();
        } else {
            echo "<script>alert('Invalid password.'); window.location.href = 'prolog.html';</script>";
            exit();
        }
    } else {
        echo "<script>alert('No user found with that username.'); window.location.href = 'prolog.html';</script>";
        exit();
    }
    $stmt->close();
}
$conn->close();
?>