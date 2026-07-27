<?php
require_once "config.php";
session_start();

if (!isset($_SESSION["user_id"])) {
    header("location: login.php");
    exit;
}

$user_id = $_SESSION["user_id"];
$msg = "";

// Action Handler: Accept Pending Inbound Relationship requests
if (isset($_GET['action']) && $_GET['action'] == 'accept' && isset($_GET['id'])) {
    $request_id = (int)$_GET['id'];
    
    // Find the request to verify ownership safely
    $stmt = $pdo->prepare("SELECT * FROM friends WHERE id = ? AND friend_id = ? AND status = 'pending'");
    $stmt->execute([$request_id, $user_id]);
    $request = $stmt->fetch();
    
    if ($request) {
        $pdo->beginTransaction();
        try {
            // Update initial line direction request state status
            $stmt = $pdo->prepare("UPDATE friends SET status = 'accepted' WHERE id = ?");
            $stmt->execute([$request_id]);
            
            // Insert secondary return relationship array linkage to complete structural bidirectional link mapping
            $stmt2 = $pdo->prepare("INSERT INTO friends (user_id, friend_id, status) VALUES (?, ?, 'accepted')");
            $stmt2->execute([$user_id, $request['user_id']]);
            
            $pdo->commit();
            $msg = "Friend connection accepted successfully!";
        } catch (Exception $e) {
            $pdo->rollBack();
            $msg = "Error handling connection workflow adjustment processing.";
        }
    }
}

// Action Handler: Assign a Friend into a Top 8 position matrix slot
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['assign_top'])) {
    $friend_id = (int)$_POST['friend_id'];
    $slot = (int)$_POST['slot_position'];
    
    if ($slot >= 1 && $slot <= 8) {
        $pdo->beginTransaction();
        try {
            // Clear slot tracking parameter constraints if occupied 
            $stmt = $pdo->prepare("DELETE FROM top_friends WHERE user_id = ? AND slot_position = ?");
            $stmt->execute([$user_id, $slot]);
            
            // Clear past tracking entry row if they were inside another positional index array entry
            $stmt = $pdo->prepare("DELETE FROM top_friends WHERE user_id = ? AND friend_id = ?");
            $stmt->execute([$user_id, $friend_id]);
            
            // Insert assignment into layout matrix tracking records map table
            $stmt = $pdo->prepare("INSERT INTO top_friends (user_id, friend_id, slot_position) VALUES (?, ?, ?)");
            $stmt->execute([$user_id, $friend_id, $slot]);
            
            $pdo->commit();
            $msg = "Top Friend allocated to slot position #{$slot}!";
        } catch (Exception $e) {
            $pdo->rollBack();
            $msg = "Failed matrix allocation pipeline process.";
        }
    }
}

// Fetch all Pending Requests
$pending_stmt = $pdo->prepare("SELECT f.id, u.username FROM friends f JOIN users u ON f.user_id = u.id WHERE f.friend_id = ? AND f.status = 'pending'");
$pending_stmt->execute([$user_id]);
$pending_requests = $pending_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch Completed Network List members entries data 
$friends_stmt = $pdo->prepare("SELECT u.id, u.username FROM friends f JOIN users u ON f.friend_id = u.id WHERE f.user_id = ? AND f.status = 'accepted'");
$friends_stmt->execute([$user_id]);
$my_friends = $friends_stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Friends Space Matrix</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="site-header">
    <div class="logo">🌸 PinkSpace Network Connections 🌸</div>
    <nav>
        <a href="dashboard.php">Dashboard</a> | 
        <a href="profile.php?user=<?= urlencode($_SESSION['username']) ?>">View My Profile</a>
    </nav>
</header>
<main class="container" style="display: block;">
    <h2>Friend Management Panel Engine</h2>
    <?php if(!empty($msg)): ?><p style="color: green; font-weight: bold;"><?= htmlspecialchars($msg) ?></p><?php endif; ?>

    <h3>Pending Inbound Connections Request Queue</h3>
    <?php if(empty($pending_requests)): ?><p>No pending friend requests.</p><?php endif; ?>
    <ul>
        <?php foreach ($pending_requests as $req): ?>
            <li>
                <strong><?= htmlspecialchars($req['username']) ?></strong> wants to be friends!
                <a href="friends.php?action=accept&id=<?= $req['id'] ?>" style="color: #ff1a8c; font-weight:bold;">[Accept Request]</a>
            </li>
        <?php endforeach; ?>
    </ul>

    <hr style="border: 1px dashed #ff99cc; margin: 20px 0;">

    <h3>Assign Friends to Your Classic Top 8 Grid Matrix</h3>
    <form action="friends.php" method="POST">
        <p>
            <label>Select Friend Profile:</label>
            <select name="friend_id" required style="border: 1px solid #ff1a8c;">
                <?php foreach($my_friends as $fr): ?>
                    <option value="<?= $fr['id'] ?>"><?= htmlspecialchars($fr['username']) ?></option>
                <?php endforeach; ?>
            </select>
        </p>
        <p>
            <label>Assign to Space Position Grid Slot:</label>
            <select name="slot_position" required style="border: 1px solid #ff1a8c;">
                <?php for($i=1; $i<=8; $i++): ?>
                    <option value="<?= $i ?>">Grid Slot Placement Position #<?= $i ?></option>
                <?php endfor; ?>
            </select>
        </p>
        <p>
            <input type="submit" name="assign_top" value="Lock to Grid Matrix Space Layout Slot" style="background:#ff1a8c; color:white; border:none; padding:5px; cursor:pointer;">
        </p>
    </form>
</main>
</body>
</html>
