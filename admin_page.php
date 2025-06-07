<?php

@include 'config.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
    header('location:login.php');
    exit;
}

function count_rows($conn, $query, $types = null, $params = []) {
    $stmt = mysqli_prepare($conn, $query);
    if ($stmt && $types !== null) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    $count = mysqli_stmt_num_rows($stmt);
    mysqli_stmt_close($stmt);
    return $count;
}

function sum_total_price($conn, $status) {
    $total = 0;
    $stmt = mysqli_prepare($conn, "SELECT total_price FROM orders WHERE payment_status = ?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $status);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $price);
        while (mysqli_stmt_fetch($stmt)) {
            $total += $price;
        }
        mysqli_stmt_close($stmt);
    }
    return $total;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>admin page</title>

    <!-- font awesome cdn link  -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" />

    <!-- custom css file link  -->
    <link rel="stylesheet" href="css/admin_style.css?v=<?= time(); ?>">

</head>

<body>

    <?php include 'admin_header.php'; ?>

    <section class="dashboard">

        <h1 class="title">dashboard</h1>

        <div class="box-container">

            <div class="box">
                <?php
                $total_pendings = sum_total_price($conn, 'pending');
                ?>
                <h3>$<?= htmlspecialchars($total_pendings); ?>/-</h3>
                <p>total pendings</p>
                <a href="admin_orders.php" class="btn">see orders</a>
            </div>

            <div class="box">
                <?php
                $total_completed = sum_total_price($conn, 'completed');
                ?>
                <h3>$<?= htmlspecialchars($total_completed); ?>/-</h3>
                <p>completed orders</p>
                <a href="admin_orders.php" class="btn">see orders</a>
            </div>

            <div class="box">
                <?php
                $number_of_orders = count_rows($conn, "SELECT id FROM orders");
                ?>
                <h3><?= htmlspecialchars($number_of_orders); ?></h3>
                <p>orders placed</p>
                <a href="admin_orders.php" class="btn">see orders</a>
            </div>

            <div class="box">
                <?php
                $number_of_products = count_rows($conn, "SELECT id FROM products");
                ?>
                <h3><?= htmlspecialchars($number_of_products); ?></h3>
                <p>products added</p>
                <a href="admin_products.php" class="btn">see products</a>
            </div>

            <div class="box">
                <?php
                $number_of_users = count_rows($conn, "SELECT id FROM users WHERE user_type = ?", "s", ['user']);
                ?>
                <h3><?= htmlspecialchars($number_of_users); ?></h3>
                <p>total users</p>
                <a href="admin_users.php" class="btn">see accounts</a>
            </div>

            <div class="box">
                <?php
                $number_of_admins = count_rows($conn, "SELECT id FROM users WHERE user_type = ?", "s", ['admin']);
                ?>
                <h3><?= htmlspecialchars($number_of_admins); ?></h3>
                <p>total admins</p>
                <a href="admin_users.php" class="btn">see accounts</a>
            </div>

            <div class="box">
                <?php
                $number_of_accounts = count_rows($conn, "SELECT id FROM users");
                ?>
                <h3><?= htmlspecialchars($number_of_accounts); ?></h3>
                <p>total accounts</p>
                <a href="admin_users.php" class="btn">see accounts</a>
            </div>

            <div class="box">
                <?php
                $number_of_messages = count_rows($conn, "SELECT id FROM message");
                ?>
                <h3><?= htmlspecialchars($number_of_messages); ?></h3>
                <p>total messages</p>
                <a href="admin_contacts.php" class="btn">see messages</a>
            </div>

        </div>

    </section>

    <script src="js/script.js"></script>

</body>

</html>
