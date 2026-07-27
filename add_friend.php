<?php
require_once "config.php";
session_start();

// Step 1: Force active login state check
if (!isset($_SESSION["user_id"])) {
    die("Error: You must be logged in to add friends. <a href='login.php'>Login here</a>");
}

$current_user_id = $_SESSION["user_id"];
$target_username = $_GET['user'] ?? '';

// Step 2: Fetch target user ID from user string parameter
$stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
$stmt->execute([$target_username]);
$target_user = $stmt->fetch();

if (!$target_user) {
    die("Error: Target user space not found.");
}

$target_user_id = $target_user['id'];

// Step 3: Prevent users from adding themselves
if ($current_user_id == $target_user_id) {
    die("Error: You cannot add yourself to your own Top 8 space matrix.");
}

// Step 4: Check if relationship connection array row parameters exist already
$stmt = $pdo->prepare("SELECT status FROM friends WHERE user_id = ? AND friend_id = ?");
$stmt->execute([$current_user_id, $target_user_id]);
$existing_relationship = $stmt->fetch();

if ($existing_relationship) {
    if ($existing_relationship['status'] == 'pending') {
        die("Notification: Friend request is already pending verification approval.");
    } else {
        die("Notification: You are already connected friends with this profile space!");
    }
}

// Step 5: Execute database record entry injection safe statement pipeline 
$stmt = $pdo->prepare("INSERT INTO friends (user_id, friend_id, status) VALUES (?, ?, 'pending')");
if ($stmt->execute([$current_user_id, $target_user_id])) {
    echo "<div style='font-family: Arial; text-align: center; margin-top: 50px; color: #ff1a8c;'>";
    echo "<h2>🌸 Request Sent! 🌸</h2>";
    echo "<p>Your connection invitation has been sent to " . htmlspecialchars($target_username) . ".</p>";
    echo "<a href='profile.php?user=" . urlencode($target_username) . "' style='color:#ff1a8c; font-weight:bold;'>Back to Profile</a>";
    echo "</div>";
} else {
    echo "Critical Error processing dynamic friendship mapping logic.";
}
?>
