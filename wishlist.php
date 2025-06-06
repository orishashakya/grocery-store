<?php

@include 'config.php';

session_start();

$user_id = $_SESSION['user_id'];

if (!isset($user_id)) {
   header('location:login.php');
   exit;
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
   $result_cart = $check_cart->get_result();

   if ($result_cart->num_rows > 0) {
      $message[] = 'already added to cart!';
   } else {
      $check_wishlist = $conn->prepare("SELECT * FROM `wishlist` WHERE name = ? AND user_id = ?");
      $check_wishlist->bind_param("si", $p_name, $user_id);
      $check_wishlist->execute();
      $result_wishlist = $check_wishlist->get_result();

      if ($result_wishlist->num_rows > 0) {
         $delete_wishlist = $conn->prepare("DELETE FROM `wishlist` WHERE name = ? AND user_id = ?");
         $delete_wishlist->bind_param("si", $p_name, $user_id);
         $delete_wishlist->execute();
      }

      $insert_cart = $conn->prepare("INSERT INTO `cart` (user_id, pid, name, price, quantity, image) VALUES (?, ?, ?, ?, ?, ?)");
      $insert_cart->bind_param("iisdss", $user_id, $pid, $p_name, $p_price, $p_qty, $p_image);
      $insert_cart->execute();
      $message[] = 'added to cart!';
   }
}

// Delete single item
if (isset($_GET['delete'])) {
   $delete_id = $_GET['delete'];
   $delete_item = $conn->prepare("DELETE FROM `wishlist` WHERE id = ?");
   $delete_item->bind_param("i", $delete_id);
   $delete_item->execute();
   header('location:wishlist.php');
   exit;
}

// Delete all wishlist items
if (isset($_GET['delete_all'])) {
   $delete_all = $conn->prepare("DELETE FROM `wishlist` WHERE user_id = ?");
   $delete_all->bind_param("i", $user_id);
   $delete_all->execute();
   header('location:wishlist.php');
   exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>wishlist</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'header.php'; ?>

<section class="wishlist">

   <h1 class="title">products added</h1>

   <div class="box-container">
   <?php
      $grand_total = 0;
      $select_wishlist = $conn->prepare("SELECT * FROM `wishlist` WHERE user_id = ?");
      $select_wishlist->bind_param("i", $user_id);
      $select_wishlist->execute();
      $result = $select_wishlist->get_result();

      if ($result->num_rows > 0) {
         while ($row = $result->fetch_assoc()) {
            $grand_total += $row['price'];
   ?>
   <form action="" method="POST" class="box">
      <a href="wishlist.php?delete=<?= $row['id']; ?>" class="fas fa-times" onclick="return confirm('delete this from wishlist?');"></a>
      <a href="view_page.php?pid=<?= $row['pid']; ?>" class="fas fa-eye"></a>
      <img src="images/<?= htmlspecialchars($row['image']); ?>" alt="">
      <div class="name"><?= htmlspecialchars($row['name']); ?></div>
      <div class="price">$<?= htmlspecialchars($row['price']); ?>/-</div>
      <input type="number" min="1" value="1" class="qty" name="p_qty">
      <input type="hidden" name="pid" value="<?= $row['pid']; ?>">
      <input type="hidden" name="p_name" value="<?= htmlspecialchars($row['name']); ?>">
      <input type="hidden" name="p_price" value="<?= $row['price']; ?>">
      <input type="hidden" name="p_image" value="<?= htmlspecialchars($row['image']); ?>">
      <input type="submit" value="add to cart" name="add_to_cart" class="btn">
   </form>
   <?php
         }
      } else {
         echo '<p class="empty">your wishlist is empty</p>';
      }
   ?>
   </div>

   <div class="wishlist-total">
      <p>grand total : <span>$<?= $grand_total; ?>/-</span></p>
      <a href="shop.php" class="option-btn">continue shopping</a>
      <a href="wishlist.php?delete_all" class="delete-btn <?= ($grand_total > 0) ? '' : 'disabled'; ?>">delete all</a>
   </div>

</section>

<?php include 'footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>
