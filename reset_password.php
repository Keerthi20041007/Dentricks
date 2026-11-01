<?php
session_start();
$error = '';

// Database configuration
$db_host = 'localhost';
$db_name = 'dental';
$db_user = 'root';
$db_pass = '';

// Create connection
try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['email'])) {
        // Step 1: Verify email
        $email = htmlspecialchars(trim($_POST['email']));
        
        $stmt = $pdo->prepare("SELECT * FROM patients WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user) {
            $_SESSION['reset_user'] = $email; // Store email in session
        } else {
            $error = "Email not found";
        }
    } 
	elseif (isset($_POST['new_password'])) {
    if (!isset($_SESSION['reset_user'])) {
        die("Invalid session");
    }

    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Check minimum length
    if (strlen($new_password) < 6) {
        $error = "Password must be at least 6 characters long";
    }
    // Check if passwords match
    elseif ($new_password !== $confirm_password) {
        $error = "Passwords do not match";
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $email = $_SESSION['reset_user'];
        
        $stmt = $pdo->prepare("UPDATE patients SET password = ? WHERE email = ?");
        if ($stmt->execute([$hashed_password, $email])) {
            session_destroy();
            header("Location: prolog.html?reset=success");
            exit();
        } else {
            $error = "Password update failed";
        }
    }
}
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Password Reset</title>
    <style>
        html, body {
            overflow-y: hidden;
            margin: 0;
            padding: 0;
            height: 100%;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            max-width: 400px;
            width: 100%;
            position: absolute;
            top: 150px;
            left: 500px;
            padding: 30px;
            background-color: #ffffff;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            text-align: center;
        }

        h2 {
            margin-bottom: 20px;
            color: #333;
            font-size: 24px;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-size: 14px;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            box-sizing: border-box;
        }

        .form-group input:focus {
            border-color: #007bff;
            outline: none;
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #0056b3;
        }

        .error {
            color: #ff0000;
            margin-bottom: 15px;
            font-size: 14px;
        }

        .success {
            color: #28a745;
            margin-bottom: 15px;
            font-size: 14px;
        }
        .Dentrics {
            color: red;
            text-align: left;
            font-family: "Jaldi-Regular", sans-serif;
            font-size: 30px;
            font-weight: 400;
            position: absolute;
            left: 35px;
            top: 10px;
            width: 57px;
            height: 40.07px;
        }
        .image-1 {
            width: 35px;
            height: 45px;
            position: absolute;
            left: 215px;
            top: 5px;
        }
        .image1 {
            width: 100%;
            height: 645px;
            position: absolute;
            top: 50px;
        }
        .desktop-1 {
            background: #36c7c7;
            width: 100%;
            position: fixed;
            min-height: 30px;
            left: 0px;
            padding: 10px 0;
            top: 0px;
            overflow: hidden;
            z-index: 1000;
        }
    </style>
</head>
<body>
    <div class="desktop-1">
        <div class="Dentrics"><b>DENTRICKS</b></div>
        <img class="image-1" src="logo.png" alt="Logo">
    </div>
    <img class="image1" src="AdobeStock_267804683.jpeg">
    <div class="container">
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if (!isset($_SESSION['reset_user'])): ?>
            <!-- Step 1: Email Verification -->
            <h2>Reset Password</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Email:</label>
                    <input type="email" name="email" required>
                </div>
                <button type="submit">Continue</button>
            </form>
        <?php else: ?>
            <!-- Step 2: New Password -->
            <h2>Set New Password</h2>
            <form method="POST" onsubmit="return validatePassword()">
                <div class="form-group">
                    <label>New Password:</label>
                    <input type="password" name="new_password" id="new_password" required>
                </div>
                <div class="form-group">
                    <label>Confirm Password:</label>
                    <input type="password" name="confirm_password" id="confirm_password" required>
                </div>
                <button type="submit">Reset Password</button>
            </form>
        <?php endif; ?>
    </div>

    <script>
        function validatePassword() {
            // Get the password field values
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;

            // Check if new password is at least 6 characters
            if (newPassword.length < 6) {
                alert('New password must be at least 6 characters long.');
                return false;
            }

            // Check if passwords match (already handled server-side, but added here for consistency)
            if (newPassword !== confirmPassword) {
                alert('Passwords do not match.');
                return false;
            }

            // If all validations pass, allow form submission
            return true;
        }
    </script>
</body>
</html>