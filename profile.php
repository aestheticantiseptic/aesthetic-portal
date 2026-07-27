<?php
require_once "config.php";
session_start();

// Identify which user profile to fetch via URL parameters
$username = $_GET['user'] ?? '';

$stmt = $pdo->prepare("SELECT u.username, p.* FROM users u JOIN profiles p ON u.id = p.user_id WHERE u.username = ?");
$stmt->execute([$username]);
$profile = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$profile) {
    die("User profile not found.");
}

// Security function to prevent XSS while allowing layout styling injection
function clean_retro_code($dirty_html) {
    // Strips runtime scripts and event actions
    $clean = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', "", $dirty_html);
    $clean = preg_replace('/on\w+\s*=\s*"[^"]*"/i', "", $clean);
    $clean = preg_replace('/on\w+\s*=\s*\'[^\']*\'/i', "", $clean);
    return $clean;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($profile['display_name']) ?>'s Pink Space</title>
    <link rel="stylesheet" href="style.css">
    
    <!-- Dynamically injects any override styles the user saved in their bio field -->
    <?= clean_retro_code($profile['bio_about']) ?>
</head>
<body>

<header class="site-header">
    <div class="logo">🌸 PinkSpace 🌸</div>
    <nav>
        <a href="index.php">Home</a> | 
        <a href="browse.php">Browse Profiles</a> | 
        <a href="login.php">Login</a>
    </nav>
</header>

<main class="container">
    <div class="left-column">
        <h1><?= htmlspecialchars($profile['display_name']) ?></h1>
        
        <div class="avatar-box">
            <img class="avatar" src="<?= htmlspecialchars($profile['avatar_url']) ?>" alt="User Avatar">
        </div>
        
        <div class="network-badge">
            "<?= htmlspecialchars($profile['username']) ?> is in your pink network"
        </div>
    </div>
    
    <div class="right-column">
        <div class="blurbs-box">
            <h2><?= htmlspecialchars($profile['display_name']) ?>'s Blurbs</h2>
            
            <h3>About me:</h3>
            <div><?= clean_retro_code($profile['bio_about']) ?></div>
            
            <h3>Interests & Hobbies:</h3>
            <div><?= clean_retro_code($profile['bio_interests']) ?></div>
        </div>
    </div>
</main>

</body>
</html>
