<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include your database connection here
@include 'config.php';  // Make sure $conn is your MySQLi connection

// Initialize user_id safely
$user_id = $_SESSION['user_id'] ?? 0;

// Function to get count of items for a user from a specified table
function getUserItemCount($conn, $user_id, $table) {
    $count = 0;
    // Sanitize table name by allowing only expected tables
    $allowed_tables = ['wishlist', 'cart'];
    if (!in_array($table, $allowed_tables)) {
        return 0;
    }
    $sql = "SELECT COUNT(*) AS count FROM $table WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if ($row = mysqli_fetch_assoc($result)) {
            $count = (int)$row['count'];
        }
        mysqli_stmt_close($stmt);
    }
    return $count;
}

// Fetch wishlist and cart counts
$wishlist_count = getUserItemCount($conn, $user_id, 'wishlist');
$cart_count = getUserItemCount($conn, $user_id, 'cart');

// Default profile data
$profile_img = "default.png";
$profile_name = "Guest";

if ($user_id) {
    $user_sql = "SELECT name, image FROM users WHERE id = ?";
    $user_stmt = mysqli_prepare($conn, $user_sql);
    if ($user_stmt) {
        mysqli_stmt_bind_param($user_stmt, "i", $user_id);
        mysqli_stmt_execute($user_stmt);
        $user_result = mysqli_stmt_get_result($user_stmt);
        if ($user = mysqli_fetch_assoc($user_result)) {
            $profile_img = htmlspecialchars($user['image']);
            $profile_name = htmlspecialchars($user['name']);
        }
        mysqli_stmt_close($user_stmt);
    }
}
?>

<!-- Optional messages display -->
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

      <a href="home.php" class="logo">Grocery<span>Plus</span></a>

      <nav class="navbar">
         <a href="home.php">Home</a>
         <a href="shop.php">Products</a>
         <a href="orders.php">Orders</a>
         <a href="about.php">About</a>
         <a href="contact.php">Contact</a>
      </nav>

      <div class="icons">
         <div id="menu-btn" class="fas fa-bars"></div>
         <div id="user-btn" class="fas fa-user"></div>
         <a href="search_page.php" class="fas fa-search"></a>

         <a href="wishlist.php">
            <i class="fas fa-heart"></i><span>(<?= $wishlist_count; ?>)</span>
         </a>
         <a href="cart.php">
            <i class="fas fa-shopping-cart"></i><span>(<?= $cart_count; ?>)</span>
         </a>
      </div>

      <div class="profile">
         <img src="images/<?= $profile_img; ?>" alt="<?= $profile_name; ?>'s profile picture">
         <p><?= $profile_name; ?></p>
         
         <?php if ($user_id): ?>
            <a href="user_profile_update.php" class="btn">update profile</a>
            <a href="logout.php" class="delete-btn">logout</a>
         <?php else: ?>
            <div class="flex-btn">
               <a href="login.php" class="option-btn">login</a>
               <a href="register.php" class="option-btn">register</a>
            </div>
         <?php endif; ?>
      </div>

   </div>
</header>
