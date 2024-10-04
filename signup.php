<?php
include("api/connect.php");

$firstname = $lastname = $phn = $address = '';
$role = 'voter';

if (isset($_POST['signup'])) {
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $pass = $_POST['password'];
    $cpass = $_POST['confirm_password'];
    $phn = $_POST['phnno'];
    $address = $_POST['address'];
    $role = $_POST['role'];

    if ($pass === $cpass) {
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $image_name = $_FILES['image']['name'];
            $image_tmp = $_FILES['image']['tmp_name'];
            $image_ext = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));

            $allowed_extensions = array("jpg", "jpeg", "png", "gif");

            if (in_array($image_ext, $allowed_extensions)) {
                $new_image_name = uniqid('img_', true) . '.' . $image_ext;
                $upload_path = "uploads/" . $new_image_name;

                if (move_uploaded_file($image_tmp, $upload_path)) {
                    try {
                        $voter = $conn->prepare("INSERT INTO userdetail (firstname, lastname, phoneNo, password, address, role, votes, status, photo) VALUES (?, ?, ?, ?, ?, ?, 0, 0, ?)");

                        $voter->bindParam(1, $firstname);
                        $voter->bindParam(2, $lastname);
                        $voter->bindParam(3, $phn);
                        $voter->bindParam(4, $pass);
                        $voter->bindParam(5, $address);
                        $voter->bindParam(6, $role);
                        $voter->bindParam(7, $upload_path);

                        if ($voter->execute()) {
                            echo "<script>
                                    alert('Registration successful');
                                    setTimeout(function() {
                                        window.location='index.php';
                                    }, 1000);
                                  </script>";
                        } else {
                            echo "Error: " . $voter->errorInfo()[2];
                        }
                    } catch (PDOException $e) {
                        echo "Error inserting data: " . $e->getMessage();
                    }
                } else {
                    echo "Error moving uploaded file.";
                }
            } else {
                echo "<script>
                        alert('Invalid file format. Allowed formats: JPG, JPEG, PNG, GIF.');
                      </script>";
            }
        } else {
            echo "<script>
                    alert('No image uploaded or an error occurred.');
                  </script>";
        }
    } else {
        echo "<script>
                alert('Passwords do not match');
              </script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign in page</title>
    <link href="bootstrap.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&display=swap"
        rel="stylesheet">
</head>

<body>
    <center>
    <div class="container">
        <div class="row">
    <h1>Register Yourself</h1>
    <hr>
    <form action="" method="post" enctype="multipart/form-data">
        <div class="col-lg-12">
        <input type="text" name="firstname" placeholder="Enter First Name" value="<?php echo $firstname ?>">
            <input class="mx-3" type="text" name="lastname" placeholder="Enter Last Name" value="<?php echo $lastname ?>" required>
            <br><br></div>
            <div class="col-lg-12">
        <input type="text" name="phnno" placeholder="Phone no" value="<?php echo $phn ?>">
        <input class="mx-3" type="text" name="id-no" placeholder="Id No(if Available)" >
        <br><br>
        </div>
        <div class="col-lg-12">
        <input type="password" name="password" placeholder=" Set Password">
        <input class="mx-3" type="password" name="confirm_password" placeholder=" Confirm Your Password">
        <br><br>
        </div>
        <div class="col-lg-10 offset-1 ">
            <textarea name="address" cols="45" placeholder="Address"><?php echo $address ?></textarea>  
            <br><br> 
        </div>
        <div class="col-lg-5 offset-4" id="image-upload">
        <input class="my-2" type="file" name="image" accept=".jpg"><br>
       <p class="px-5">Upload Your Recent Image </p>
        </div>
        <p class="text-black my-3">Sign-up as a</p><select name="role">
            <option value="1">Voter</option>
            <option value="2">Candidate</option>
        </select>
        <br><br>
        <button class="btn btn-dark btn-lg" type="submit" name="signup">Sign up</button>
        <br><br>
        <pre>
        <p>All ready Have a account?<a class="mx-2" href="index.php">Login page</a></pre></p>

    </form>
    </div>
    </div>
</center>
</body>

</html>

