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

    // Prepare and execute delete query using mysqli
    $stmt = mysqli_prepare($conn, "DELETE FROM `message` WHERE id = ?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $delete_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    header('location:admin_contacts.php');
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8" />
   <meta http-equiv="X-UA-Compatible" content="IE=edge" />
   <meta name="viewport" content="width=device-width, initial-scale=1.0" />
   <title>Messages</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" />

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/admin_style.css" />
</head>
<body>

<?php include 'admin_header.php'; ?>

<section class="messages">

   <h1 class="title">Messages</h1>

   <div class="box-container">

   <?php
      // Select all messages
      $select_query = "SELECT * FROM `message`";
      $result = mysqli_query($conn, $select_query);

      if ($result && mysqli_num_rows($result) > 0) {
         while ($fetch_message = mysqli_fetch_assoc($result)) {
   ?>
   <div class="box">
      <p> user id : <span><?= htmlspecialchars($fetch_message['user_id']); ?></span> </p>
      <p> name : <span><?= htmlspecialchars($fetch_message['name']); ?></span> </p>
      <p> number : <span><?= htmlspecialchars($fetch_message['number']); ?></span> </p>
      <p> email : <span><?= htmlspecialchars($fetch_message['email']); ?></span> </p>
      <p> message : <span><?= htmlspecialchars($fetch_message['message']); ?></span> </p>
      <a href="admin_contacts.php?delete=<?= (int)$fetch_message['id']; ?>" onclick="return confirm('Delete this message?');" class="delete-btn">delete</a>
   </div>
   <?php
         }
      } else {
         echo '<p class="empty">You have no messages!</p>';
      }
   ?>

   </div>

</section>

<script src="js/script.js"></script>

</body>
</html>
