<?php

@include 'config.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
    header('location:login.php');
    exit;
}

$message = [];

if (isset($_POST['update_order'])) {

    $order_id = $_POST['order_id'];
    $update_payment = $_POST['update_payment'];
    // filter_var removed

    // Prepare and execute update query using mysqli
    $stmt = mysqli_prepare($conn, "UPDATE `orders` SET payment_status = ? WHERE id = ?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "si", $update_payment, $order_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        $message[] = 'Payment has been updated!';
    } else {
        $message[] = 'Failed to update payment status.';
    }
}

if (isset($_GET['delete'])) {

    $delete_id = $_GET['delete'];

    // Prepare and execute delete query using mysqli
    $stmt = mysqli_prepare($conn, "DELETE FROM `orders` WHERE id = ?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $delete_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        header('location:admin_orders.php');
        exit;
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>orders</title>

    <!-- font awesome cdn link  -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" />

    <!-- custom css file link  -->
    <link rel="stylesheet" href="css/admin_style.css" />
</head>

<body>

    <?php include 'admin_header.php'; ?>

    <?php
    if (!empty($message)) {
        foreach ($message as $msg) {
            echo '<div class="message"><span>' . htmlspecialchars($msg) . '</span>
            <i class="fas fa-times" onclick="this.parentElement.remove();"></i></div>';
        }
    }
    ?>

    <section class="placed-orders">

        <h1 class="title">placed orders</h1>

        <div class="box-container">

            <?php
            // Select all orders
            $result = mysqli_query($conn, "SELECT * FROM `orders`");
            if ($result && mysqli_num_rows($result) > 0) {
                while ($fetch_orders = mysqli_fetch_assoc($result)) {
            ?>
                    <div class="box">
                        <p> user id : <span><?= (int)$fetch_orders['user_id']; ?></span> </p>
                        <p> placed on : <span><?= htmlspecialchars($fetch_orders['placed_on']); ?></span> </p>
                        <p> name : <span><?= htmlspecialchars($fetch_orders['name']); ?></span> </p>
                        <p> email : <span><?= htmlspecialchars($fetch_orders['email']); ?></span> </p>
                        <p> number : <span><?= htmlspecialchars($fetch_orders['number']); ?></span> </p>
                        <p> address : <span><?= htmlspecialchars($fetch_orders['address']); ?></span> </p>
                        <p> total products : <span><?= htmlspecialchars($fetch_orders['total_products']); ?></span> </p>
                        <p> total price : <span>$<?= htmlspecialchars($fetch_orders['total_price']); ?>/-</span> </p>
                        <p> payment method : <span><?= htmlspecialchars($fetch_orders['method']); ?></span> </p>
                        <form action="" method="POST">
                            <input type="hidden" name="order_id" value="<?= (int)$fetch_orders['id']; ?>">
                            <select name="update_payment" class="drop-down" required>
                                <option value="" disabled><?= htmlspecialchars($fetch_orders['payment_status']); ?></option>
                                <option value="pending">pending</option>
                                <option value="completed">completed</option>
                            </select>
                            <div class="flex-btn">
                                <input type="submit" name="update_order" class="option-btn" value="update" />
                                <a href="admin_orders.php?delete=<?= (int)$fetch_orders['id']; ?>" class="delete-btn" onclick="return confirm('delete this order?');">delete</a>
                            </div>
                        </form>
                    </div>
            <?php
                }
            } else {
                echo '<p class="empty">no orders placed yet!</p>';
            }
            ?>

        </div>

    </section>

    <script src="js/script.js"></script>

</body>

</html>
