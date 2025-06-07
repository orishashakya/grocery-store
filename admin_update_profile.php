<?php

@include 'config.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
    header('location:login.php');
    exit;
}

// Fetch current profile data to fill the form
$fetch_profile = null;
$stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $admin_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
if ($result && mysqli_num_rows($result) > 0) {
    $fetch_profile = mysqli_fetch_assoc($result);
}
mysqli_stmt_close($stmt);

if (isset($_POST['update_profile'])) {

    $name = $_POST['name'];  // removed filter_var
    $email = $_POST['email'];

    // Update name and email
    $stmt = mysqli_prepare($conn, "UPDATE users SET name = ?, email = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "ssi", $name, $email, $admin_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    // Handle image update
    $image = $_FILES['image']['name'];
    $image_size = $_FILES['image']['size'];
    $image_tmp_name = $_FILES['image']['tmp_name'];
    $image_folder = 'uploaded_img/' . $image;
    $old_image = $_POST['old_image'];

    if (!empty($image)) {
        if ($image_size > 2000000) {
            $message[] = 'image size is too large!';
        } else {
            if (!is_dir('uploaded_img')) {
                mkdir('uploaded_img', 0777, true);
            }
            $stmt = mysqli_prepare($conn, "UPDATE users SET image = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "si", $image, $admin_id);
            mysqli_stmt_execute($stmt);
            if (mysqli_stmt_affected_rows($stmt) > 0) {
                move_uploaded_file($image_tmp_name, $image_folder);
                if ($old_image && file_exists('uploaded_img/' . $old_image)) {
                    unlink('uploaded_img/' . $old_image);
                }
                $message[] = 'image updated successfully!';
            }
            mysqli_stmt_close($stmt);
        }
    }

    // Handle password update
    $old_pass = $_POST['old_pass'];
    $update_pass = md5($_POST['update_pass']);
    $new_pass = md5($_POST['new_pass']);
    $confirm_pass = md5($_POST['confirm_pass']);

    if (!empty($_POST['update_pass']) && !empty($_POST['new_pass']) && !empty($_POST['confirm_pass'])) {
        if ($update_pass !== $old_pass) {
            $message[] = 'old password not matched!';
        } elseif ($new_pass !== $confirm_pass) {
            $message[] = 'confirm password not matched!';
        } else {
            $stmt = mysqli_prepare($conn, "UPDATE users SET password = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "si", $confirm_pass, $admin_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            $message[] = 'password updated successfully!';
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8" />
   <meta http-equiv="X-UA-Compatible" content="IE=edge" />
   <meta name="viewport" content="width=device-width, initial-scale=1.0" />
   <title>update admin profile</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" />

   <!-- custom css file link  -->
    <link rel="stylesheet" href="css/components.css?v=<?= time(); ?>">

</head>
<body>

<?php include 'admin_header.php'; ?>

<section class="update-profile">

   <h1 class="title">update profile</h1>

   <?php if (!empty($message)) {
       foreach ($message as $msg) {
           echo '<p class="message">' . htmlspecialchars($msg) . '</p>';
       }
   } ?>

   <form action="" method="POST" enctype="multipart/form-data">
      <img src="images/<?= htmlspecialchars($fetch_profile['image']); ?>" alt="">
      <div class="flex">
         <div class="inputBox">
            <span>username :</span>
            <input type="text" name="name" value="<?= htmlspecialchars($fetch_profile['name']); ?>" placeholder="update username" required class="box">
            <span>email :</span>
            <input type="email" name="email" value="<?= htmlspecialchars($fetch_profile['email']); ?>" placeholder="update email" required class="box">
            <span>update pic :</span>
            <input type="file" name="image" accept="image/jpg, image/jpeg, image/png" class="box">
            <input type="hidden" name="old_image" value="<?= htmlspecialchars($fetch_profile['image']); ?>">
         </div>
         <div class="inputBox">
            <input type="hidden" name="old_pass" value="<?= htmlspecialchars($fetch_profile['password']); ?>">
            <span>old password :</span>
            <input type="password" name="update_pass" placeholder="enter previous password" class="box">
            <span>new password :</span>
            <input type="password" name="new_pass" placeholder="enter new password" class="box">
            <span>confirm password :</span>
            <input type="password" name="confirm_pass" placeholder="confirm new password" class="box">
         </div>
      </div>
      <div class="flex-btn">
         <input type="submit" class="btn" value="update profile" name="update_profile">
         <a href="admin_page.php" class="option-btn">go back</a>
      </div>
   </form>

</section>

<script src="js/script.js"></script>

</body>
</html>
