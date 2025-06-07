<?php

@include 'config.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
    header('location:login.php');
    exit;
}

if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];

    // Optional: prevent deleting self for safety
    if ($delete_id != $admin_id) {
        $stmt = mysqli_prepare($conn, "DELETE FROM users WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $delete_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    header('location:admin_users.php');
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8" />
   <meta http-equiv="X-UA-Compatible" content="IE=edge" />
   <meta name="viewport" content="width=device-width, initial-scale=1.0" />
   <title>users</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" />

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/admin_style.css?v=<?= time(); ?>">

</head>
<body>

<?php include 'admin_header.php'; ?>

<section class="user-accounts">

   <h1 class="title">user accounts</h1>

   <div class="box-container">

      <?php
         $stmt = mysqli_prepare($conn, "SELECT * FROM users");
         mysqli_stmt_execute($stmt);
         $result = mysqli_stmt_get_result($stmt);
         while ($fetch_users = mysqli_fetch_assoc($result)) {
            if ($fetch_users['id'] == $admin_id) {
               // Skip current admin user (same as your display:none)
               continue;
            }
      ?>
      <div class="box">
         <img src="images/<?= htmlspecialchars($fetch_users['image']); ?>" alt="">
         <p> user id : <span><?= htmlspecialchars($fetch_users['id']); ?></span></p>
         <p> username : <span><?= htmlspecialchars($fetch_users['name']); ?></span></p>
         <p> email : <span><?= htmlspecialchars($fetch_users['email']); ?></span></p>
         <p> user type : <span style="color:<?= ($fetch_users['user_type'] === 'admin') ? 'orange' : 'inherit'; ?>"><?= htmlspecialchars($fetch_users['user_type']); ?></span></p>
         <a href="admin_users.php?delete=<?= htmlspecialchars($fetch_users['id']); ?>" onclick="return confirm('delete this user?');" class="delete-btn">delete</a>
      </div>
      <?php
         }
         mysqli_stmt_close($stmt);
      ?>

   </div>

</section>

<script src="js/script.js"></script>

</body>
</html>
