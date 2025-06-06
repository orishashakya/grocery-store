<?php

@include 'config.php';

session_start();

$user_id = $_SESSION['user_id'];

if (!isset($user_id)) {
   header('location:login.php');
   exit;
}

// Add to wishlist
if (isset($_POST['add_to_wishlist'])) {

   $pid = $_POST['pid'];
   $p_name = $_POST['p_name'];
   $p_price = $_POST['p_price'];
   $p_image = $_POST['p_image'];

   $check_wishlist = $conn->prepare("SELECT * FROM `wishlist` WHERE name = ? AND user_id = ?");
   $check_wishlist->bind_param("si", $p_name, $user_id);
   $check_wishlist->execute();
   $check_wishlist_result = $check_wishlist->get_result();

   $check_cart = $conn->prepare("SELECT * FROM `cart` WHERE name = ? AND user_id = ?");
   $check_cart->bind_param("si", $p_name, $user_id);
   $check_cart->execute();
   $check_cart_result = $check_cart->get_result();

   if ($check_wishlist_result->num_rows > 0) {
      $message[] = 'already added to wishlist!';
   } elseif ($check_cart_result->num_rows > 0) {
      $message[] = 'already added to cart!';
   } else {
      $insert_wishlist = $conn->prepare("INSERT INTO `wishlist`(user_id, pid, name, price, image) VALUES (?, ?, ?, ?, ?)");
      $insert_wishlist->bind_param("iisss", $user_id, $pid, $p_name, $p_price, $p_image);
      $insert_wishlist->execute();
      $message[] = 'added to wishlist!';
   }
}

// Add to cart
if (isset($_POST['add_to_cart'])) {

   $pid = $_POST['pid'];
   $p_name = $_POST['p_name'];
   $p_price = $_POST['p_price'];
   $p_image = $_POST['p_image'];
   $p_qty = $_POST['p_qty'];

   $check_cart = $conn->prepare("SELECT * FROM `cart` WHERE name = ? AND user_id = ?");
   $check_cart->bind_param("si", $p_name, $user_id);
   $check_cart->execute();
   $check_cart_result = $check_cart->get_result();

   if ($check_cart_result->num_rows > 0) {
      $message[] = 'already added to cart!';
   } else {

      $check_wishlist = $conn->prepare("SELECT * FROM `wishlist` WHERE name = ? AND user_id = ?");
      $check_wishlist->bind_param("si", $p_name, $user_id);
      $check_wishlist->execute();
      $check_wishlist_result = $check_wishlist->get_result();

      if ($check_wishlist_result->num_rows > 0) {
         $delete_wishlist = $conn->prepare("DELETE FROM `wishlist` WHERE name = ? AND user_id = ?");
         $delete_wishlist->bind_param("si", $p_name, $user_id);
         $delete_wishlist->execute();
      }

      $insert_cart = $conn->prepare("INSERT INTO `cart`(user_id, pid, name, price, quantity, image) VALUES (?, ?, ?, ?, ?, ?)");
      $insert_cart->bind_param("iisdss", $user_id, $pid, $p_name, $p_price, $p_qty, $p_image);
      $insert_cart->execute();
      $message[] = 'added to cart!';
   }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Quick View</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'header.php'; ?>

<section class="quick-view">
   <h1 class="title">quick view</h1>

   <?php
   if (isset($_GET['pid'])) {
      $pid = $_GET['pid'];
      $select_product = $conn->prepare("SELECT * FROM `products` WHERE id = ?");
      $select_product->bind_param("i", $pid);
      $select_product->execute();
      $result = $select_product->get_result();

      if ($result->num_rows > 0) {
         while ($fetch_products = $result->fetch_assoc()) {
   ?>
   <form action="" class="box" method="POST">
      <div class="price">$<span><?= htmlspecialchars($fetch_products['price']); ?></span>/-</div>
      <img src="images/<?= htmlspecialchars($fetch_products['image']); ?>" alt="">
      <div class="name"><?= htmlspecialchars($fetch_products['name']); ?></div>
      <div class="details"><?= htmlspecialchars($fetch_products['details']); ?></div>
      <input type="hidden" name="pid" value="<?= $fetch_products['id']; ?>">
      <input type="hidden" name="p_name" value="<?= htmlspecialchars($fetch_products['name']); ?>">
      <input type="hidden" name="p_price" value="<?= $fetch_products['price']; ?>">
      <input type="hidden" name="p_image" value="<?= htmlspecialchars($fetch_products['image']); ?>">
      <input type="number" min="1" value="1" name="p_qty" class="qty">
      <input type="submit" value="add to wishlist" class="option-btn" name="add_to_wishlist">
      <input type="submit" value="add to cart" class="btn" name="add_to_cart">
   </form>
   <?php
         }
      } else {
         echo '<p class="empty">no products added yet!</p>';
      }
   } else {
      echo '<p class="empty">no product selected!</p>';
   }
   ?>
</section>

<?php include 'footer.php'; ?>
<script src="js/script.js"></script>

</body>
</html>
