<?php
require_once "config.php";
session_start();

// Strict route safeguard: eject unauthenticated users back out to portal gate
if (!isset($_SESSION["user_id"])) {
    header("location: login.php");
    exit;
}

$user_id = $_SESSION["user_id"];
$msg = "";

// Process layout custom profile text alterations
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $display_name = trim($_POST["display_name"]);
    $bio_about = $_POST["bio_about"];
    $bio_interests = $_POST["bio_interests"];

    $stmt = $pdo->prepare("UPDATE profiles SET display_name = ?, bio_about = ?, bio_interests = ? WHERE user_id = ?");
    if ($stmt->execute([$display_name, $bio_about, $bio_interests, $user_id])) {
        $msg = "Profile update saved successfully!";
    }
}

// Retrieve valid baseline profiles variables settings to pre-fill web text boxes
$stmt = $pdo->prepare("SELECT * FROM profiles WHERE user_id = ?");
$stmt->execute([$user_id]);
$profile = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Profile Workspace</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="site-header">
    <div class="logo">🌸 PinkSpace Dashboard 🌸</div>
    <nav>
        <a href="profile.php?user=<?= urlencode($_SESSION['username']) ?>">View My Live Page</a> | 
        <a href="logout.php" style="color: yellow;">Log Out</a>
    </nav>
</header>
<main class="container" style="display: block;">
    <h2>Welcome back, <?= htmlspecialchars($_SESSION["username"]) ?>!</h2>
    
    <?php if (!empty($msg)): ?>
        <p style="color: green; font-weight: bold;"><?= htmlspecialchars($msg) ?></p>
    <?php endif; ?>

    <form action="dashboard.php" method="POST">
        <p>
            <label><b>Public Display Name:</b></label><br>
            <input type="text" name="display_name" value="<?= htmlspecialchars($profile['display_name'] ?? '') ?>" style="width: 98%; border: 1px solid #ff1a8c; padding: 4px;">
        </p>
        
        <p>
            <label><b>About Me Field (You can inject raw HTML layout blocks or custom CSS &lt;style&gt; configurations here!):</b></label><br>
            <textarea name="bio_about" rows="10" style="width: 98%; border: 1px solid #ff1a8c; font-family: monospace;"><?= htmlspecialchars($profile['bio_about'] ?? '') ?></textarea>
        </p>
        
        <p>
            <label><b>Interests & Hobbies Section:</b></label><br>
            <textarea name="bio_interests" rows="5" style="width: 98%; border: 1px solid #ff1a8c;"><?= htmlspecialchars($profile['bio_interests'] ?? '') ?></textarea>
        </p>
        
        <p>
            <input type="submit" value="Save Profile Layout Alterations" style="background: #ff1a8c; color: white; border: none; font-weight: bold; padding: 8px 16px; cursor: pointer;">
        </p>
    </form>
</main>
</body>
</html>
