<?php

@include 'config.php';

session_start();

$user_id = $_SESSION['user_id'];

if (!isset($user_id)) {
   header('location:login.php');
   exit;
}

// Get existing profile data
$fetch_stmt = $conn->prepare("SELECT * FROM `users` WHERE id = ?");
$fetch_stmt->bind_param("i", $user_id);
$fetch_stmt->execute();
$fetch_result = $fetch_stmt->get_result();
$fetch_profile = $fetch_result->fetch_assoc();

if (isset($_POST['update_profile'])) {

   $name = $_POST['name'];
   $email = $_POST['email'];

   $update_stmt = $conn->prepare("UPDATE `users` SET name = ?, email = ? WHERE id = ?");
   $update_stmt->bind_param("ssi", $name, $email, $user_id);
   $update_stmt->execute();

   $image = $_FILES['image']['name'];
   $image_size = $_FILES['image']['size'];
   $image_tmp_name = $_FILES['image']['tmp_name'];
   $image_folder = 'images' . $image;
   $old_image = $_POST['old_image'];

   if (!empty($image)) {
      if ($image_size > 2000000) {
         $message[] = 'image size is too large!';
      } else {
         $update_img_stmt = $conn->prepare("UPDATE `users` SET image = ? WHERE id = ?");
         $update_img_stmt->bind_param("si", $image, $user_id);
         $update_img_stmt->execute();

         if ($update_img_stmt) {
            move_uploaded_file($image_tmp_name, $image_folder);
            if (!empty($old_image) && file_exists('images/' . $old_image)) {
               unlink('images/' . $old_image);
            }
            $message[] = 'image updated successfully!';
         }
      }
   }

   $old_pass = $_POST['old_pass'];
   $update_pass = md5($_POST['update_pass']);
   $new_pass = md5($_POST['new_pass']);
   $confirm_pass = md5($_POST['confirm_pass']);

   if (!empty($_POST['update_pass']) && !empty($_POST['new_pass']) && !empty($_POST['confirm_pass'])) {
      if ($update_pass != $old_pass) {
         $message[] = 'old password not matched!';
      } elseif ($new_pass != $confirm_pass) {
         $message[] = 'confirm password not matched!';
      } else {
         $pass_update_stmt = $conn->prepare("UPDATE `users` SET password = ? WHERE id = ?");
         $pass_update_stmt->bind_param("si", $confirm_pass, $user_id);
         $pass_update_stmt->execute();
         $message[] = 'password updated successfully!';
      }
   }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Update Profile</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
 <link rel="stylesheet" href="css/components.css?v=<?= time(); ?>">

</head>
<body>

<?php include 'header.php'; ?>

<section class="update-profile">
   <h1 class="title">Update Profile</h1>

   <form action="" method="POST" enctype="multipart/form-data">
      <img src="images/<?= htmlspecialchars($fetch_profile['image']); ?>" alt="">
      <div class="flex">
         <div class="inputBox">
            <span>username :</span>
            <input type="text" name="name" value="<?= htmlspecialchars($fetch_profile['name']); ?>" required class="box">
            <span>email :</span>
            <input type="email" name="email" value="<?= htmlspecialchars($fetch_profile['email']); ?>" required class="box">
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
         <a href="home.php" class="option-btn">go back</a>
      </div>
   </form>
</section>

<?php include 'footer.php'; ?>
<script src="js/script.js"></script>
</body>
</html>
