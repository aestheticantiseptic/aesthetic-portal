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
   // Action Handler: Process a newly submitted wall comment scrape post
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_comment'])) {
    if (!isset($_SESSION["user_id"])) {
        die("Error: You must be authenticated to post profile comments.");
    }
    
    $author_id = $_SESSION["user_id"];
    $owner_id = $profile['user_id'];
    $raw_comment = trim($_POST['comment_body'] ?? '');
    
    if (!empty($raw_comment)) {
        $insert_comment_stmt = $pdo->prepare("INSERT INTO profile_comments (profile_owner_id, author_user_id, comment_text) VALUES (?, ?, ?)");
        $insert_comment_stmt->execute([$owner_id, $author_id, $raw_comment]);
        
        // Refresh page to safely prevent form multi-submission bugs
        header("Location: profile.php?user=" . urlencode($profile['username']));
        exit;
    }
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

        <!-- NEW COMPONENT LAYER INJECTION: DYNAMIC SEND FRIEND REQUEST COMPONENT BUTTON -->
        <?php if (isset($_SESSION['user_id']) && $_SESSION['username'] !== $profile['username']): ?>
            <a href="add_friend.php?user=<?= urlencode($profile['username']) ?>" class="add-friend-btn">
                🌸 Add to Friends 🌸
            </a>
        <?php endif; ?>
        
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
                <!-- NEW COMPONENT LAYER INJECTION: RETRO GUESTBOOK COMMENTS SECTION -->
        <div class="comments-container-box">
            <h2>🌸 <?= htmlspecialchars($profile['display_name']) ?>'s Friend Comments 🌸</h2>
            
            <!-- Comment Box Input Form Module View -->
            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="comment-input-form">
                    <form action="profile.php?user=<?= urlencode($profile['username']) ?>" method="POST">
                        <textarea name="comment_body" rows="3" placeholder="Leave a comment scrape on their wall..." required></textarea>
                        <br>
                        <input type="submit" name="submit_comment" value="Post Comment" class="comment-submit-btn">
                    </form>
                </div>
            <?php else: ?>
                <p style="font-size:11px; text-align:center;"><a href="login.php" style="color:#ff1a8c; font-weight:bold;">Log in</a> to leave a comment text post here.</p>
            <?php endif; ?>

            <!-- Interactive Comment History Feed Log View -->
            <table class="comment-log-table">
                <?php
                // Query execution to fetch comments paired with author structural details
                $comments_query = $pdo->prepare("
                    SELECT pc.comment_text, pc.created_at, u.username, p.display_name, p.avatar_url 
                    FROM profile_comments pc
                    JOIN users u ON pc.author_user_id = u.id
                    JOIN profiles p ON u.id = p.user_id
                    WHERE pc.profile_owner_id = ?
                    ORDER BY pc.id DESC
                ");
                $comments_query->execute([$profile['user_id']]);
                $profile_comments_list = $comments_query->fetchAll(PDO::FETCH_ASSOC);

                if (empty($profile_comments_list)):
                ?>
                    <tr>
                        <td colspan="2" style="padding:15px; text-align:center; color:#999; font-size:11px;">No comments posted yet. Be the first!</td>
                    </tr>
                <?php
                else:
                    foreach ($profile_comments_list as $comment_item):
                ?>
                    <tr class="comment-row-item">
                        <td class="comment-author-cell">
                            <a href="profile.php?user=<?= urlencode($comment_item['username']) ?>" style="color:#ff1a8c; text-decoration:none;">
                                <?= htmlspecialchars($comment_item['display_name']) ?>
                            </a>
                            <img src="<?= htmlspecialchars($comment_item['avatar_url']) ?>" alt="User Pic">
                        </td>
                        <td class="comment-body-cell">
                            <span class="comment-timestamp"><?= date("M d, Y @ g:i A", strtotime($comment_item['created_at'])) ?></span>
                            <div><?= clean_retro_code(nl2br(htmlspecialchars($comment_item['comment_text']))) ?></div>
                        </td>
                    </tr>
                <?php
                    endforeach;
                endif;
                ?>
            </table>
        </div>
        <!-- END COMMENTS INJECTION LAYER -->
    </div>
</main>

</body>
</html>
