<?php

@include 'config.php';

session_start();

$user_id = $_SESSION['user_id'];

if(!isset($user_id)){
   header('location:login.php');
   exit;
}

// mysqli connection is assumed to be in $conn (mysqli object)

if(isset($_GET['delete'])){
   $delete_id = $_GET['delete'];

   // Check ownership before deleting
   $stmt = $conn->prepare("SELECT user_id FROM cart WHERE id = ?");
   $stmt->bind_param("i", $delete_id);
   $stmt->execute();
   $result = $stmt->get_result();
   $item = $result->fetch_assoc();
   $stmt->close();

   if($item && $item['user_id'] == $user_id){
      $stmt = $conn->prepare("DELETE FROM cart WHERE id = ?");
      $stmt->bind_param("i", $delete_id);
      $stmt->execute();
      $stmt->close();
   }
   header('location:cart.php');
   exit;
}

if(isset($_GET['delete_all'])){
   $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
   $stmt->bind_param("i", $user_id);
   $stmt->execute();
   $stmt->close();
   header('location:cart.php');
   exit;
}

if(isset($_POST['update_qty'])){
   $cart_id = $_POST['cart_id'];
   $p_qty = filter_var($_POST['p_qty'], FILTER_VALIDATE_INT);
   if($p_qty === false || $p_qty < 1){
      $p_qty = 1; // default to 1 if invalid
   }

   // Check ownership before updating
   $stmt = $conn->prepare("SELECT user_id FROM cart WHERE id = ?");
   $stmt->bind_param("i", $cart_id);
   $stmt->execute();
   $result = $stmt->get_result();
   $item = $result->fetch_assoc();
   $stmt->close();

   if($item && $item['user_id'] == $user_id){
      $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
      $stmt->bind_param("ii", $p_qty, $cart_id);
      $stmt->execute();
      $stmt->close();
   }

   header('location:cart.php');
   exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8" />
   <meta http-equiv="X-UA-Compatible" content="IE=edge" />
   <meta name="viewport" content="width=device-width, initial-scale=1.0" />
   <title>Shopping Cart</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" />

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css" />
</head>
<body>

<?php include 'header.php'; ?>

<section class="shopping-cart">

   <h1 class="title">products added</h1>

   <div class="box-container">

   <?php
      $grand_total = 0;

      $stmt = $conn->prepare("SELECT * FROM cart WHERE user_id = ?");
      $stmt->bind_param("i", $user_id);
      $stmt->execute();
      $result = $stmt->get_result();

      if($result->num_rows > 0){
         while($fetch_cart = $result->fetch_assoc()){
            $sub_total = $fetch_cart['price'] * $fetch_cart['quantity'];
   ?>
   <form action="" method="POST" class="box">
      <a href="cart.php?delete=<?= htmlspecialchars($fetch_cart['id']); ?>" class="fas fa-times" onclick="return confirm('delete this from cart?');"></a>
      <a href="view_page.php?pid=<?= htmlspecialchars($fetch_cart['pid']); ?>" class="fas fa-eye"></a>
      <img src="images/<?= htmlspecialchars($fetch_cart['image']); ?>" alt="">
      <div class="name"><?= htmlspecialchars($fetch_cart['name']); ?></div>
      <div class="price">$<?= htmlspecialchars(number_format($fetch_cart['price'], 2)); ?>/-</div>
      <input type="hidden" name="cart_id" value="<?= htmlspecialchars($fetch_cart['id']); ?>">
      <div class="flex-btn">
         <input type="number" min="1" value="<?= htmlspecialchars($fetch_cart['quantity']); ?>" class="qty" name="p_qty" required>
         <input type="submit" value="update" name="update_qty" class="option-btn">
      </div>
      <div class="sub-total"> sub total : <span>$<?= htmlspecialchars(number_format($sub_total, 2)); ?>/-</span> </div>
   </form>
   <?php
      $grand_total += $sub_total;
         }
      }else{
         echo '<p class="empty">your cart is empty</p>';
      }
      $stmt->close();
   ?>
   </div>

   <div class="cart-total">
      <p>Grand total : <span>$<?= htmlspecialchars(number_format($grand_total, 2)); ?>/-</span></p>
      <a href="shop.php" class="option-btn">continue shopping</a>
      <a href="cart.php?delete_all" class="delete-btn <?= ($grand_total > 0) ? '' : 'disabled'; ?>" onclick="return confirm('Delete all items from your cart?');">delete all</a>
      <a href="checkout.php" class="btn <?= ($grand_total > 0) ? '' : 'disabled'; ?>">proceed to checkout</a>
   </div>

</section>

<?php include 'footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>
