<?php
@include 'config.php';

session_start();

$user_id = $_SESSION['user_id'];

if(!isset($user_id)){
   header('location:login.php');
   exit;
}

if(isset($_POST['add_to_wishlist'])){

   $pid = $_POST['pid'];
   $p_name = $_POST['p_name'];
   $p_price = $_POST['p_price'];
   $p_image = $_POST['p_image'];

   // Check wishlist
   $check_wishlist_numbers = mysqli_prepare($conn, "SELECT * FROM wishlist WHERE name = ? AND user_id = ?");
   mysqli_stmt_bind_param($check_wishlist_numbers, "si", $p_name, $user_id);
   mysqli_stmt_execute($check_wishlist_numbers);
   $result_wishlist = mysqli_stmt_get_result($check_wishlist_numbers);

   // Check cart
   $check_cart_numbers = mysqli_prepare($conn, "SELECT * FROM cart WHERE name = ? AND user_id = ?");
   mysqli_stmt_bind_param($check_cart_numbers, "si", $p_name, $user_id);
   mysqli_stmt_execute($check_cart_numbers);
   $result_cart = mysqli_stmt_get_result($check_cart_numbers);

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

if(isset($_POST['add_to_cart'])){

   $pid = $_POST['pid'];
   $p_name = $_POST['p_name'];
   $p_price = $_POST['p_price'];
   $p_image = $_POST['p_image'];
   $p_qty = $_POST['p_qty'];

   // Check cart
   $check_cart_numbers = mysqli_prepare($conn, "SELECT * FROM cart WHERE name = ? AND user_id = ?");
   mysqli_stmt_bind_param($check_cart_numbers, "si", $p_name, $user_id);
   mysqli_stmt_execute($check_cart_numbers);
   $result_cart = mysqli_stmt_get_result($check_cart_numbers);

   if(mysqli_num_rows($result_cart) > 0){
      $message[] = 'already added to cart!';
   } else {
      // Check wishlist
      $check_wishlist_numbers = mysqli_prepare($conn, "SELECT * FROM wishlist WHERE name = ? AND user_id = ?");
      mysqli_stmt_bind_param($check_wishlist_numbers, "si", $p_name, $user_id);
      mysqli_stmt_execute($check_wishlist_numbers);
      $result_wishlist = mysqli_stmt_get_result($check_wishlist_numbers);

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
   <meta http-equiv="X-UA-Compatible" content="IE=edge" />
   <meta name="viewport" content="width=device-width, initial-scale=1.0" />
   <title>Grocery Plus</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" />

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css" />
</head>
<body>
   
<?php include 'header.php'; ?>

<?php
if(isset($message)){
   foreach($message as $msg){
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

      <div class="box">
         <img src="images/cat-1.png" alt="" />
         <h3>fruits</h3>
         <p>Lorem ipsum, dolor sit amet consectetur adipisicing elit. Exercitationem, quaerat.</p>
         <a href="category.php?category=fruits" class="btn">fruits</a>
      </div>

      <div class="box">
         <img src="images/cat-2.png" alt="" />
         <h3>meat</h3>
         <p>Lorem ipsum, dolor sit amet consectetur adipisicing elit. Exercitationem, quaerat.</p>
         <a href="category.php?category=meat" class="btn">meat</a>
      </div>

      <div class="box">
         <img src="images/cat-3.png" alt="" />
         <h3>vegitables</h3>
         <p>Lorem ipsum, dolor sit amet consectetur adipisicing elit. Exercitationem, quaerat.</p>
         <a href="category.php?category=vegitables" class="btn">vegitables</a>
      </div>

      <div class="box">
         <img src="images/cat-4.png" alt="" />
         <h3>fish</h3>
         <p>Lorem ipsum, dolor sit amet consectetur adipisicing elit. Exercitationem, quaerat.</p>
         <a href="category.php?category=fish" class="btn">fish</a>
      </div>

   </div>
</section>

<section class="products">

   <h1 class="title">latest products</h1>

   <div class="box-container">

   <?php
      $select_products = mysqli_query($conn, "SELECT * FROM products LIMIT 6");
      if(mysqli_num_rows($select_products) > 0){
         while($fetch_products = mysqli_fetch_assoc($select_products)){ 
   ?>
   <form action="" class="box" method="POST">
      <div class="price">$<span><?= htmlspecialchars($fetch_products['price']); ?></span>/-</div>
      <a href="view_page.php?pid=<?= htmlspecialchars($fetch_products['id']); ?>" class="fas fa-eye"></a>
      <img src="images/<?= htmlspecialchars($fetch_products['image']); ?>" alt="<?= htmlspecialchars($fetch_products['name']); ?>" />
      <div class="name"><?= htmlspecialchars($fetch_products['name']); ?></div>
      <input type="hidden" name="pid" value="<?= htmlspecialchars($fetch_products['id']); ?>" />
      <input type="hidden" name="p_name" value="<?= htmlspecialchars($fetch_products['name']); ?>" />
      <input type="hidden" name="p_price" value="<?= htmlspecialchars($fetch_products['price']); ?>" />
      <input type="hidden" name="p_image" value="<?= htmlspecialchars($fetch_products['image']); ?>" />
      <input type="number" min="1" value="1" name="p_qty" class="qty" />
      <input type="submit" value="add to wishlist" class="option-btn" name="add_to_wishlist" />
      <input type="submit" value="add to cart" class="btn" name="add_to_cart" />
   </form>
   <?php
      }
   }else{
      echo '<p class="empty">no products added yet!</p>';
   }
   ?>

   </div>

</section>

<?php include 'footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>
