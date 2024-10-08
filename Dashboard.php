<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include("api/connect.php");
session_start();

// Function to safely get values
function safe_get($array, $key, $default = 'N/A') {
    return isset($array[$key]) ? htmlspecialchars($array[$key]) : $default;
}

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

    try {
        // Register the vote
        $voteStmt = $conn->prepare("INSERT INTO votes (user_id, candidate_id) VALUES (:user_id, :candidate_id)");
        $voteStmt->execute(['user_id' => $user_id, 'candidate_id' => $candidate_id]);

        // Update the voter's status to 1
        $updateStatusStmt = $conn->prepare("UPDATE userdetail SET status = 1 WHERE id = :user_id");
        $updateStatusStmt->execute(['user_id' => $user_id]);

        $hasVoted = true; 
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
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
        /* Global Styles */
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f7f9;
            color: #333;
            line-height: 1.6;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
        }
        /* Header and Navigation */
        .header {
            background-color: #2c3e50;
            color: white;
            padding: 20px 0;
            margin-bottom: 30px;
        }
        .header h1 {
            margin: 0;
            font-size: 2.5em;
        }
        #logout-button {
            background-color: #e74c3c;
            border: none;
            padding: 10px 20px;
            color: white;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }
        #logout-button:hover {
            background-color: #c0392b;
        }
        /* User Profile */
        #profile-detail {
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        #user-image {
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #3498db;
        }
        /* Voting Section */
        #group-detail {
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            transition: box-shadow 0.3s ease;
        }
        #group-detail:hover {
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        #group-detail img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 50%;
            margin-right: 20px;
        }
        .vote-button {
            background-color: #2ecc71;
            border: none;
            padding: 12px 25px;
            color: white;
            font-weight: bold;
            font-size: 1.1em;
            margin-top: 20px;
            transition: background-color 0.3s ease;
        }
        .vote-button:hover {
            background-color: #27ae60;
        }
        /* Alert Messages */
        .alert {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
        }
        /* Responsive Design */
        @media (max-width: 768px) {
            #group-detail {
                flex-direction: column;
                text-align: center;
            }
            #group-detail img {
                margin-right: 0;
                margin-bottom: 15px;
            }
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
                            <img src="<?php echo htmlspecialchars($groupData['photo']); ?>" alt="Candidate Photo">
                            <div class="text-start">
                                <b>Name:</b> <?php echo safe_get($groupData, 'firstname') . " " . safe_get($groupData, 'lastname'); ?><br>
                                <b>Votes:</b> <?php echo safe_get($groupData, 'vote_count'); ?><br>
                                <input type="radio" name="candidate_id" value="<?php echo $groupData['id']; ?>" required>
                                <label>Vote for this candidate</label>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <input class="btn btn-primary vote-button" type="submit" value="Vote">
                <?php else: ?>
                    <div class="alert">No candidates available for voting.</div>
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
