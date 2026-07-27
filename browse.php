<?php
require_once "config.php";
session_start();

// Fetch all registered users along with their public profile information
$stmt = $pdo->query("
    SELECT u.username, p.display_name, p.avatar_url, p.bio_about 
    FROM users u 
    JOIN profiles p ON u.id = p.user_id 
    ORDER BY u.id DESC
");
$members = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Browse PinkSpace Members</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header class="site-header">
    <div class="logo">🌸 PinkSpace Member Directory 🌸</div>
    <nav>
        <a href="index.php">Home</a> | 
        <?php if(isset($_SESSION['user_id'])): ?>
            <a href="dashboard.php">Dashboard</a> | 
            <a href="profile.php?user=<?= urlencode($_SESSION['username']) ?>">My Profile</a>
        <?php else: ?>
            <a href="login.php">Login</a> | 
            <a href="register.php">Sign Up</a>
        <?php endif; ?>
    </nav>
</header>

<main class="container" style="display: block; max-width: 800px;">
    <h2>Community Members Directory</h2>
    <p style="padding-left: 5px;">Discover new friends and explore their custom retro profile spaces below!</p>
    
    <div class="directory-list">
        <?php if(empty($members)): ?>
            <p style="text-align:center; padding: 20px; color: #999;">No members have registered on the network yet.</p>
        <?php else: ?>
            <?php foreach($members as $member): ?>
                <div class="directory-card">
                    <div class="directory-avatar-box">
                        <img src="<?= htmlspecialchars($member['avatar_url']) ?>" alt="User Photo">
                    </div>
                    <div class="directory-info-box">
                        <h3>
                            <a href="profile.php?user=<?= urlencode($member['username']) ?>">
                                <?= htmlspecialchars($member['display_name']) ?> (@<?= htmlspecialchars($member['username']) ?>)
                            </a>
                        </h3>
                        <div class="directory-snippet">
                            <!-- Strips layout markup code to show a short readable text preview string -->
                            <?= htmlspecialchars(substr(strip_tags($member['bio_about']), 0, 150)) ?>...
                        </div>
                        <a href="profile.php?user=<?= urlencode($member['username']) ?>" class="view-space-link">
                            Visit Space Layout ➔
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>

</body>
</html>
