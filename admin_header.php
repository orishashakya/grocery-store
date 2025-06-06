<?php

if (isset($message)) {
    foreach ($message as $msg) {
        echo '
        <div class="message">
           <span>' . htmlspecialchars($msg) . '</span>
           <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
        </div>
        ';
    }
}

?>

<header class="header">

   <div class="flex">

      <a href="admin_page.php" class="logo">Admin<span>Panel</span></a>

      <nav class="navbar">
         <a href="admin_page.php">home</a>
         <a href="admin_products.php">products</a>
         <a href="admin_orders.php">orders</a>
         <a href="admin_users.php">users</a>
         <a href="admin_contacts.php">messages</a>
      </nav>

      <div class="icons">
         <div id="menu-btn" class="fas fa-bars"></div>
         <div id="user-btn" class="fas fa-user"></div>
      </div>

      <div class="profile">
         <?php
            // Prepare statement
            $stmt = mysqli_prepare($conn, "SELECT * FROM `users` WHERE id = ?");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "i", $admin_id);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $fetch_profile = mysqli_fetch_assoc($result);
                mysqli_stmt_close($stmt);
            }
         ?>
         <?php if (!empty($fetch_profile)) : ?>
            <img src="images/<?= htmlspecialchars($fetch_profile['image']); ?>" alt="">
            <p><?= htmlspecialchars($fetch_profile['name']); ?></p>
         <?php else: ?>
            <p>Profile not found</p>
         <?php endif; ?>
         <a href="admin_update_profile.php" class="btn">update profile</a>
         <a href="logout.php" class="delete-btn">logout</a>
         <div class="flex-btn">
            <a href="login.php" class="option-btn">login</a>
            <a href="register.php" class="option-btn">register</a>
         </div>
      </div>

   </div>

</header>
