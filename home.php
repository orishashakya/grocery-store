<?php
@include 'config.php';
session_start();

// Allow page to load for everyone; set $user_id only if logged in
$user_id = $_SESSION['user_id'] ?? null;

// Handle Add to Wishlist
if ($user_id && isset($_POST['add_to_wishlist'])) {
   $pid = $_POST['pid'];
   $p_name = $_POST['p_name'];
   $p_price = $_POST['p_price'];
   $p_image = $_POST['p_image'];

   $check_wishlist = mysqli_prepare($conn, "SELECT * FROM wishlist WHERE name = ? AND user_id = ?");
   mysqli_stmt_bind_param($check_wishlist, "si", $p_name, $user_id);
   mysqli_stmt_execute($check_wishlist);
   $result_wishlist = mysqli_stmt_get_result($check_wishlist);

   $check_cart = mysqli_prepare($conn, "SELECT * FROM cart WHERE name = ? AND user_id = ?");
   mysqli_stmt_bind_param($check_cart, "si", $p_name, $user_id);
   mysqli_stmt_execute($check_cart);
   $result_cart = mysqli_stmt_get_result($check_cart);

   if(mysqli_num_rows($result_wishlist) > 0){
      $message[] = 'already added to wishlist!';
   } elseif(mysqli_num_rows($result_cart) > 0){
      $message[] = 'already added to cart!';
   } else {
      $insert_wishlist = mysqli_prepare($conn, "INSERT INTO wishlist(user_id, pid, name, price, image) VALUES (?, ?, ?, ?, ?)");
      mysqli_stmt_bind_param($insert_wishlist, "iisss", $user_id, $pid, $p_name, $p_price, $p_image);
      mysqli_stmt_execute($insert_wishlist);
      $message[] = 'added to wishlist!';
   }
}

// Handle Add to Cart
if ($user_id && isset($_POST['add_to_cart'])) {
   $pid = $_POST['pid'];
   $p_name = $_POST['p_name'];
   $p_price = $_POST['p_price'];
   $p_image = $_POST['p_image'];
   $p_qty = $_POST['p_qty'];

   $check_cart = mysqli_prepare($conn, "SELECT * FROM cart WHERE name = ? AND user_id = ?");
   mysqli_stmt_bind_param($check_cart, "si", $p_name, $user_id);
   mysqli_stmt_execute($check_cart);
   $result_cart = mysqli_stmt_get_result($check_cart);

   if(mysqli_num_rows($result_cart) > 0){
      $message[] = 'already added to cart!';
   } else {
      $check_wishlist = mysqli_prepare($conn, "SELECT * FROM wishlist WHERE name = ? AND user_id = ?");
      mysqli_stmt_bind_param($check_wishlist, "si", $p_name, $user_id);
      mysqli_stmt_execute($check_wishlist);
      $result_wishlist = mysqli_stmt_get_result($check_wishlist);

      if(mysqli_num_rows($result_wishlist) > 0){
         $delete_wishlist = mysqli_prepare($conn, "DELETE FROM wishlist WHERE name = ? AND user_id = ?");
         mysqli_stmt_bind_param($delete_wishlist, "si", $p_name, $user_id);
         mysqli_stmt_execute($delete_wishlist);
      }

      $insert_cart = mysqli_prepare($conn, "INSERT INTO cart(user_id, pid, name, price, quantity, image) VALUES (?, ?, ?, ?, ?, ?)");
      mysqli_stmt_bind_param($insert_cart, "iissis", $user_id, $pid, $p_name, $p_price, $p_qty, $p_image);
      mysqli_stmt_execute($insert_cart);
      $message[] = 'added to cart!';
   }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8" />
   <meta name="viewport" content="width=device-width, initial-scale=1.0" />
   <title>Grocery Plus</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" />
   <link rel="stylesheet" href="css/style.css?v=<?= time(); ?>">

</head>
<body>

<?php include 'header.php'; ?>

<?php
if (isset($message)) {
   foreach ($message as $msg) {
      echo '<div class="message"><span>' . htmlspecialchars($msg) . '</span><i class="fas fa-times" onclick="this.parentElement.remove();"></i></div>';
   }
}
?>

<div class="home-bg">
   <section class="home">
      <div class="content">
         <span>GET YOUR GROCERIES</span>
         <h3>Reach For A Healthier You With Organic Foods</h3>
         <p>Find your organic greens.</p>
         <a href="about.php" class="btn">about us</a>
      </div>
   </section>
</div>

<section class="home-category">
   <h1 class="title">shop by category</h1>
   <div class="box-container">
      <div class="box"><img src="images/cat-1.png" alt=""><h3>fruits</h3><p>Find fresh fruits</p><a href="category.php?category=fruits" class="btn">fruits</a></div>
      <div class="box"><img src="images/cat-2.png" alt=""><h3>meat</h3><p>Find fresh meat.</p><a href="category.php?category=meat" class="btn">meat</a></div>
      <div class="box"><img src="images/cat-3.png" alt=""><h3>vegetables</h3><p>Find fresh vegetables.</p><a href="category.php?category=vegetables" class="btn">vegetables</a></div>
      <div class="box"><img src="images/cat-4.png" alt=""><h3>fish</h3><p>Find fresh fishes.</p><a href="category.php?category=fish" class="btn">fish</a></div>
   </div>
</section>

<section class="products">
   <h1 class="title">latest products</h1>
   <div class="box-container">
   <?php
      $select_products = mysqli_query($conn, "SELECT * FROM products LIMIT 6");
      if (mysqli_num_rows($select_products) > 0) {
         while ($product = mysqli_fetch_assoc($select_products)) {
   ?>
   <form action="" method="POST" class="box">
      <div class="price">$<span><?= htmlspecialchars($product['price']); ?></span>/-</div>
      <a href="view_page.php?pid=<?= htmlspecialchars($product['id']); ?>" class="fas fa-eye"></a>
      <img src="images/<?= htmlspecialchars($product['image']); ?>" alt="<?= htmlspecialchars($product['name']); ?>">
      <div class="name"><?= htmlspecialchars($product['name']); ?></div>
      <input type="hidden" name="pid" value="<?= htmlspecialchars($product['id']); ?>">
      <input type="hidden" name="p_name" value="<?= htmlspecialchars($product['name']); ?>">
      <input type="hidden" name="p_price" value="<?= htmlspecialchars($product['price']); ?>">
      <input type="hidden" name="p_image" value="<?= htmlspecialchars($product['image']); ?>">
      <input type="number" name="p_qty" value="1" min="1" class="qty">
      <?php if ($user_id): ?>
         <input type="submit" name="add_to_wishlist" value="add to wishlist" class="option-btn">
         <input type="submit" name="add_to_cart" value="add to cart" class="btn">
      <?php else: ?>
         <a href="login.php" class="option-btn">Login to Wishlist</a>
         <a href="login.php" class="btn">Login to Cart</a>
      <?php endif; ?>
   </form>
   <?php
         }
      } else {
         echo '<p class="empty">no products added yet!</p>';
      }
   ?>
   </div>
</section>

<?php include 'footer.php'; ?>
<script src="js/script.js"></script>
</body>
</html>
