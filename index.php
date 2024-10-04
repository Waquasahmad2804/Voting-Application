<?php
session_start();
include("api/connect.php");

if (isset($_POST['login'])) {
    $phnno = $_POST['phnno'];
    $password = $_POST['password'];

   
    $verify = $conn->prepare("SELECT * FROM userdetail WHERE phoneNo = :phnno AND password = :pass");
    $verify->bindParam(':phnno', $phnno);
    $verify->bindParam(':pass', $password);
    $verify->execute();

    $result = $verify->fetchAll(PDO::FETCH_ASSOC);

    
    
    if (count($result) > 0) {
        if (count($result) > 0) {
          
            $user = $result[0];
        
           
            $_SESSION['voter'] = $user; 
            $_SESSION['groups'] = $conn->query("SELECT * FROM userdetail WHERE role='2'")->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<script>
            window.location='Dashboard.php';
            </script>";
        }
        
    } else {
        echo "<script>
        alert('Invalid phone number or password. Please try again.');
        window.location='index.php';
        </script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Voting Portal</title>
    <link href="bootstrap.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&display=swap"
        rel="stylesheet">
</head>

<body>
    <div class="container">
        <div class="row">
    <h1>Online Voting System</h1>
    <hr>
    <form action="" method="post">
        <input type="text" name="phnno" placeholder="Phone no">
        <br><br>
        <input type="password" name="password" placeholder="Password">
        <br><br>
        <p>Login as a</p><select><option>Voter</option><option>Candidate</option></select><br><br>
        <button class="btn btn-dark btn-lg" type="submit" name="login">Login</button>
        <br><br><pre>
        <p>New User?<a class="mx-2" href="signup.php">Register Here</a></p></pre>

    </form>
    </div>
    </div>

</body>

</html>