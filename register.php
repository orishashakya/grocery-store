<?php
include 'config.php';

$message = [];

if (isset($_POST['submit'])) {

    // Directly get inputs without filter_var sanitization
    $name = $_POST['name'];
    $email = $_POST['email'];

    $pass = $_POST['pass'];
    $cpass = $_POST['cpass'];

    $image = $_FILES['image']['name'];
    $image_size = $_FILES['image']['size'];
    $image_tmp_name = $_FILES['image']['tmp_name'];
    $image_folder = 'images/' . $image;

    // Check if email already exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $message[] = 'User email already exists!';
    } else {
        if ($pass !== $cpass) {
            $message[] = 'Confirm password does not match!';
        } else {
            if ($image_size > 2000000) {
                $message[] = 'Image size is too large!';
            } else {
                // Hash password securely
                $hashed_pass = password_hash($pass, PASSWORD_DEFAULT);

                // Insert user data
                $stmt = $conn->prepare("INSERT INTO users (name, email, password, image) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $name, $email, $hashed_pass, $image);

                if ($stmt->execute()) {
                    move_uploaded_file($image_tmp_name, $image_folder);
                    $message[] = 'Registered successfully!';
                    header('Location: login.php');
                    exit;
                } else {
                    $message[] = 'Registration failed!';
                }
            }
        }
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8" />
   <meta http-equiv="X-UA-Compatible" content="IE=edge" />
   <meta name="viewport" content="width=device-width, initial-scale=1.0" />
   <title>Register</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" />
   <link rel="stylesheet" href="css/components.css" />
</head>
<body>

<?php
if (!empty($message)) {
    foreach ($message as $msg) {
        echo '<div class="message">
                 <span>' . htmlspecialchars($msg) . '</span>
                 <i class="fas fa-times" onclick="this.parentElement.style.display=\'none\';"></i>
              </div>';
    }
}
?>

<section class="form-container">
   <form action="" method="POST" enctype="multipart/form-data">
      <h3>Register Now</h3>
      <input type="text" name="name" class="box" placeholder="Enter your name" required />
      <input type="email" name="email" class="box" placeholder="Enter your email" required />
      <input type="password" name="pass" class="box" placeholder="Enter your password" required />
      <input type="password" name="cpass" class="box" placeholder="Confirm your password" required />
      <input type="file" name="image" accept="image/*" class="box" required />
      <input type="submit" value="Register" class="btn" name="submit" />
      <p>Already have an account? <a href="login.php">Login now</a></p>
   </form>
</section>

</body>
</html>
