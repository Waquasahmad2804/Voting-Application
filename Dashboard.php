<?php
include("api/connect.php");
session_start();

if (!isset($_SESSION['voter'])) {
    header("Location: ../");
    exit();
}

$userData = $_SESSION['voter'];

// Fetch candidates and their vote counts from the database
$stmt = $conn->prepare("
    SELECT ud.id, ud.firstname, ud.lastname, ud.photo, COUNT(v.candidate_id) as vote_count 
    FROM userdetail ud 
    LEFT JOIN votes v ON ud.id = v.candidate_id 
    WHERE ud.role = 2 
    GROUP BY ud.id
");
$stmt->execute();
$candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle voting
$hasVoted = $userData['status'] == 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['candidate_id']) && !$hasVoted) {
    $candidate_id = $_POST['candidate_id'];
    $user_id = $userData['id'];

    // Register the vote
    $voteStmt = $conn->prepare("INSERT INTO votes (user_id, candidate_id) VALUES (:user_id, :candidate_id)");
    $voteStmt->execute(['user_id' => $user_id, 'candidate_id' => $candidate_id]);

    // Update the voter's status to 1
    $updateStatusStmt = $conn->prepare("UPDATE userdetail SET status = 1 WHERE id = :user_id");
    $updateStatusStmt->execute(['user_id' => $user_id]);

    $hasVoted = true; 
}

if (isset($_GET['logout'])) {
    session_destroy(); 
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link href="bootstrap.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Arial', sans-serif;
        }
        #profile-detail {
            border: 2px solid black;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .alert {
            margin-top: 20px;
            color: green;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="row align-items-center">
        <form action="" method="get"> 
            <button class="my-3 btn btn-primary" name="logout" id="logout-button">Logout</button>
        </form>

        <!-- User Profile -->
        <div class="text-start my-5" id="profile-detail">
            <div style="text-align:start; margin-top:10px"> 
                <?php
                $imagePath = htmlspecialchars($userData['photo']);
                if (file_exists($imagePath)) {
                    echo "<img id='user-image' src='$imagePath' class='profile-image mb-3' style='height: 100px; width: 100px;'><br><br>";
                } else {
                    echo "Image not found.";
                }
                ?>
            </div>
            <b>Name:</b> <?php echo $userData['firstname'] . " " . $userData['lastname']; ?><br>
            <b>Mobile:</b> <?php echo $userData['phoneNo']; ?> <br>
            <b>Address:</b> <?php echo $userData['address']; ?> <br><br>
        </div>

        <?php if (!$hasVoted): // Only show voting section if the user hasn't voted ?>
            <!-- Voting Form -->
            <form method="POST">
                <h3>Select a Candidate to Vote</h3>
                <?php if ($candidates): 
                    foreach ($candidates as $groupData): ?>
                        <div id="group-detail">
                            <img src="<?php echo htmlspecialchars($groupData['photo']); ?>" alt="Group Photo" style="width: 100px;">
                            <div class="text-start">
                                <b>Name:</b> <?php echo safe_get($groupData, 'firstname') . " " . safe_get($groupData, 'lastname'); ?><br>
                                <b>Votes:</b> <?php echo safe_get($groupData, 'vote_count'); ?><br>
                                <input type="radio" name="candidate_id" value="<?php echo $groupData['id']; ?>" required>
                                <label>Vote for this candidate</label>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <input class="btn btn-primary vote-button" type="submit" value="Vote" <?php echo $hasVoted ? 'disabled' : ''; ?>>
                <?php endif; ?>
            </form>

            <?php if ($hasVoted): ?>
                <div class="alert">Thank you for voting! You can't vote again.</div>
            <?php endif; ?>

        <?php else: // Show confirmation message if the user has voted ?>
            <div class="alert">Thank you for voting! Your vote has been recorded.</div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
