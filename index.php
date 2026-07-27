<?php
require_once "config.php";
session_start();

// Execute data matrix counts queries to display live platform usage statistics on landing home template
$users_count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$comments_count = $pdo->query("SELECT COUNT(*) FROM profile_comments")->fetchColumn();

// Fetch the 4 newest profiles to showcase on the home wall directory grid preview layout
$new_members_stmt = $pdo->query("
    SELECT u.username, p.display_name, p.avatar_url 
    FROM users u 
    JOIN profiles p ON u.id = p.user_id 
    ORDER BY u.id DESC LIMIT 4
");
$new_members = $new_members_stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Welcome to PinkSpace!</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .welcome-hero-box {
            background-color: #fff2f8;
            border: 2px dashed #ff1a8c;
            padding: 20px;
            text-align: center;
            margin-bottom: 20px;
        }
        .stat-grid-row {
            display: flex;
            justify-content: space-around;
            background: #ff1a8c;
            color: #ffffff;
            font-weight: bold;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #4d0026;
        }
        .stat-pill {
            text-align: center;
            font-size: 14px;
        }
        .stat-number {
            font-size: 20px;
            display: block;
            color: #ffe6f2;
        }
        .portal-btn {
            display: inline-block;
            background: #ff1a8c;
            color: #ffffff !important;
            padding: 10px 20px;
            font-weight: bold;
            text-decoration: none;
            border: 2px outset #ffb3d9;
            margin: 5px;
            font-size: 14px;
        }
        .portal-btn:hover {
            background: #ff66b2;
        }
    </style>
</head>
<body>

<header class="site-header">
    <div class="logo">🌸 Welcome to PinkSpace 🌸</div>
    <nav>
        <a href="index.php">Home</a> | 
        <a href="browse.php">Browse Directory</a>
    </nav>
</header>

<main class="container" style="display: block; max-width: 800px;">
    <div class="welcome-hero-box">
        <h1 style="color: #ff1a8c; margin-top: 0;">An Elegant Retro Space for Creative Minds</h1>
        <p style="font-size: 13px; line-height: 1.5;">
            Welcome to the ultimate retro pink social playground! Express your true identity by building custom layout coding blocks, customizing theme stylesheet options, connecting with cool profiles, and scraping fun comments on your friends' space walls.
        </p>
        
        <div style="margin-top: 20px;">
            <?php if(isset($_SESSION['user_id'])): ?>
                <p>Logged in as: <b>@<?= htmlspecialchars($_SESSION['username']) ?></b></p>
                <a href="dashboard.php" class="portal-btn">Go to Dashboard Workspace</a>
                <a href="profile.php?user=<?= urlencode($_SESSION['username']) ?>" class="portal-btn">View My Space Layout</a>
            <?php else: ?>
                <a href="login.php" class="portal-btn">Member Authentication Login</a>
                <a href="register.php" class="portal-btn" style="background:#4d0026;">Join the Network Today!</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Real-time network statistics panel array window -->
    <div class="stat-grid-row">
        <div class="stat-pill">
            <span class="stat-number"><?= $users_count ?></span>
            Total Registered Spaces
        </div>
        <div class="stat-pill">
            <span class="stat-number"><?= $comments_count ?></span>
            Wall Scrapes Posted
        </div>
    </div>

    <!-- Fresh Space Explorers catalog dashboard row block previews -->
    <h2>🌸 Meet Our Newest Space Members 🌸</h2>
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-top: 15px;">
        <?php foreach ($new_members as $member): ?>
            <div style="border: 1px solid #ff99cc; background: #fff9fb; padding: 10px; text-align: center; box-shadow: 2px 2px 0px #ffb3d9;">
                <img src="<?= htmlspecialchars($member['avatar_url']) ?>" alt="Member" style="width: 80px; height: 80px; object-fit: cover; border: 1px solid #ff1a8c; margin-bottom: 5px;">
                <a href="profile.php?user=<?= urlencode($member['username']) ?>" style="color: #ff1a8c; font-weight: bold; text-decoration: none; font-size: 12px; display: block;">
                    <?= htmlspecialchars($member['display_name']) ?>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</main>

</body>
</html>
