<?php

@include 'config.php';

session_start();

$user_id = $_SESSION['user_id'];

if(!isset($user_id)){
   header('location:login.php');
   exit;
}

$message = [];

if(isset($_POST['order'])){

   $name = $_POST['name'];
   $number = $_POST['number'];
   $email = $_POST['email'];
   $method = $_POST['method'];
   $address = 'flat no. '. $_POST['flat'] .' '. $_POST['street'] .' '. $_POST['city'] .' '. $_POST['state'] .' '. $_POST['country'] .' - '. $_POST['pin_code'];
   $placed_on = date('d-M-Y');

   $cart_total = 0;
   $cart_products = [];

   // Fetch cart items
   $stmt_cart = $conn->prepare("SELECT * FROM cart WHERE user_id = ?");
   $stmt_cart->bind_param("i", $user_id);
   $stmt_cart->execute();
   $result_cart = $stmt_cart->get_result();

   if($result_cart->num_rows > 0){
      while($cart_item = $result_cart->fetch_assoc()){
         $cart_products[] = $cart_item['name'].' ( '.$cart_item['quantity'].' )';
         $sub_total = $cart_item['price'] * $cart_item['quantity'];
         $cart_total += $sub_total;
      }
   }
   $stmt_cart->close();

   $total_products = implode(', ', $cart_products);

   // Check for duplicate order
   $stmt_check = $conn->prepare("SELECT * FROM orders WHERE name = ? AND number = ? AND email = ? AND method = ? AND address = ? AND total_products = ? AND total_price = ?");
   $stmt_check->bind_param("ssssssd", $name, $number, $email, $method, $address, $total_products, $cart_total);
   $stmt_check->execute();
   $result_check = $stmt_check->get_result();

   if($cart_total == 0){
      $message[] = 'your cart is empty';
   } elseif($result_check->num_rows > 0){
      $message[] = 'order placed already!';
   } else {
      // Insert order
      $stmt_insert = $conn->prepare("INSERT INTO orders (user_id, name, number, email, method, address, total_products, total_price, placed_on) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
      $stmt_insert->bind_param("issssssds", $user_id, $name, $number, $email, $method, $address, $total_products, $cart_total, $placed_on);
      $stmt_insert->execute();
      $stmt_insert->close();

      // Clear cart
      $stmt_delete = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
      $stmt_delete->bind_param("i", $user_id);
      $stmt_delete->execute();
      $stmt_delete->close();

      $message[] = 'order placed successfully!';
   }

   $stmt_check->close();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8" />
   <meta http-equiv="X-UA-Compatible" content="IE=edge" />
   <meta name="viewport" content="width=device-width, initial-scale=1.0" />
   <title>checkout</title>

   <!-- font awesome cdn link -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" />

   <!-- custom css file link -->
   <link rel="stylesheet" href="css/style.css" />
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

<section class="display-orders">

   <?php
      $cart_grand_total = 0;
      $stmt_display = $conn->prepare("SELECT * FROM cart WHERE user_id = ?");
      $stmt_display->bind_param("i", $user_id);
      $stmt_display->execute();
      $result_display = $stmt_display->get_result();

      if($result_display->num_rows > 0){
         while($fetch_cart_items = $result_display->fetch_assoc()){
            $cart_total_price = $fetch_cart_items['price'] * $fetch_cart_items['quantity'];
            $cart_grand_total += $cart_total_price;
   ?>
   <p><?= htmlspecialchars($fetch_cart_items['name']); ?> <span>(<?= '$'.htmlspecialchars($fetch_cart_items['price']).'/- x '.htmlspecialchars($fetch_cart_items['quantity']); ?>)</span></p>
   <?php
         }
      } else {
         echo '<p class="empty">your cart is empty!</p>';
      }
      $stmt_display->close();
   ?>
   <div class="grand-total">grand total : <span>$<?= $cart_grand_total; ?>/-</span></div>
</section>

<section class="checkout-orders">

   <form action="" method="POST">

      <h3>Place your order</h3>

      <div class="flex">
         <div class="inputBox">
            <span>your name :</span>
            <input type="text" name="name" placeholder="Enter your name" class="box" required />
         </div>
         <div class="inputBox">
            <span>your number :</span>
            <input type="number" name="number" placeholder="Enter your number" class="box" required />
         </div>
         <div class="inputBox">
            <span>your email :</span>
            <input type="email" name="email" placeholder="Enter your email" class="box" required />
         </div>
         <div class="inputBox">
            <span>Payment method :</span>
            <select name="method" class="box" required>
               <option value="cash on delivery">Cash on Delivery</option>
               <option value="credit card">Credit card</option>
               <option value="esewa">Eswa</option>
               <option value="paypal">Paypal</option>
            </select>
         </div>
         <div class="inputBox">
            <span>address line 01 :</span>
            <input type="text" name="flat" placeholder="e.g. flat number" class="box" required />
         </div>
         <div class="inputBox">
            <span>address line 02 :</span>
            <input type="text" name="street" placeholder="e.g. street name" class="box" required />
         </div>
         <div class="inputBox">
            <span>city :</span>
            <input type="text" name="city" placeholder="e.g. lalitpur" class="box" required />
         </div>
         <div class="inputBox">
            <span>state :</span>
            <input type="text" name="state" placeholder="e.g. bagmati" class="box" required />
         </div>
         <div class="inputBox">
            <span>country :</span>
            <input type="text" name="country" placeholder="e.g. nepal" class="box" required />
         </div>
         <div class="inputBox">
            <span>pin code :</span>
            <input type="number" min="0" name="pin_code" placeholder="e.g. 123456" class="box" required />
         </div>
      </div>

      <input type="submit" name="order" class="btn <?= ($cart_grand_total > 1) ? '' : 'disabled'; ?>" value="place order" />

   </form>

</section>

<?php include 'footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>
