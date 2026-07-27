<?php
require_once "config.php";
session_start();

// Redirect home if already logged inside a valid active session
if (isset($_SESSION["user_id"])) {
    header("location: dashboard.php");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    if (!empty($username) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT id, username, password_hash FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user["password_hash"])) {
            // Credentials match: instantiate server variables
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["username"] = $user["username"];
            header("location: dashboard.php");
            exit;
        } else {
            $error = "Invalid username or password configuration.";
        }
    } else {
        $error = "Please fill out all credentials fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PinkSpace Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="site-header">
    <div class="logo">🌸 PinkSpace Portal 🌸</div>
    <nav><a href="register.php">Sign Up Here</a></nav>
</header>
<main class="container" style="max-width: 400px; display: block;">
    <h2>Member Account Authentication</h2>
    
    <?php if (!empty($error)): ?>
        <p style="color: red; font-weight: bold;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form action="login.php" method="POST">
        <p>
            <label>Account Username:</label><br>
            <input type="text" name="username" required style="width:95%; border:1px solid #ff1a8c;">
        </p>
        <p>
            <label>Password:</label><br>
            <input type="password" name="password" required style="width:95%; border:1px solid #ff1a8c;">
        </p>
        <p>
            <input type="submit" value="Log In" style="background:#ff1a8c; color:white; border:none; padding:5px 10px; cursor:pointer;">
        </p>
    </form>
</main>
</body>
</html>
