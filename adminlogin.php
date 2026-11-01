<?php
session_start();

// Database configuration
$host = 'localhost';
$dbname = 'dental';
$username = 'root';
$password = '';
$error = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);

        // Fetch admin details
        $stmt = $pdo->prepare("SELECT id, username, password FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        // Verify password using password_verify
        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['username'];
            header('Location: admin_final.php'); // Redirect to admin dashboard
            exit();
        } else {
            $error = 'Invalid credentials';
        }
    }
} catch(PDOException $e) {
    $error = 'Database error: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Dental Clinic</title>
    <style>
        .desktop-1 {
            background: #36c7c7;
            width: 100%;
            position: fixed;
            min-height: 50px;
            left: 0px;
            padding: 10px 0; /* Fixed typo: 'padding:10px o' to 'padding:10px 0' */
            top: 0px;
            overflow: hidden;
            z-index: 1000;
        }
        .Dentrics {
            color: red;
            text-align: left;
            font-family: "Jaldi-Regular", sans-serif;
            font-size: 30px;
            font-weight: 400;
            position: absolute;
            left: 35px;
            top: 6px;
            width: 57px;
            height: 40.07px;
        }
        .image-1 {
            width: 35px;
            height: 45px;
            position: absolute;
            left: 220px;
            top: 5px;
        }
        body {
            background: linear-gradient(to bottom, lightblue, white);
            height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
    <!-- Header Section -->
    <div class="desktop-1">
        <div class="Dentrics"><b>DENTRICKS</b></div>
        <img class="image-1" src="logo.png" alt="Dentrics Logo">
    </div>
<body class="bg-gray-100 h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow-md w-96 mt-20">
        <h1 class="text-2xl font-bold text-center mb-6 text-blue-600">Admin Login</h1>
        
        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-gray-700 mb-2">Username</label>
                <input type="text" name="username" 
                       class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                       required>
            </div>
            
            <div>
                <label class="block text-gray-700 mb-2">Password</label>
                <input type="password" name="password" 
                       class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                       required>
            </div>

            <button type="submit" 
                    class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition-colors">
                Login
            </button>
        </form>
    </div>
</body>
</html>