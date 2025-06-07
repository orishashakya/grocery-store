<?php

@include 'config.php';

session_start();

$user_id = $_SESSION['user_id'];

if(!isset($user_id)){
   header('location:login.php');
   exit;
}

$message = [];

if(isset($_POST['add_to_wishlist'])){

   $pid = (int)$_POST['pid'];
   $p_name = $_POST['p_name'];
   $p_price = (float)$_POST['p_price'];
   $p_image = $_POST['p_image'];

   // Check if already in wishlist
   $stmt = $conn->prepare("SELECT * FROM wishlist WHERE name = ? AND user_id = ?");
   $stmt->bind_param("si", $p_name, $user_id);
   $stmt->execute();
   $result = $stmt->get_result();

   // Check if already in cart
   $stmt_cart = $conn->prepare("SELECT * FROM cart WHERE name = ? AND user_id = ?");
   $stmt_cart->bind_param("si", $p_name, $user_id);
   $stmt_cart->execute();
   $result_cart = $stmt_cart->get_result();

   if($result->num_rows > 0){
      $message[] = 'already added to wishlist!';
   } elseif($result_cart->num_rows > 0){
      $message[] = 'already added to cart!';
   } else {
      $stmt_insert = $conn->prepare("INSERT INTO wishlist (user_id, pid, name, price, image) VALUES (?, ?, ?, ?, ?)");
      $stmt_insert->bind_param("iisss", $user_id, $pid, $p_name, $p_price, $p_image);
      $stmt_insert->execute();
      $message[] = 'added to wishlist!';
      $stmt_insert->close();
   }
   $stmt->close();
   $stmt_cart->close();
}

if(isset($_POST['add_to_cart'])){

   $pid = (int)$_POST['pid'];
   $p_name = $_POST['p_name'];
   $p_price = (float)$_POST['p_price'];
   $p_image = $_POST['p_image'];
   $p_qty = filter_var($_POST['p_qty'], FILTER_VALIDATE_INT);
   if($p_qty === false || $p_qty < 1){
       $p_qty = 1; // default to 1 if invalid
   }

   // Check if already in cart
   $stmt_cart = $conn->prepare("SELECT * FROM cart WHERE name = ? AND user_id = ?");
   $stmt_cart->bind_param("si", $p_name, $user_id);
   $stmt_cart->execute();
   $result_cart = $stmt_cart->get_result();

   if($result_cart->num_rows > 0){
      $message[] = 'already added to cart!';
   } else {
      // Check if in wishlist to remove
      $stmt_wishlist = $conn->prepare("SELECT * FROM wishlist WHERE name = ? AND user_id = ?");
      $stmt_wishlist->bind_param("si", $p_name, $user_id);
      $stmt_wishlist->execute();
      $result_wishlist = $stmt_wishlist->get_result();

      if($result_wishlist->num_rows > 0){
         $stmt_delete = $conn->prepare("DELETE FROM wishlist WHERE name = ? AND user_id = ?");
         $stmt_delete->bind_param("si", $p_name, $user_id);
         $stmt_delete->execute();
         $stmt_delete->close();
      }

      $stmt_insert_cart = $conn->prepare("INSERT INTO cart (user_id, pid, name, price, quantity, image) VALUES (?, ?, ?, ?, ?, ?)");
      $stmt_insert_cart->bind_param("iisdis", $user_id, $pid, $p_name, $p_price, $p_qty, $p_image);
      $stmt_insert_cart->execute();
      $message[] = 'added to cart!';

      $stmt_insert_cart->close();
      $stmt_wishlist->close();
   }
   $stmt_cart->close();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8" />
   <meta http-equiv="X-UA-Compatible" content="IE=edge" />
   <meta name="viewport" content="width=device-width, initial-scale=1.0" />
   <title>Category</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" />

   <!-- custom css file link  -->
  <link rel="stylesheet" href="css/style.css?v=<?= time(); ?>">

</head>
<body>

<?php include 'header.php'; ?>

<?php
if (!empty($message)) {
    foreach ($message as $msg) {
        echo '<div class="message"><span>' . htmlspecialchars($msg) . '</span>
        <i class="fas fa-times" onclick="this.parentElement.remove();"></i></div>';
    }
}
?>

<section class="products">

   <h1 class="title">Products Categories</h1>

   <div class="box-container">

   <?php
      $category_name = isset($_GET['category']) ? $_GET['category'] : '';
      $category_name = htmlspecialchars($category_name);

      $stmt = $conn->prepare("SELECT * FROM products WHERE category = ?");
      $stmt->bind_param("s", $category_name);
      $stmt->execute();
      $result = $stmt->get_result();

      if($result->num_rows > 0){
         while($fetch_products = $result->fetch_assoc()){
   ?>
   <form action="" class="box" method="POST">
      <div class="price">$<span><?= htmlspecialchars($fetch_products['price']); ?></span>/-</div>
      <a href="view_page.php?pid=<?= htmlspecialchars($fetch_products['id']); ?>" class="fas fa-eye"></a>
      <img src="images/<?= htmlspecialchars($fetch_products['image']); ?>" alt="">
      <div class="name"><?= htmlspecialchars($fetch_products['name']); ?></div>
      <input type="hidden" name="pid" value="<?= (int)$fetch_products['id']; ?>">
      <input type="hidden" name="p_name" value="<?= htmlspecialchars($fetch_products['name']); ?>">
      <input type="hidden" name="p_price" value="<?= (float)$fetch_products['price']; ?>">
      <input type="hidden" name="p_image" value="<?= htmlspecialchars($fetch_products['image']); ?>">
      <input type="number" min="1" value="1" name="p_qty" class="qty">
      <input type="submit" value="add to wishlist" class="option-btn" name="add_to_wishlist">
      <input type="submit" value="add to cart" class="btn" name="add_to_cart">
   </form>
   <?php
         }
      } else {
         echo '<p class="empty">no products available!</p>';
      }
      $stmt->close();
   ?>

   </div>

</section>

<?php include 'footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>
