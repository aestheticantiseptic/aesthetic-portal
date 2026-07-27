<?php
require_once "config.php";
session_start();

$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    // Validation
    if (empty($username) || empty($email) || empty($password)) {
        $errors[] = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters.";
    }

    if (empty($errors)) {
        // Check if username/email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            $errors[] = "Username or Email is already taken.";
        } else {
            // Hash password and insert user securely
            $password_hash = password_hash($password, PASSWORD_BCRYPT);
            
            try {
                $pdo->beginTransaction();

                $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
                $stmt->execute([$username, $email, $password_hash]);
                $user_id = $pdo->lastInsertId();

                // Create accompanying empty profile record immediately
                $stmtProfile = $pdo->prepare("INSERT INTO profiles (user_id, display_name, bio_about, bio_interests) VALUES (?, ?, 'Welcome to my space!', 'Coding, Retro Web, Music')");
                $stmtProfile->execute([$user_id, $username]);

                $pdo->commit();
                
                $_SESSION["user_id"] = $user_id;
                $_SESSION["username"] = $username;
                header("location: dashboard.php");
                exit;
            } catch (Exception $e) {
                $pdo->rollBack();
                $errors[] = "Registration failed. Try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Join PinkSpace</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="site-header">
    <div class="logo">🌸 PinkSpace Signup 🌸</div>
    <nav><a href="login.php">Back to Login</a></nav>
</header>
<main class="container" style="max-width: 400px; display: block;">
    <h2>Create an Account</h2>
    
    <?php foreach ($errors as $error): ?>
        <p style="color: red; font-weight: bold;"><?= htmlspecialchars($error) ?></p>
    <?php endforeach; ?>

    <form action="register.php" method="POST">
        <p>
            <label>Username:</label><br>
            <input type="text" name="username" required style="width:95%; border:1px solid #ff1a8c;">
        </p>
        <p>
            <label>Email Address:</label><br>
            <input type="email" name="email" required style="width:95%; border:1px solid #ff1a8c;">
        </p>
        <p>
            <label>Password:</label><br>
            <input type="password" name="password" required style="width:95%; border:1px solid #ff1a8c;">
        </p>
        <p>
            <input type="submit" value="Sign Up!" style="background:#ff1a8c; color:white; border:none; padding:5px 10px; cursor:pointer;">
        </p>
    </form>
</main>
</body>
</html>
